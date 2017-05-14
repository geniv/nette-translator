<?php

namespace Translator\Bridges\Nette;

use Nette;
use Nette\DI\CompilerExtension;


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
                    ->setClass('Translator\Drivers\DevNullDriver')
                    ->setInject(false);
                break;

            case 'Database':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass('Translator\Drivers\DatabaseDriver', [$config['parameters']])
                    ->setInject(false);
                break;

            case 'Neon':
                $translator = $builder->addDefinition($this->prefix('default'))
                    ->setClass('Translator\Drivers\NeonDriver', [$config['parameters']])
                    ->setInject(false);
                break;
        }

        // pokud je debugmod a existuje rozhranni tak aktivuje panel
        if ($builder->parameters['debugMode'] && interface_exists('Tracy\IBarPanel')) {
            $builder->addDefinition($this->prefix('panel'))
                ->setClass('Translator\Bridges\Tracy\Panel');

            $translator->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
        }
    }
}
