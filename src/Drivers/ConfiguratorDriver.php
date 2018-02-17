<?php

namespace Translator\Drivers;

use Translator\Translator;


/**
 * Class ConfiguratorDriver
 *
 * @author  geniv
 * @package Translator\Drivers
 */
class ConfiguratorDriver extends Translator
{

    /**
     * Update translate.
     *
     * @param $ident
     * @param $message
     * @param $idLocale
     * @return mixed
     */
    protected function updateTranslate($ident, $message, $idLocale)
    {
        // TODO: Implement updateTranslate() method.
    }


    /**
     * Load translate.
     *
     * @return mixed
     */
    protected function loadTranslate()
    {
        // TODO: Implement loadTranslate() method.
    }


    /**
     * Save translate.
     *
     * @param $ident
     * @param $message
     * @return mixed
     */
    protected function saveTranslate($ident, $message)
    {
        // TODO: Implement saveTranslate() method.
    }


    /**
     * Search translate by idents.
     *
     * @param array $idents
     * @return mixed
     */
    public function searchTranslate(array $idents)
    {
        // TODO: Implement searchTranslate() method.
    }
}
