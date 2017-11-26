<?php

namespace Translator\Drivers;

use Exception;
use Translator\Translator;
use Locale\Locale;
use Nette\Neon\Neon;


/**
 * Class NeonDriver
 *
 * Filesystem with support plurals.
 *
 * @author  geniv
 * @package Translator\Drivers
 */
class NeonDriver extends Translator
{
    /** @var string path to file */
    private $path;


    /**
     * NeonDriver constructor.
     *
     * @param array  $parameters
     * @param Locale $locale
     * @throws Exception
     */
    public function __construct(array $parameters, Locale $locale)
    {
        parent::__construct($locale);

        // pokud parametr table neexistuje
        if (!isset($parameters['path'])) {
            throw new Exception('Parameters path is not defined in configure! (path: xy)');
        }
        // nacteni jmena tabulky
        $path = $parameters['path'];

        // path
        $this->path = $path . '/dictionary-' . $locale->getCode() . '.neon';

        $this->loadTranslate();    // nacteni prekladu
    }


    /**
     * Load translate.
     *
     * @return mixed
     */
    protected function loadTranslate()
    {
        if (file_exists($this->path)) {
            $this->dictionary = Neon::decode(file_get_contents($this->path));
        }
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
        //vlozeni prekladu do pole
        $this->dictionary[$ident] = $message;
        //ulozit do souboru
        file_put_contents($this->path, Neon::encode($this->dictionary, Neon::BLOCK));
        // vraceni textu
        return $message;
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
        $this->saveTranslate($ident, $message);
    }
}
