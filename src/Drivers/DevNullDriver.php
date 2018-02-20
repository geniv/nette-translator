<?php declare(strict_types=1);

namespace Translator\Drivers;

use Translator\Translator;
use Locale\ILocale;


/**
 * Class DevNullDriver
 *
 * /Dev/Null translator with support plurals without storage.
 *
 * @author  geniv
 * @package Translator\Drivers
 */
class DevNullDriver extends Translator
{

    /**
     * DevNullDriver constructor.
     *
     * @param ILocale $locale
     */
    public function __construct(ILocale $locale)
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
     */
    protected function loadTranslate()
    {
    }


    /**
     * Save translate.
     *
     * @param string $ident
     * @param string $message
     * @param null   $idLocale
     * @return string
     */
    protected function saveTranslate($ident, $message, $idLocale = null)
    {
        return $message;
    }


    /**
     * Search translate by idents.
     *
     * @param array $idents
     * @return array
     */
    public function searchTranslate(array $idents)
    {
        return [];
    }
}
