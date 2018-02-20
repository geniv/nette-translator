<?php declare(strict_types=1);

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
    const
        TRANSLATION_IDENTIFICATION = 'translation';
    /** @var string */
    private $identification;
    /** @var Configurator */
    private $configurator;


    /**
     * ConfiguratorDriver constructor.
     *
     * @param string       $identification
     * @param ILocale      $locale
     * @param Configurator $configurator
     */
    public function __construct($identification = '', ILocale $locale, Configurator $configurator)
    {
        parent::__construct($locale);

        $this->identification = $identification ?: self::TRANSLATION_IDENTIFICATION;
        $this->configurator = $configurator;

        // load translate
        $this->loadTranslate();
    }


    /**
     * Load translate.
     */
    protected function loadTranslate()
    {
        //TODO toto zahrnout do konfigurace
        $this->dictionary = $this->configurator->loadDataByType($this->identification)
            ->fetchPairs('ident', 'content');
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
        $method = 'set' . ucfirst($this->identification);
        return $this->configurator->$method($ident, $message);
    }


    /**
     * Search translate by idents.
     *
     * @param array $idents
     * @return array
     */
    public function searchTranslate(array $idents)
    {
        //TODO toto by mohli jit implementovat!?!
        // TODO: Implement searchTranslate() method.
        return [];
    }
}
