<?php declare(strict_types=1);

namespace Translator;

use Locale\ILocale;
use Nette\Localization\ITranslator;
use Nette\Neon\Neon;
use Nette\SmartObject;
use Nette\Utils\Finder;


/**
 * Class Translator
 *
 * @author  geniv
 * @package Translator
 */
abstract class Translator implements ITranslator
{
    use SmartObject;

    /** @var ILocale */
    protected $locale;
    /** @var array */
    protected $dictionary = [];
    /** @var string */
    protected $plural = null;
    /** @var string */
    private $path;


    /**
     * Translator constructor.
     *
     * @param ILocale $locale
     */
    protected function __construct(ILocale $locale)
    {
        $this->locale = $locale;

        // example: '$plural=(n==1) ? 0 : ((n>=2 && n<=4) ? 1 : 2);'
        // zdroj: http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
        // predavani pluralu z locales do translatu vzdy pro konkretni jazyk
        $this->plural = $locale->getPlural();
    }


    /**
     * Translates the given string.
     *
     * @param      $message
     * @param null $count
     * @return null|string
     */
    public function translate($message, $count = NULL)
    {
        $indexDictionary = $message; // jako index je pouzity text ktery odpovida prekladovemu textu

        if ($message) {
            if (isset($count)) {
                if (isset($this->plural)) {
                    $plural = null; // vystupni promenna typu pluralu
                    $n = (is_array($count) ? $count[0] : $count);    // vstupni promenna poctu (pokud je pole, bere index: [0])
                    eval($this->plural);    // samotna evaluace pluralu
                    $pluralFormat = '%s:plural:%d'; // format pluralu
                    $pluralIndex = sprintf($pluralFormat, $indexDictionary, $plural);   // slozeni rozsireneho indexu
                    if (!isset($this->dictionary[$pluralIndex])) {
                        // hromadne vkladani plural tvaru podle poctu ($nplurals)
                        if (isset($nplurals)) {
                            // vlozeni vsech pluralu naraz
                            for ($i = 0; $i < $nplurals; $i++) {
                                $this->saveTranslate(sprintf($pluralFormat, $indexDictionary, $i), $message);    // vytvoreni vsech pluralu
                            }
                            return $message;
                        } else {
                            return $this->saveTranslate($pluralIndex, $message);  // vytvoreni konkretniho pluralu
                        }
                    } else {
                        if (is_array($count)) { // pokud je pole pouzije vsprintf
                            // vicenasobna substituce pole
                            return vsprintf($this->dictionary[$pluralIndex], $count);    // pole
                        }
                        // substituce parametru
                        return sprintf($this->dictionary[$pluralIndex], $count); // parametr
                    }
                } else {
                    if (!isset($this->dictionary[$indexDictionary])) {
                        return $this->saveTranslate($indexDictionary, $message);    // vytvoreni
                    }

                    if (is_array($count)) { // pokud je pole pouzije vsprintf
                        // vicenasobna substituce pole
                        return vsprintf($this->dictionary[$indexDictionary], $count);    // pole
                    } else {
                        return sprintf($this->dictionary[$indexDictionary], $count); // parametr
                    }
                }
            }

            if (!isset($this->dictionary[$indexDictionary])) {
                return $this->saveTranslate($indexDictionary, $message);    // vytvoreni
            }
            return $this->dictionary[$indexDictionary];
        }
        return null;
    }


    /**
     * Set path.
     *
     * @param string $path
     */
    public function setPath(string $path)
    {
        $this->path = $path;
        $this->searchDefaultTranslate();
    }


    /**
     * Search default translate.
     */
    private function searchDefaultTranslate()
    {
        if ($this->path) {
            $messages = [];
            foreach (Finder::findFiles('*Translation.neon')->from($this->path) as $file) {
                $messages = array_merge($messages, Neon::decode(file_get_contents($file->getPathname())));
            }

            foreach ($messages as $identification => $message) {
                if (!isset($this->dictionary[$identification])) {   // save only not exist identification
                    $this->saveTranslate($identification, $message);
                }
            }
        }
    }


    /**
     * Create translate.
     *
     * @param      $identification
     * @param      $message
     * @param null $idLocale
     * @return string
     */
    public function createTranslate(string $identification, string $message, $idLocale = null): string
    {
        if (isset($this->dictionary) && $this->dictionary) {
            if (!isset($this->dictionary[$identification]) || $this->dictionary[$identification] != $message) {
                $this->saveTranslate($identification, $message, $idLocale ?: $this->locale->getId());
            }
            return $this->dictionary[$identification];
        }
        return $message;
    }


    /**
     * Load translate.
     */
    abstract protected function loadTranslate();


    /**
     * Save translate.
     *
     * @param string $identification
     * @param string $message
     * @param null   $idLocale
     * @return string
     */
    abstract protected function saveTranslate(string $identification, string $message, $idLocale = null): string;


    /**
     * Search translate.
     *
     * @param array $identifications
     * @return array
     */
    abstract public function searchTranslate(array $identifications): array;
}
