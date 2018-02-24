<?php declare(strict_types=1);

namespace Translator\Bridges\Nette;

use Nette\DI\CompilerExtension;
use Translator\Bridges\Tracy\Panel;


/**
 * Class Extension
 *
 * @author  geniv
 * @package Translator\Bridges\Nette
 */
class Extension extends CompilerExtension
{
    /** @var array default values */
    private $defaults = [
        'debugger'  => true,
        'autowired' => true,
        'driver'    => null,
        'path'      => null,
    ];


    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        $default=$builder->addDefinition($this->prefix('default'))
            ->setFactory($config['driver'])
            ->addSetup('setPath', [$config['path']])
            ->setAutowired($config['autowired']);

        // define panel
        if (isset($config['debugger']) && $config['debugger']) {
            $panel=$builder->addDefinition($this->prefix('panel'))
                ->setFactory(Panel::class)
                ->setAutowired($config['autowired']);
            $default->addSetup([$panel, 'register']);
        }
    }


    /**
     * Before Compile.
     */
    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // linked filter to latte
        $builder->getDefinition('latte.latteFactory')
            ->addSetup('addFilter', ['translate', [$this->prefix('@default'), 'translate']]);

//        if (isset($config['debugger']) && $config['debugger']) {
//            // linked panel to tracy
//            $builder->getDefinition($this->prefix('default'))
////                ->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
//                ->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
//        }
    }
}
