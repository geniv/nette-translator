<?php

namespace Translator\Bridges\Nette;

use Nette;
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

        switch ($config['source']) {
            case 'DevNull':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass(DevNullDriver::class)
                    ->setInject(false);
                break;

            case 'Database':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass(DatabaseDriver::class, [$config['parameters']])
                    ->setInject(false);
                break;

            case 'Neon':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass(NeonDriver::class, [$config['parameters']])
                    ->setInject(false);
                break;
        }

        // pokud je debugmod a existuje rozhranni tak aktivuje panel
        if ($builder->parameters['debugMode'] && interface_exists(IBarPanel::class)) {
            $builder->addDefinition($this->prefix('panel'))
                ->setClass(Panel::class);

            $translator->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
        }
    }
}
