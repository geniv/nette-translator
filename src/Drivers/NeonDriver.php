<?php

namespace Translator\Drivers;

use Translator\Translator;
use LocaleServices\LocaleService;
use Nette\Neon\Neon;


/**
 * Class NeonDriver
 *
 * souborovy s podporou Pluralu
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
     * @param array         $parameters
     * @param LocaleService $localeService
     */
    public function __construct(array $parameters, LocaleService $localeService)
    {
        parent::__construct($localeService);

        // pokud parametr table neexistuje
        if (!isset($parameters['path'])) {
            throw new Exception('Parameters path is not defined in configure! (path: xy)');
        }
        // nacteni jmena tabulky
        $path = $parameters['path'];

        // vytvoreni cesty
        $this->path = $path . '/dictionary-' . $localeService->getCode() . '.neon';

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
     * @param $index
     * @param $message
     * @return mixed
     */
    protected function saveTranslate($index, $message)
    {
        //vlozeni prekladu do pole
        $this->dictionary[$index] = $message;
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
}
