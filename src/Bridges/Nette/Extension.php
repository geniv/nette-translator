<?php

namespace Translator\Bridges\Nette;

use Nette\DI\CompilerExtension;
use Translator\Bridges\Tracy\Panel;
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
        'autowired'   => null,
        'source'      => 'DevNull', // DevNull|Dibi|Neon
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

        // define driveru
        switch ($config['source']) {
            case 'DevNull':
                $builder->addDefinition($this->prefix('default'))
                    ->setClass(DevNullDriver::class);
                break;

            case 'Dibi':
                $builder->addDefinition($this->prefix('default'))
                    ->setClass(DibiDriver::class, [$config]);
                break;

            case 'Neon':
                $builder->addDefinition($this->prefix('default'))
                    ->setClass(NeonDriver::class, [$config]);
                break;
        }

        // if define autowired then set value
        if (isset($config['autowired'])) {
            $builder->getDefinition($this->prefix('default'))
                ->setAutowired($config['autowired']);
        }

        // define panel
        if (isset($config['debugger']) && $config['debugger']) {
            $builder->addDefinition($this->prefix('panel'))
                ->setClass(Panel::class);
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
