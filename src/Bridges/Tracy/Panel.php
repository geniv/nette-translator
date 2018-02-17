<?php

namespace Translator\Bridges\Tracy;

use Latte\Engine;
use Locale\Locale;
use Nette\Application\Application;
use Translator\Translator;
use Latte\MacroTokens;
use Latte\Parser;
use Latte\PhpWriter;
use Nette\DI\Container;
use Nette\SmartObject;
use Tracy\Debugger;
use Tracy\IBarPanel;


/**
 * Class Panel
 *
 * @author  geniv
 * @package Translator\Bridges\Tracy
 */
class Panel implements IBarPanel
{
    use SmartObject;

    /** @var Translator translator from DI */
    private $translator;
    /** @var Container container from DI */
    private $container;


    /**
     * Panel constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }


    /**
     * Register to Tracy.
     *
     * @param Translator $translator
     */
    public function register(Translator $translator)
    {
        $this->translator = $translator;
        Debugger::getBar()->addPanel($this);
    }


    /**
     * Renders HTML code for custom tab.
     *
     * @return string
     */
    public function getTab()
    {
        return '<span title="Translator"><img width="16px" height="16px" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiA/PjxzdmcgaGVpZ2h0PSI0OCIgdmlld0JveD0iMCAwIDQ4IDQ4IiB3aWR0aD0iNDgiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTAgMGg0OHY0OGgtNDh6IiBmaWxsPSJub25lIi8+PHBhdGggZD0iTTI1Ljc0IDMwLjE1bC01LjA4LTUuMDIuMDYtLjA2YzMuNDgtMy44OCA1Ljk2LTguMzQgNy40Mi0xMy4wNmg1Ljg2di00LjAxaC0xNHYtNGgtNHY0aC0xNHYzLjk4aDIyLjM0Yy0xLjM1IDMuODYtMy40NiA3LjUyLTYuMzQgMTAuNzItMS44Ni0yLjA3LTMuNC00LjMyLTQuNjItNi43aC00YzEuNDYgMy4yNiAzLjQ2IDYuMzQgNS45NiA5LjEybC0xMC4xNyAxMC4wNSAyLjgzIDIuODMgMTAtMTAgNi4yMiA2LjIyIDEuNTItNC4wN3ptMTEuMjYtMTAuMTVoLTRsLTkgMjRoNGwyLjI1LTZoOS41bDIuMjUgNmg0bC05LTI0em0tNS4yNSAxNGwzLjI1LTguNjcgMy4yNSA4LjY3aC02LjV6Ii8+PC9zdmc+" />' .
            'Translator' .
            '</span>';
    }


    /**
     * Renders HTML code for custom panel.
     *
     * @return string
     * @throws \Latte\CompileException
     */
    public function getPanel()
    {
        $locale = $this->container->getByType(Locale::class);   // nacteni lokalizacni sluzby
        $application = $this->container->getByType(Application::class);    // nacteni aplikace
        $presenter = $application->getPresenter();  // nacteni presenteru

        $translateMap = new TranslateMap;
        // vyrazeni prekladu z @layout
        $layoutLatte = dirname($presenter->template->getFile()) . '/../@layout.latte';
        $layoutTranslate = (file_exists($layoutLatte) ? $this->extractFile($layoutLatte, $translateMap) : []);
        // vytazeni prekladu z aktualniho souboru
        $contentTranslate = ($presenter->template->getFile() ? $this->extractFile($presenter->template->getFile(), $translateMap) : []);

        $params = [
            // locales
            'locales'          => $locale->getLocales(),
            'localeCode'       => $locale->getCode(),
            // translates
            'translateLayout'  => $layoutTranslate,
            'translateContent' => $contentTranslate,
            'translateClass'   => get_class($this->translator),
            'translateSearch'  => $this->translator->searchTranslate(array_merge($layoutTranslate, $contentTranslate)),   // vyhledani prekladu v driveru prekladace
            'translatesMap'    => $translateMap->toArray(), // mapa umisteni prekladu
        ];

        $latte = new Engine;
        return $latte->renderToString(__DIR__ . '/PanelTemplate.latte', $params);
    }


    /**
     * Extracts translation messages from a file.
     *
     * @param $file
     * @param $translateMap
     * @return array
     * @throws \Latte\CompileException
     */
    public function extractFile($file, $translateMap)
    {
        $buffer = null;
        $parser = new Parser();

        $result = [];
        $tokens = $parser->parse(file_get_contents($file));
        foreach ($tokens as $token) {

            // vylouceni zbytecnych tagu
            if ($token->type !== $token::MACRO_TAG || !in_array($token->name, ['_', '/_', 'include'], true)) {
                // pokud neni buffer null tak vklada text
                if ($buffer !== null) {
                    $buffer .= $token->text;
                }
                continue;
            }

            // pokud je konec prekladu a nebo jednoduchy uzavreny preklad tak preda buffer
            if ($token->name === '/_' || ($token->name === '_' && $token->closing === true)) {
                $result[] = $buffer;
                $translateMap->add($buffer, realpath($file), $token->line);

                $buffer = null;

                // pokud nazazi na blok include
            } elseif ($token->name === 'include') {

                // vezme aktualni slozku, spoji s hodnotou includ, pokud existuje tak na ni rekurzivne aplikuje extractFile
                $res = null;
                if (file_exists(dirname($file) . '/' . $token->value)) {
                    $res = $this->extractFile(dirname($file) . '/' . $token->value, $translateMap);
                }

                // slouceni pole nactenych z include bloku
                if ($res) {
                    $result = array_merge($result, $res);
                }

                // pokud je zacatek prekladu a je prazdna hodnota tak vyprazdni buffer
            } elseif ($token->name === '_' && !$token->value) {
                $buffer = '';

            } else {
                $writer = new PhpWriter(new MacroTokens($token->value), $token->modifiers);
                $message = $writer->write('%node.word');
                // pokud text obsahuje uvozovku, apostrof, tak vezme text mezi znaky
                if (in_array(substr(trim($message), 0, 1), ['"', '\''], TRUE)) {
                    $message = substr(trim($message), 1, -1);
                }
                $result[] = $message;
                $translateMap->add($message, realpath($file), $token->line);
            }
        }
        return $result;
    }
}
