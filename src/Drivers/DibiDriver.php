<?php

namespace Translator\Drivers;

use Translator\Translator;
use Locale\ILocale;
use dibi;
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
     * @param array      $parameters
     * @param Connection $connection
     * @param ILocale    $locale
     * @param IStorage   $storage
     */
    public function __construct(array $parameters, Connection $connection, ILocale $locale, IStorage $storage)
    {
        parent::__construct($locale);

        // define table names
        $this->tableTranslate = $parameters['tablePrefix'] . self::TABLE_NAME;
        $this->tableTranslateIdent = $parameters['tablePrefix'] . self::TABLE_NAME_IDENT;

        $this->connection = $connection;
        $this->cache = new Cache($storage, 'cache-TranslatorDrivers-DibiDriver');

        // key for cache
        $this->cacheKey = 'dictionary' . $this->locale->getId();

        // nacteni prekladu
        $this->loadCache();
    }


    /**
     * Internal load cache.
     */
    private function loadCache()
    {
        $this->dictionary = $this->cache->load($this->cacheKey);
        if ($this->dictionary === null) {
            $this->dictionary = $this->loadTranslate();
            $this->saveCache();
        }
    }


    /**
     * Internal save cache.
     */
    protected function saveCache()
    {
        $this->cache->save($this->cacheKey, $this->dictionary, [
            Cache::EXPIRE => '30 minutes',
            Cache::TAGS   => ['saveCache'],
        ]);
    }


    /**
     * Load translate.
     *
     * @return mixed
     */
    protected function loadTranslate()
    {
        return $this->connection->select('t.id, i.ident, IFNULL(lo_t.translate, t.translate) translate')
            ->from($this->tableTranslate)->as('t')
            ->join($this->tableTranslateIdent)->as('i')->on('i.id=t.id_ident')
            ->leftJoin($this->tableTranslate)->as('lo_t')->on('lo_t.id_ident=i.id')->and('lo_t.id_locale=%i', $this->locale->getId())
            ->where(['t.id_locale' => null])
            ->groupBy('i.id')
            ->fetchPairs('ident', 'translate');
    }


    /**
     * Internal get id ident.
     *
     * @param $ident
     * @return mixed
     */
    private function getIdIdent($ident)
    {
        $result = $this->connection->select('id')
            ->from($this->tableTranslateIdent)
            ->where(['ident' => $ident])
            ->fetchSingle();

        if (!$result) {
            $result = $this->connection->insert($this->tableTranslateIdent, [
                'ident' => $ident,
            ])->execute(dibi::IDENTIFIER);  // must return last insert ID
        }
        return $result;
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
        $values = [
            'id_locale' => null,    // prazdna vazba na jazyk => defaultni preklad
            'id_ident'  => $this->getIdIdent($ident),      // ukladani identifikatoru
            'translate' => $message, // ukladani do zkratky jazyka
        ];

        $this->connection->insert($this->tableTranslate, $values)->execute();

        $this->dictionary[$ident] = $message;   // pridani slozeneho pole do slovniku
        $this->saveCache();

        // vraceni textu
        return $message;
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
        $values = [
            'id_locale' => $idLocale,
            'id_ident'  => $this->getIdIdent($ident),      // ukladani identifikatoru
            'translate' => $message, // ukladani do zkratky jazyka
        ];

        $this->connection->insert($this->tableTranslate, $values)->onDuplicateKeyUpdate('%a', $values)->execute();

        $this->dictionary[$ident] = $message;   // pridani slozeneho pole do slovniku
        $this->saveCache();
    }


    /**
     * Search translate by idents.
     *
     * @param array $idents
     * @return array
     */
    public function searchTranslate(array $idents)
    {
        $locales = $this->connection->select('t.id, b.ident, GROUP_CONCAT(t.id_locale) locales, t.translate')
            ->from($this->tableTranslate)->as('t')
            ->join($this->tableTranslateIdent)->as('b')->on('b.id=t.id_ident')
            ->where('b.ident IN %in', $idents)
            ->groupBy('b.ident')
            ->fetchPairs('ident', 'locales');

        return array_map(function ($r) {
            return ($r ? explode(',', $r) : null);
        }, $locales);
    }
}
