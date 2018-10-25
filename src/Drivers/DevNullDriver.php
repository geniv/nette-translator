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
        // set fake translate for enable searchDefaultTranslate()
        $this->dictionary['__DevNullDriver__'] = true;
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
}
