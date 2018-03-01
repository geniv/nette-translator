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
     * Load translate.
     */
    protected function loadTranslate()
    {
    }


    /**
     * Save translate.
     *
     * @param string $identification
     * @param        $message
     * @param null   $idLocale
     * @return string
     */
    protected function saveTranslate(string $identification, $message, $idLocale = null): string
    {
        $this->dictionary[$identification] = $message;  // save to only variable
        return $message;
    }


    /**
     * Search translate.
     *
     * @param array $identifications
     * @return array
     */
    public function searchTranslate(array $identifications): array
    {
        return [];
    }
}
