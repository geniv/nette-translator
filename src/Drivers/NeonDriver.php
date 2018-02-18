<?php

namespace Translator\Drivers;

use Exception;
use Translator\Translator;
use Locale\ILocale;
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
     * @param         $path
     * @param ILocale $locale
     */
    public function __construct($path, ILocale $locale)
    {
        parent::__construct($locale);

        // path
        $this->path = $path . '/dictionary-' . $locale->getCode() . '.neon';

        // load translate
        $this->loadTranslate();
    }


    /**
     * Update translate.
     *
     * @param $ident
     * @param $message
     * @param $idLocale
     */
    protected function updateTranslate($ident, $message, $idLocale)
    {
        $this->saveTranslate($ident, $message);
    }


    /**
     * Load translate.
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
     * @return string
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
     * @return array
     */
    public function searchTranslate(array $idents)
    {
        return [];
    }
}
