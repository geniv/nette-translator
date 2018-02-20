<?php declare(strict_types=1);

namespace Translator\Drivers;

use Nette\Neon\Neon;
use Translator\Translator;
use Locale\ILocale;


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
     * @param string $ident
     * @param string $message
     * @param null   $idLocale
     * @return string
     */
    protected function saveTranslate($ident, $message, $idLocale = null)
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
