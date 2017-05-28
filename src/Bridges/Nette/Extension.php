<?php

namespace Translator\Bridges\Nette;

use Exception;
use Nette\DI\CompilerExtension;
use Tracy\IBarPanel;
use Translator\Bridges\Tracy\Panel;
use Translator\Drivers\DatabaseDriver;
use Translator\Drivers\DevNullDriver;
use Translator\Drivers\NeonDriver;


/**
 * Class Extension
 *
 * nette extension pro zavadeni jazykove sluzby jako rozsireni
 *
 * @author  geniv
 * @package Translator\Bridges\Nette
 */
class Extension extends CompilerExtension
{

    /**
     * Load configuration.
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig();

        if (!isset($config['parameters'])) {
            throw new Exception('Parameters is not defined! (' . $this->name . ':{parameters: {...}})');
        }

        // definice driveru
        switch ($config['source']) {
            case 'DevNull':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass(DevNullDriver::class);
                break;

            case 'Database':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass(DatabaseDriver::class, [$config['parameters']]);
                break;

            case 'Neon':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass(NeonDriver::class, [$config['parameters']]);
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

        // pripojeni fitru do latte
        $builder->getDefinition('latte.latteFactory')
            ->addSetup('addFilter', ['translate', [$this->prefix('@default'), 'translate']]);

        // pripojeni panelu do tracy
        $builder->getDefinition($this->prefix('default'))
            ->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
    }
}
