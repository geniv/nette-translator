<?php

namespace Translator\Bridges\Nette;

use Nette\DI\CompilerExtension;
use Translator\Bridges\Tracy\Panel;
use Translator\Drivers\ConfiguratorDriver;
use Translator\Drivers\DibiDriver;
use Translator\Drivers\DevNullDriver;
use Translator\Drivers\NeonDriver;


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
        'debugger'    => true,
        'autowired'   => true,
        'source'      => 'DevNull', // DevNull|Dibi|Neon|Configurator
        'tablePrefix' => null,
        'path'        => null,
    ];


    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // define driver
        switch ($config['source']) {
            case 'DevNull':
                $builder->addDefinition($this->prefix('default'))
                    ->setFactory(DevNullDriver::class)
                    ->setAutowired($config['autowired']);
                break;

            case 'Dibi':
                $builder->addDefinition($this->prefix('default'))
                    ->setFactory(DibiDriver::class, [$config])
                    ->setAutowired($config['autowired']);
                break;

            case 'Neon':
                $builder->addDefinition($this->prefix('default'))
                    ->setFactory(NeonDriver::class, [$config])
                    ->setAutowired($config['autowired']);
                break;

            case 'Configurator':
                $builder->addDefinition($this->prefix('default'))
                    ->setFactory(ConfiguratorDriver::class, [$config])
                    ->setAutowired($config['autowired']);
                break;
        }

        // define panel
        if (isset($config['debugger']) && $config['debugger']) {
            $builder->addDefinition($this->prefix('panel'))
                ->setFactory(Panel::class)
                ->setAutowired($config['autowired']);
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

        if (isset($config['debugger']) && $config['debugger']) {
            // linked panel to tracy
            $builder->getDefinition($this->prefix('default'))
                ->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
        }
    }
}
