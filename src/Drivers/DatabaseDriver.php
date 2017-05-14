<?php

namespace TranslatorServices\Drivers;

use TranslatorService\TranslatorService;
use LocaleServices\LocaleService;
use dibi;
use Dibi\Connection;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Exception;


/**
 * Class DatabaseDriver
 *
 * databazovy translator s podporou Pluralu
 *
 * @author  geniv
 * @package TranslatorServices\Drivers
 */
class DatabaseDriver extends TranslatorService
{
    private $cache, $cacheKey, $idLocale;
    protected $database, $tableTranslate, $tableTranslateIdent;


    /**
     * DatabaseDriver constructor.
     *
     * @param array         $parameters
     * @param Connection    $database
     * @param LocaleService $localeService
     * @param IStorage      $cacheStorage
     * @throws Exception
     */
    public function __construct(array $parameters, Connection $database, LocaleService $localeService, IStorage $cacheStorage)
    {
        parent::__construct($localeService);

        // pokud parametr table neexistuje
        if (!isset($parameters['table'])) {
            throw new Exception('Parameters table name is not defined in configure! (table: xy)');
        }
        // nacteni jmena tabulky
        $tableTranslate = $parameters['table'];

        $this->database = $database;
        $this->tableTranslate = $tableTranslate;
        $this->tableTranslateIdent = $tableTranslate . '_ident';

        $this->cache = new Cache($cacheStorage, 'cache' . __CLASS__);

        $this->idLocale = $this->localeService->getId();
        // klic pro cache
        $this->cacheKey = 'dictionary' . $this->idLocale;

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
        return $this->database->select('t.id, i.ident, t.translate')
            ->from($this->tableTranslate)->as('t')
            ->join($this->tableTranslateIdent)->as('i')->on('i.id=t.id_ident')
            ->where('t.id_locale=%i OR t.id_locale IS NULL', $this->idLocale)
            ->fetchPairs('ident', 'translate');
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
        $arr = ['ident' => $index];
        // nacte identifikator
        $identifier = $this->database->select('id')
            ->from($this->tableTranslateIdent)
            ->where($arr)
            ->fetchSingle();
        // pokud se nenajde tak se vlozi novy
        if (!$identifier) {
            $identifier = $this->database->insert($this->tableTranslateIdent, $arr)
                ->onDuplicateKeyUpdate('%a', $arr)
                ->execute(Dibi::IDENTIFIER);
        }

        // vklada se bez vazby na jazyk,
        $values = [
            'id_locale' => null,    // prazdna vazba na jazyk => defaultni preklad
            'id_ident'  => $identifier,      // ukladani identifikatoru
            'translate' => $message, // ukladani do zkratky jazyka
        ];

        $this->database->insert($this->tableTranslate, $values)
            ->execute();
        $this->dictionary[$index] = $message;   // pridani slozeneho pole do slovniku
        $this->saveCache();

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
        $locales = $this->database->select('t.id, b.ident, GROUP_CONCAT(t.id_locale) locales, t.translate')
            ->from($this->tableTranslate)->as('t')
            ->join($this->tableTranslateIdent)->as('b')->on('b.id=t.id_ident')
            ->where('ident IN %in', $idents)
            ->groupBy('b.ident')
            ->fetchPairs('ident', 'locales');

        return array_map(function ($r) {
            return ($r ? explode(',', $r) : null);
        }, $locales);
    }
}
