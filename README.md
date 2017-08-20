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
"php": ">=5.6.0",
"nette/nette": ">=2.4.0",
"dibi/dibi": ">=3.0.0",
"geniv/nette-locale": ">=1.0.0"
```

Include in application
----------------------

available source drivers:
- Dibi (dibi + cache)
- Neon (filesystem in neon syntax)
- DevNull (ignore translate)

neon configure:
```neon
# translator
translator:
#   debugger: false
#   autowired: false    # default null, false => disable autowiring (in case multiple linked extension) | self
#   source: "DevNull"
    source: "Dibi"
    tablePrefix: %tablePrefix%
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

latte translate with devnull source for plurals:
```latte
{_'preklad', $pocet, ['%s 0x', '%s 1x', '%s 2x+']}
```

**this latte macro is not supported, because like index must use be simple string `{_'xyz'}`**:
```latte
{_}preklad{/_}
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
