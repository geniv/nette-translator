<?php declare(strict_types=1);

namespace Translator\Bridges\Tracy;

use Nette\SmartObject;


/**
 * Class TranslateMap
 *
 * vnitrni trida pro mapovani umisteni prekladu v latte
 *
 * @author  geniv
 * @package Translator\Bridges\Tracy
 */
class TranslateMap
{
    use SmartObject;

    /** @var array list vales */
    private $list = [];


    /**
     * Insert value.
     *
     * @param $key
     * @param $file
     * @param $line
     */
    public function add($key, $file, $line)
    {
        $dirs = explode('/', $file);
        $this->list[$key] = [
            'file' => implode('/', array_slice($dirs, -2)), // vrati jen posledni 2 urovne cesty
            'line' => $line,
        ];
    }


    /**
     * Return as array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->list;
    }
}
