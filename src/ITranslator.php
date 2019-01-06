<?php declare(strict_types=1);

namespace Translator;


/**
 * Interface ITranslator
 *
 * @author  geniv
 * @package Translator
 */
interface ITranslator extends \Nette\Localization\ITranslator
{

    /**
     * Get list default translate.
     *
     * @internal
     * @return array
     */
    public function getListDefaultTranslate(): array;


    /**
     * Get list all default translate.
     *
     * @internal
     * @return array
     */
    public function getListAllDefaultTranslate(): array;


    /**
     * Get list used translate.
     *
     * @internal
     * @return array
     */
    public function getListUsedTranslate(): array;


    /**
     * Get dictionary.
     *
     * @return array
     */
    public function getDictionary(): array;


    /**
     * Clean cache.
     */
    public function cleanCache();
}
