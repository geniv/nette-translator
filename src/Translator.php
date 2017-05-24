<?php

namespace Translator;

use Locale\Locale;
use Nette\Localization\ITranslator;
use Nette\SmartObject;


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

    /** @var Locale locale from DI */
    protected $locale;
    /** @var array dictionary array */
    protected $dictionary = [];
    /** @var string plural format */
    protected $plural = null;


    /**
     * Translator constructor.
     *
     * @param Locale $locale
     */
    protected function __construct(Locale $locale)
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
            if (!isset($count) || !isset($this->plural)) {   // pokud neni pocet nebo neni plural
                if (!isset($this->dictionary[$indexDictionary])) {
                    return $this->saveTranslate($indexDictionary, $message);    // vytvoreni
                }
            } else {
                // obsluha ciste substituce, pokud je count pole, a prvni index je NULL
                if (isset($count) && is_array($count) && is_null($count[0])) {
                    if (!isset($this->dictionary[$indexDictionary])) {
                        return $this->saveTranslate($indexDictionary, $message);    // vytvoreni
                    } else {
                        return vsprintf($this->dictionary[$indexDictionary], array_slice($count, 1));    // substitude od 1. indexu
                    }
                }

                // obsluha pluralove substituce
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
                }
            }
            return $this->dictionary[$indexDictionary];
        }
        return null;
    }


    public function createTranslate($index, $message)
    {
        if (!isset($this->dictionary[$index])) {
            $this->saveTranslate($index, $message);
        }
        return $this->dictionary[$index];
    }


    /**
     * Load translate.
     *
     * @return mixed
     */
    abstract protected function loadTranslate();


    /**
     * Save translate.
     *
     * @param $index
     * @param $message
     * @return mixed
     */
    abstract protected function saveTranslate($index, $message);


    /**
     * Search translate by idents.
     *
     * @param array $idents
     * @return mixed
     */
    abstract public function searchTranslate(array $idents);
}
