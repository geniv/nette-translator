<?php

namespace Translator\Bridges\Nette;

use Nette\DI\CompilerExtension;
use Translator\Bridges\Tracy\Panel;
use Translator\Drivers\DatabaseDriver;
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
    /** @var array vychozi hodnoty */
    private $defaults = [
        'debugger'    => true,
        'source'      => 'DevNull',
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

        // definice driveru
        switch ($config['source']) {
            case 'DevNull':
                $builder->addDefinition($this->prefix('default'))
                    ->setClass(DevNullDriver::class);
                break;

            case 'Database':
                $builder->addDefinition($this->prefix('default'))
                    ->setClass(DatabaseDriver::class, [$config]);
                break;

            case 'Neon':
                $builder->addDefinition($this->prefix('default'))
                    ->setClass(NeonDriver::class, [$config]);
                break;
        }

        // definice panelu
        $builder->addDefinition($this->prefix('panel'))
            ->setClass(Panel::class);
    }


    /**
     * Before Compile.
     */
    public function beforeCompile()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->validateConfig($this->defaults);

        // pripojeni fitru do latte
        $builder->getDefinition('latte.latteFactory')
            ->addSetup('addFilter', ['translate', [$this->prefix('@default'), 'translate']]);

        if ($config['debugger']) {
            // pripojeni panelu do tracy
            $builder->getDefinition($this->prefix('default'))
                ->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
        }
    }
}
