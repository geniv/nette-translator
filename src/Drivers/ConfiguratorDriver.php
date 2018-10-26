<?php declare(strict_types=1);

namespace Translator\Drivers;

use Configurator\IConfigurator;
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
//TODO move to configurator - separate?!


    /**
     * ConfiguratorDriver constructor.
     *
     * @param string        $identification
     * @param ILocale       $locale
     * @param IConfigurator $configurator
     * @param IStorage      $storage
     */
    public function __construct(string $identification = '', ILocale $locale, IConfigurator $configurator, IStorage $storage)
    {
        parent::__construct($locale);

        $this->identification = $identification ?: self::TRANSLATION_IDENTIFICATION;
        $this->configurator = $configurator;

        $this->cache = new Cache($storage, 'Translator-Drivers-ConfiguratorDriver');
    }


    /**
     * Load translate.
     */
    protected function loadTranslate()
    {
        $cacheKey = 'dictionary' . $this->locale->getId();
//        \Tracy\Debugger::fireLog('ConfiguratorDriver::loadTranslate; cacheKey ' . $cacheKey);
        $this->dictionary = $this->cache->load($cacheKey);
        if ($this->dictionary === null) {
            $this->dictionary = $this->configurator->getListDataByType($this->identification)
                ->fetchPairs('ident', 'content');

            try {
                $this->cache->save($cacheKey, $this->dictionary, [
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
