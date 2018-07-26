<?php declare(strict_types=1);

namespace Translator\Drivers;

use IConfigurator;
use Locale\ILocale;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
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
    /** @var IConfigurator */
    private $configurator;
    /** @var Cache */
    private $cache;
    /** @var string */
    private $cacheKey;


    /**
     * ConfiguratorDriver constructor.
     *
     * @param string        $identification
     * @param ILocale       $locale
     * @param IConfigurator $configurator
     * @param IStorage      $storage
     */
    public function __construct($identification = '', ILocale $locale, IConfigurator $configurator, IStorage $storage)
    {
        parent::__construct($locale);

        $this->identification = $identification ?: self::TRANSLATION_IDENTIFICATION;
        $this->configurator = $configurator;

        $this->cache = new Cache($storage, 'Translator-Drivers-ConfiguratorDriver');
        // key for cache
        $this->cacheKey = 'dictionary' . $this->locale->getId();

        // load translate
        $this->loadTranslate();
    }


    /**
     * Load translate.
     */
    protected function loadTranslate()
    {
        $this->dictionary = $this->cache->load('loadTranslate');
        if ($this->dictionary === null) {
            $this->dictionary = $this->configurator->getListDataByType($this->identification)
                ->fetchPairs('ident', 'content');

            try {
                $this->cache->save('loadTranslate', $this->dictionary, [
                    Cache::TAGS => ['saveCache'],
                ]);
            } catch (\Throwable $e) {
            }
        }
    }


    /**
     * Save translate.
     *
     * @param string $identification
     * @param        $message
     * @param null   $idLocale
     * @return string
     * @throws \Dibi\Exception
     */
    protected function saveTranslate(string $identification, $message, $idLocale = null): string
    {
        $method = 'set' . ucfirst($this->identification);
        return $this->configurator->$method($identification, $message);
    }
}
