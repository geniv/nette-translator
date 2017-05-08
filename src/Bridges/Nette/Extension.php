<?php

namespace TranslatorServices\Bridges\Nette;

use Nette;
use Nette\DI\CompilerExtension;


/**
 * Class Extension
 *
 * nette extension pro zavadeni jazykove sluzby jako rozsireni
 *
 * @author  geniv
 * @package TranslatorServices\Bridges\Nette
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
                $translatorService = $builder->addDefinition($this->prefix('default'))
                    ->setClass('TranslatorServices\Drivers\DevNull')
                    ->setInject(false);
                break;

            case 'Database':
                $translatorService = $builder->addDefinition($this->prefix('default'))
                    ->setClass('TranslatorServices\Drivers\Database', [$config['parameters']])
                    ->setInject(false);
                break;

            case 'Neon':
                $translatorService = $builder->addDefinition($this->prefix('default'))
                    ->setClass('TranslatorServices\Drivers\Neon', [$config['parameters']])
                    ->setInject(false);
                break;
        }

        // pokud je debugmod a existuje rozhranni tak aktivuje panel
        if ($builder->parameters['debugMode'] && interface_exists('Tracy\IBarPanel')) {
            $builder->addDefinition($this->prefix('panel'))
                ->setClass('TranslatorServices\Bridges\Tracy\Panel');

            $translatorService->addSetup('?->register(?)', [$this->prefix('@panel'), '@self']);
        }
    }
}
