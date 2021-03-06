<?php declare(strict_types=1);

namespace Translator;

use Locale\ILocale;
use Nette\SmartObject;
use SearchContent;


/**
 * Class Translator
 *
 * @author  geniv
 * @package Translator
 */
abstract class Translator implements ITranslator
{
    use SmartObject;

    /** @var ILocale */
    protected $locale;
    /** @var array */
    protected $dictionary = [];
    /** @var string */
    protected $plural;
    /** @var array */
    private $listAllDefaultTranslate = [], $listUsedIndex = [];
    /** @var SearchContent */
    private $searchContent;


    /**
     * Translator constructor.
     *
     * @param ILocale $locale
     */
    public function __construct(ILocale $locale)
    {
        $this->locale = $locale;

        // example: '$plural=(n==1) ? 0 : ((n>=2 && n<=4) ? 1 : 2);'
        // via: http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html
    }


    /**
     * Add used index.
     *
     * @internal
     * @param string $index
     */
    private function addUsedIndex(string $index)
    {
        // add user index and detect default translate (default: index == translate)
        $this->listUsedIndex[$index] = (isset($this->dictionary[$index]) && $this->dictionary[$index] != $index);
    }


    /**
     * Translates the given string.
     *
     * @param  mixed    message
     * @param  int      plural count
     * @return string
     */
    public final function translate($message, $count = NULL)
    {
        // load if is first usage
        if ($this->locale->isReady() && !$this->dictionary) {
            // set plurals from locales to translate always current language
            $this->plural = $this->locale->getPlural();
//            \Tracy\Debugger::fireLog('Translator::translate, loadTranslate');
            $this->loadTranslate();  // load data
        }

        $indexDictionary = $message; // message is index (identification) for translation

        if ($message) {
            if (isset($count)) {
                if (isset($this->plural)) {
                    if (!is_array($count)) {
                        $plural = null; // input variable plural for eval
                        $n = $count;    // input variable count for eval
                        eval($this->plural);    // evaluate plural
                        $pluralFormat = '%s:plural:%d'; // create format plural
                        $pluralIndex = sprintf($pluralFormat, $indexDictionary, $plural);   // main substitute plural form
                        $this->addUsedIndex($pluralIndex);
                        if (!isset($this->dictionary[$pluralIndex])) {
                            // make other plural form by $nplurals
                            if (isset($nplurals)) {
                                // iterate over $nplurals
                                for ($i = 0; $i < $nplurals; $i++) {
                                    $this->saveTranslate(sprintf($pluralFormat, $indexDictionary, $i), $message);   // create plural index
                                }
                                return $message;    // return message
                            } else {
                                return $this->saveTranslate($pluralIndex, $message);    // create plural without $nplurals
                            }
                        }
                        return sprintf($this->dictionary[$pluralIndex], $count);    // substitute value
                    } else {
                        $this->addUsedIndex($indexDictionary);
                        if (!isset($this->dictionary[$indexDictionary])) {
                            return $this->saveTranslate($indexDictionary, $message);    // create & return
                        }
                        // if plural enable but $count is array
                        return vsprintf($this->dictionary[$indexDictionary], $count);   // array
                    }
                } else {
                    $this->addUsedIndex($indexDictionary);
                    if (!isset($this->dictionary[$indexDictionary])) {
                        return $this->saveTranslate($indexDictionary, $message);    // create & return
                    }

                    if (!is_array($count)) {
                        // count is value
                        return sprintf($this->dictionary[$indexDictionary], $count);    // value
                    } else {
                        // count is array
                        return vsprintf($this->dictionary[$indexDictionary], $count);   // array
                    }
                }
            } else {
                $this->addUsedIndex($indexDictionary);
                if (!isset($this->dictionary[$indexDictionary])) {
                    return $this->saveTranslate($indexDictionary, $message);    // create & return
                }
                return $this->dictionary[$indexDictionary];
            }
        }
        return '';
    }


    /**
     * Set path search.
     *
     * @internal
     * @param array $searchMask
     * @param array $searchPath
     * @param array $excludePath
     */
    public function setSearchPath(array $searchMask = [], array $searchPath = [], array $excludePath = [])
    {
        $this->searchContent = new SearchContent($searchMask, $searchPath, $excludePath);
    }


    /**
     * Search default translate.
     *
     * @internal
     */
    protected function searchDefaultTranslate()
    {
        // call in: loadInternalData()
        if ($this->searchContent) {
            $this->listAllDefaultTranslate = $this->searchContent->getList();

            if ($this->dictionary && $this->listAllDefaultTranslate) {
                // if define dictionary
                foreach ($this->listAllDefaultTranslate as $identification => $item) {
                    if ($item['type'] == 'translation') {
                        $message = $item['value'];
                        // save only not exist identification and only string message or identification is same like dictionary index (default translate)
//                        if ((!isset($this->dictionary[$identification]) && !is_array($message)) || $this->dictionary[$identification] == $identification) {
                        if (!isset($this->dictionary[$identification]) || $this->dictionary[$identification] == $identification) {
                            // call only save default value load from files
                            $this->saveTranslate($identification, $message);
                        }
                    }
                }
            }
        }
    }


    /**
     * Get list default translate.
     *
     * @internal
     * @return array
     */
    public function getListDefaultTranslate(): array
    {
        return $this->searchContent->getListCategory();
    }


    /**
     * Get list all default translate.
     *
     * @internal
     * @return array
     */
    public function getListAllDefaultTranslate(): array
    {
        return $this->listAllDefaultTranslate;
    }


    /**
     * Get list used translate.
     *
     * @internal
     * @return array
     */
    public function getListUsedTranslate(): array
    {
        return $this->listUsedIndex;
    }


    /**
     * Get dictionary.
     *
     * @return array
     */
    public function getDictionary(): array
    {
        return $this->dictionary;
    }


    /**
     * Create translate.
     *
     * @param      $identification
     * @param      $message
     * @param null $idLocale
     * @return string
     */
    public function createTranslate(string $identification, string $message, $idLocale = null): string
    {
        if (isset($this->dictionary) && $this->dictionary) {
            if (!isset($this->dictionary[$identification]) || $this->dictionary[$identification] != $message) {
                $this->saveTranslate($identification, $message, $idLocale ?: $this->locale->getId());
            }
            return $this->dictionary[$identification];
        }
        return $message;
    }


    /**
     * Load translate.
     *
     * @internal
     */
    abstract protected function loadTranslate();


    /**
     * Save translate.
     *
     * @internal
     * @param string $identification
     * @param        $message
     * @param null   $idLocale
     * @return string
     */
    abstract protected function saveTranslate(string $identification, $message, $idLocale = null): string;
}
