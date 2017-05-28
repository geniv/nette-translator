Translator
==========

This translator is target for save ident in message, also default text is for id_locale=NULL, 
next language must be translate manual.

Installation
------------

```sh
$ composer require geniv/nette-translator
```
or
```json
"geniv/nette-translator": ">=1.0"
```

internal dependency:
```json
"nette/nette": ">=2.4.0",
"dibi/dibi": ">=3.0.0",
"geniv/nette-locale": ">=1.0"
```

Include in application
----------------------

available source drivers:
- database (dibi + cache)
- neon (filesystem)
- devnull (ignore translate)

neon configure:
```neon
# translator
translator:
#   source: "DevNull"
    source: "Database"
    table: %tb_translation%
#   source: "Neon"
#   path: %appDir%
```

neon configure extension:
```neon
extensions:
    translator: Translator\Bridges\Nette\Extension
```

usage:
```latte
{_'preklad'}
{_'preklad', $pocet}
```

latte translate with devnull source
```latte
{_'preklad', $pocet, ['%s 0x', '%s 1x', '%s 2x+']}
```

presenters:
```php
/** @var ITranslator @inject */
public $translator;

// nastaveni na formular
$form = new \Nette\Application\UI\Form;
$form->setTranslator($this->translator);
```
or
```php
// prelozeni textu
$this->translator->translate('message-ident');
```
