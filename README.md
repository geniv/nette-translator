Translator
==========

This translator is target for save ident in message, also default text is for id_locale=NULL, 
next language must be translate manual.

Plurals source: http://docs.translatehouse.org/projects/localization-guide/en/latest/l10n/pluralforms.html

Installation
------------

```sh
$ composer require geniv/nette-translator
```
or
```json
"geniv/nette-translator": ">=1.0.0"
```

require:
```json
"php": ">=7.0.0",
"nette/nette": ">=2.4.0",
"dibi/dibi": ">=3.0.0",
"geniv/nette-locale": ">=1.0.0",
"geniv/nette-configurator": ">=2.0.0"
```

Include in application
----------------------

available source drivers:
- DibiDriver (dibi + cache, self translation db table)
- NeonDriver (filesystem in neon syntax)
- DevNullDriver (ignore translate)
- ConfiguratorDriver (configurator dibi + cache storage)

neon configure:
```neon
# translator
translator:
#   debugger: true
#   autowired: true
#   driver: Translator\Drivers\DevNullDriver
#   driver: Translator\Drivers\NeonDriver(%appDir%)
#   driver: Translator\Drivers\DibiDriver(%tablePrefix%)
    driver: Translator\Drivers\ConfiguratorDriver
    searchPath:
        - %appDir%/../vendor/geniv  # first vendor
        - %appDir%
        - %appDir%/presenters/CustomTranslation.neon
```

`path` is configure for system search default translations.
Default translation system has name convection `*Translation.neon`, eg: `AppTranslation.neon`
Names in dirs are sort with function natsort().
It is possible set manual file.
This neon file has format: `myIndent: "MyDefaultMessage"`

neon configure extension:
```neon
extensions:
    translator: Translator\Bridges\Nette\Extension
```

usage:
```latte
{* standard translating *}
{_'preklad'}

{* plural translating *}
{_'preklad', $pocet}

{* substitution translating *}
{_'preklad', [$pocet]}
```

**this latte macro is not supported, because like index must use be simple string like `{_'xyz'}`**:
```latte
{_}translate{/_}
```

presenters:
```php
/** @var ITranslator @inject */
public $translator;

$form = new \Nette\Application\UI\Form;
$form->setTranslator($this->translator);
```
or
```php
$this->translator->translate('message-ident');

$this->translator->translate('message-ident', 123);             // inside %s

$this->translator->translate('message-ident', ['hello', 123]);  // inside %s, %s
```
