<?php

namespace Translator\Drivers;

use Configurator;
use Locale\ILocale;
use Translator\Translator;


/**
 * Class ConfiguratorDriver
 *
 * @author  geniv
 * @package Translator\Drivers
 */
class ConfiguratorDriver extends Translator
{
    /** @var Configurator */
    private $configurator;


    /**
     * ConfiguratorDriver constructor.
     *
     * @param ILocale      $locale
     * @param Configurator $configurator
     */
    public function __construct(ILocale $locale, Configurator $configurator)
    {
        parent::__construct($locale);

//        dump($locale->getId());
        $this->configurator = $configurator;

        // load translate
        $this->loadTranslate();
    }
//TODO load defatult data!!! system!


    /**
     * Update translate.
     *
     * @param $ident
     * @param $message
     * @param $idLocale
     */
    protected function updateTranslate($ident, $message, $idLocale)
    {
        // TODO: Implement updateTranslate() method.
    }


    /**
     * Load translate.
     */
    protected function loadTranslate()
    {
        //TODO toto zahrnout do konfigurace
        $this->dictionary = $this->configurator->loadDataByType('translation')
            ->fetchPairs('ident', 'content');
    }


    /**
     * Save translate.
     *
     * @param $ident
     * @param $message
     * @return string
     */
    protected function saveTranslate($ident, $message)
    {
        $this->configurator->setTranslation($ident, $message);
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
        // TODO: Implement searchTranslate() method.
        return [];
    }
}
