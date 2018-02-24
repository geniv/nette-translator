<?php declare(strict_types=1);

namespace Translator\Bridges\Tracy;

use Nette\SmartObject;


/**
 * Class TranslateMap
 *
 * internal class for mapping translation in latte.
 *
 * @author  geniv
 * @package Translator\Bridges\Tracy
 */
class TranslateMap
{
    use SmartObject;

    /** @var array */
    private $list = [];


    /**
     * Add.
     *
     * @param string $key
     * @param string $file
     * @param int    $line
     */
    public function add(string $key, string $file, int $line)
    {
        $dirs = explode('/', $file);
        $this->list[$key] = [
            'file' => implode('/', array_slice($dirs, -2)), // return 2 level from path
            'line' => $line,
        ];
    }


    /**
     * To array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->list;
    }
}
