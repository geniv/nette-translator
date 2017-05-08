<?php

namespace TranslatorServices\Drivers;

use LocaleServices\LocaleService;
use TranslatorService\TranslatorService;


/**
 * Class DevNull
 *
 * /Dev/Null translator s podporou plural substituce a podporou samostatne substituce, bez uloziste
 *
 * @author  geniv
 * @package TranslatorServices\Drivers
 */
class DevNull extends TranslatorService
{

    /**
     * DevNull constructor.
     *
     * @param LocaleService $languageService
     */
    public function __construct(LocaleService $languageService)
    {
        parent::__construct($languageService);
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
        $code = $this->languageService->getCode();
        if (isset($this->plural[$code]) && isset($count) && isset($plurals)) {
            $plural = null; // vystupni promenna typu pluralu
            $n = $count;    // predani poctu polozek
            eval($this->plural[$code]);    // samotna evaluace pluralu
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
     * nacitani prekladu
     *
     * @return mixed
     */
    protected function loadTranslate()
    {
        return false;
    }


    /**
     * ukladani prekladu
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
     * hledani prekladu podle identu
     *
     * @param $idents
     * @return mixed
     */
    public function searchTranslate($idents)
    {
        // TODO: Implement searchTranslate() method.
    }
}
