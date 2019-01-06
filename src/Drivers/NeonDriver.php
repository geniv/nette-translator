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
    /** @var string */
    private $path;


    /**
     * NeonDriver constructor.
     *
     * @param string  $path
     * @param ILocale $locale
     */
    public function __construct(string $path, ILocale $locale)
    {
        parent::__construct($locale);

        // set path
        $this->path = $path;
    }


    /**
     * Get path.
     *
     * @return string
     */
    private function getPath(): string
    {
        return $this->path . '/dictionary-' . $this->locale->getCode() . '.neon';
    }


    /**
     * Clean cache.
     */
    public function cleanCache()
    {
        // empty body
    }


    /**
     * Load translate.
     */
    protected function loadTranslate()
    {
        if (file_exists($this->getPath())) {
            $this->dictionary = Neon::decode(file_get_contents($this->getPath()));
        }

        // process default translate
        $this->searchDefaultTranslate();
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
        $this->dictionary[$identification] = $message;  // add to dictionary
        file_put_contents($this->getPath(), Neon::encode($this->dictionary, Neon::BLOCK));   // save to file
        return $message;
    }
}
