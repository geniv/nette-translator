<?php

namespace Translator;

use Locale\ILocale;
use Nette\Localization\ITranslator;
use Nette\SmartObject;
use Nette\Utils\Finder;


/**
 * Class Translator
 *
 * abstraktni trida prekladu
 *
 * @author  geniv
 * @package Translator
 */
abstract class Translator implements ITranslator
{
    use SmartObject;

    /** @var ILocale locale from DI */
    protected $locale;
    /** @var array dictionary array */
    protected $dictionary = [];
    /** @var string plural format */
    protected $plural = null;


    /**
     * Translator constructor.
     *
     * @param ILocale $locale
     */
    protected function __construct(ILocale $locale)
    {
        $this->locale = $locale;

        $this->searchDefaultTranslate();    //TODO docasne

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
     * Manual create translate.
     *
     * @param      $ident
     * @param      $message
     * @param null $idLocale
     * @return string
     */
    public function createTranslate($ident, $message, $idLocale = null)
    {
        if (isset($this->dictionary) && $this->dictionary) {
            if (!isset($this->dictionary[$ident]) || $this->dictionary[$ident] != $message) {
                $this->updateTranslate($ident, $message, $idLocale ?: $this->locale->getId());
            }
            return $this->dictionary[$ident];
        }
        return $message;
    }


    /**
     * Update translate.
     *
     * @param $ident
     * @param $message
     * @param $idLocale
     */
    abstract protected function updateTranslate($ident, $message, $idLocale);


    /**
     * Load translate.
     */
    abstract protected function loadTranslate();


    /**
     * Save translate.
     *
     * @param $ident
     * @param $message
     * @return string
     */
    abstract protected function saveTranslate($ident, $message);


    /**
     * Search translate by idents.
     *
     * @param array $idents
     * @return array
     */
    abstract public function searchTranslate(array $idents);


    public function searchDefaultTranslate()
    {
        foreach (Finder::findFiles('*Translation.neon')->from('/var/www/html/NetteWeb') as $file) {
            dump($file);
        }
    }
}
