<?php

namespace TranslatorServices\Drivers;

use LocaleServices\LocaleService;
use TranslatorService\TranslatorService;


/**
 * Class Neon
 *
 * souborovy s podporou Pluralu
 *
 * @author  geniv
 * @package TranslatorServices\Drivers
 */
class Neon extends TranslatorService
{
    private $path;


    /**
     * Neon constructor.
     *
     * @param array         $parameters
     * @param LocaleService $languageService
     */
    public function __construct(array $parameters, LocaleService $languageService)
    {
        parent::__construct($languageService);

        // pokud parametr table neexistuje
        if (!isset($parameters['path'])) {
            throw new Exception('Table name is not defined in configure! (table: xy)');
        }
        // nacteni jmena tabulky
        $path = $parameters['path'];

//        $this->plurals = $plurals;

        // vytvoreni cesty
        $this->path = $path . '/dictionary-' . $this->languageService->getCode() . '.neon';

        $this->loadTranslate();    // nacteni prekladu
    }


    /**
     * nacitani prekladu
     *
     * @return mixed
     */
    protected function loadTranslate()
    {
        if (file_exists($this->path)) {
            $this->dictionary = \Nette\Neon\Neon::decode(file_get_contents($this->path));
        }
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
//        if ($this->languageService->getCode() != $this->languageService->getMainLang()) {
//            $message = sprintf('## %s ##', $message);   // defaultni prekladovy text
//        }
        //vlozeni prekladu do pole
        $this->dictionary[$index] = $message;
        //ulozit do souboru
        file_put_contents($this->path, \Nette\Neon\Neon::encode($this->dictionary, \Nette\Neon\Neon::BLOCK));

        // vraceni textu
        return $message;
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
