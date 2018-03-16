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
    protected $plural;
    /** @var array */
    private $searchPath;
    /** @var array */
    private $listDefaultTranslate = [], $listAllDefaultTranslate = [], $listUsedIndex = [];


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
     * @param  mixed    message
     * @param  int      plural count
     * @return string
     */
    public final function translate($message, $count = NULL)
    {
        $indexDictionary = $message; // message is index (identification) for translation

        if ($message) {
            if (isset($count)) {
                if (isset($this->plural)) {
                    if (!is_array($count)) {
                        $plural = null; // input variable plural for eval
                        $n = $count;    // input variable count for eval
                        eval($this->plural);    // evaluate plural
                        $pluralFormat = '%s:plural:%d'; // create format plural
                        $pluralIndex = sprintf($pluralFormat, $indexDictionary, $plural);   // main substitute plural form
                        $this->listUsedIndex[] = $pluralIndex;
                        if (!isset($this->dictionary[$pluralIndex])) {
                            // make other plural form by $nplurals
                            if (isset($nplurals)) {
                                // iterate over $nplurals
                                for ($i = 0; $i < $nplurals; $i++) {
                                    $this->saveTranslate(sprintf($pluralFormat, $indexDictionary, $i), $message);   // create plural index
                                }
                                return $message;    // return message
                            } else {
                                return $this->saveTranslate($pluralIndex, $message);    // create plural without $nplurals
                            }
                        }
                        return sprintf($this->dictionary[$pluralIndex], $count);    // substitute value
                    } else {
                        $this->listUsedIndex[] = $indexDictionary;
                        // if plural enable but $count is array
                        return vsprintf($this->dictionary[$indexDictionary], $count);   // array
                    }
                } else {
                    $this->listUsedIndex[] = $indexDictionary;
                    if (!isset($this->dictionary[$indexDictionary])) {
                        return $this->saveTranslate($indexDictionary, $message);    // create & return
                    }

                    if (!is_array($count)) {
                        // count is value
                        return sprintf($this->dictionary[$indexDictionary], $count);    // value
                    } else {
                        // count is array
                        return vsprintf($this->dictionary[$indexDictionary], $count);   // array
                    }
                }
            } else {
                $this->listUsedIndex[] = $indexDictionary;
                if (!isset($this->dictionary[$indexDictionary])) {
                    return $this->saveTranslate($indexDictionary, $message);    // create & return
                }
                return $this->dictionary[$indexDictionary];
            }
        }
        return '';
    }


    /**
     * Set path search.
     *
     * @param array $searchPath
     */
    public function setSearchPath(array $searchPath)
    {
        $this->searchPath = $searchPath;
        $this->searchDefaultTranslate();
    }


    /**
     * Search default translate.
     */
    private function searchDefaultTranslate()
    {
        if ($this->searchPath) {
            $messages = [];
            // load all default translation files
            foreach (Finder::findFiles('*Translation.neon')->from($this->searchPath) as $file) {
                $lengthPath = strlen(dirname(__DIR__, 4));
                $partPath = substr($file->getRealPath(), $lengthPath + 1);

                $fileContent = (array) Neon::decode(file_get_contents($file->getPathname()));
                $this->listDefaultTranslate[$partPath] = $fileContent;

                $messages = array_merge($messages, $fileContent);  // translate file may by empty
            }
            $this->listAllDefaultTranslate = $messages;

            foreach ($messages as $identification => $message) {
                if (!isset($this->dictionary[$identification]) && !is_array($message)) {   // save only not exist identification and only string message
                    $this->saveTranslate($identification, $message);    // call only save default value load from files
                }
            }
        }
    }


    /**
     * Get list default translate.
     *
     * @return array
     */
    public function getListDefaultTranslate(): array
    {
        return $this->listDefaultTranslate;
    }


    /**
     * Get list all default translate.
     *
     * @return array
     */
    public function getListAllDefaultTranslate(): array
    {
        return $this->listAllDefaultTranslate;
    }


    /**
     * Get list used translate.
     *
     * @return array
     */
    public function getListUsedTranslate(): array
    {
        return $this->listUsedIndex;
    }


    /**
     * Get dictionary.
     *
     * @return array
     */
    public function getDictionary(): array
    {
        return $this->dictionary;
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
     * @param        $message
     * @param null   $idLocale
     * @return string
     */
    abstract protected function saveTranslate(string $identification, $message, $idLocale = null): string;


    /**
     * Search translate.
     *
     * TODO maybe deprecated!
     *
     * @param array $identifications
     * @return array
     */
    abstract public function searchTranslate(array $identifications): array;
}
