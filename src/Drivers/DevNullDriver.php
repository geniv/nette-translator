<?php

namespace Translator\Drivers;

use Translator\Translator;
use Locale\Locale;


/**
 * Class DevNullDriver
 *
 * /Dev/Null translator s podporou plural substituce a podporou samostatne substituce, bez uloziste
 *
 * @author  geniv
 * @package Translator\Drivers
 */
class DevNullDriver extends Translator
{

    /**
     * DevNullDriver constructor.
     *
     * @param Locale $locale
     */
    public function __construct(Locale $locale)
    {
        parent::__construct($locale);
    }


    /**
     * Translates the given string.
     *
     * @param      $message
     * @param null $count
     * @param null $plurals
     * @return null|string
     */
    public function translate($message, $count = null, $plurals = null)
    {
        if (isset($this->plural) && isset($count) && isset($plurals)) {
            $plural = null; // vystupni promenna typu pluralu
            $n = $count;    // predani poctu polozek
            eval($this->plural);    // samotna evaluace pluralu
            return sprintf($plurals[$plural], $count);  // vyber spravneho indexu z pole pluralu
        }

        if (is_array($count)) { // pokud je pole pouzije vsprintf
            // vicenasobna substituce pole
            return vsprintf($message, $count);    // pole
        }
        // substituce parametru
        return sprintf($message, $count); // parametr
    }


    /**
     * Load translate.
     *
     * @return mixed
     */
    protected function loadTranslate()
    {
        return false;
    }


    /**
     * Save translate.
     *
     * @param $index
     * @param $message
     * @return mixed
     */
    protected function saveTranslate($index, $message)
    {
        return false;
    }


    /**
     * Search translate by idents.
     *
     * @param array $idents
     * @return mixed
     */
    public function searchTranslate(array $idents)
    {
        return [];
    }
}
