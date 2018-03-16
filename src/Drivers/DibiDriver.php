<?php declare(strict_types=1);

namespace Translator\Drivers;

use dibi;
use Translator\Translator;
use Locale\ILocale;
use Dibi\Connection;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * Class DibiDriver
 *
 * Dibi translator with support plurals.
 *
 * @author  geniv
 * @package Translator\Drivers
 */
class DibiDriver extends Translator
{
    // define constant table names
    const
        TABLE_NAME = 'translation',
        TABLE_NAME_IDENT = 'translation_ident';

    /** @var Cache data cache */
    private $cache;
    /** @var string name cache key */
    private $cacheKey;
    /** @var Connection database connection from DI */
    protected $connection;
    /** @var string table names */
    private $tableTranslate, $tableTranslateIdent;


    /**
     * DibiDriver constructor.
     *
     * @param string     $prefix
     * @param Connection $connection
     * @param ILocale    $locale
     * @param IStorage   $storage
     */
    public function __construct(string $prefix, Connection $connection, ILocale $locale, IStorage $storage)
    {
        parent::__construct($locale);

        // define table names
        $this->tableTranslate = $prefix . self::TABLE_NAME;
        $this->tableTranslateIdent = $prefix . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->cache = new Cache($storage, 'cache-TranslatorDrivers-DibiDriver');

        // key for cache
        $this->cacheKey = 'dictionary' . $this->locale->getId();

        // load translate
        $this->loadCache();
    }


    /**
     * Load cache.
     *
     * @internal
     */
    private function loadCache()
    {
        $this->dictionary = $this->cache->load($this->cacheKey);
        if ($this->dictionary === null) {
            $this->loadTranslate();
            $this->saveCache();
        }
    }


    /**
     * Save cache.
     *
     * @internal
     */
    private function saveCache()
    {
        $this->cache->save($this->cacheKey, $this->dictionary, [
            Cache::EXPIRE => '30 minutes',
            Cache::TAGS   => ['saveCache'],
        ]);
    }


    /**
     * Get id identification.
     *
     * @internal
     * @param string $identification
     * @return int
     * @throws \Dibi\Exception
     */
    private function getIdIdentification(string $identification): int
    {
        $result = $this->connection->select('id')
            ->from($this->tableTranslateIdent)
            ->where(['ident' => $identification])
            ->fetchSingle();

        if (!$result) {
            $result = $this->connection->insert($this->tableTranslateIdent, [
                'ident' => $identification,
            ])->execute(Dibi::IDENTIFIER);  // must return last insert ID
        }
        return $result;
    }


    /**
     * Load translate.
     */
    protected function loadTranslate()
    {
        $this->dictionary = $this->connection->select('t.id, i.ident, IFNULL(lo_t.translate, t.translate) translate')
            ->from($this->tableTranslate)->as('t')
            ->join($this->tableTranslateIdent)->as('i')->on('i.id=t.id_ident')
            ->leftJoin($this->tableTranslate)->as('lo_t')->on('lo_t.id_ident=i.id')->and('lo_t.id_locale=%i', $this->locale->getId())
            ->where(['t.id_locale' => null])
            ->groupBy('i.id')
            ->fetchPairs('ident', 'translate');
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
        $values = [
            'id_locale' => $idLocale,   // linked to locale
            'id_ident'  => $this->getIdIdentification($identification), // linked to indentity
            'translate' => $message,
        ];
        //TODO bacha na NULL hodnoty!
        $this->connection->insert($this->tableTranslate, $values)->onDuplicateKeyUpdate('%a', $values)->execute();

        $this->dictionary[$identification] = $message;   // add to dictionary
        $this->saveCache();

        return $message;    // return message
    }


    /**
     * Search translate.
     *
     * @param array $identifications
     * @return array
     */
    public function searchTranslate(array $identifications): array
    {
        $locales = $this->connection->select('t.id, b.ident, GROUP_CONCAT(t.id_locale) locales, t.translate')
            ->from($this->tableTranslate)->as('t')
            ->join($this->tableTranslateIdent)->as('b')->on('b.id=t.id_ident')
            ->where('b.ident IN %in', $identifications)
            ->groupBy('b.ident')
            ->fetchPairs('ident', 'locales');

        return array_map(function ($r) {
            return ($r ? explode(',', $r) : null);
        }, $locales);
    }
}
