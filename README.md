# nette-translator
======

Translator

"geniv/nette-translator": ">=1.0"


# translator
translator:
#	source: "DevNull"
	source: "Database"
	parameters:
		table: %tb_translation%
#	source: "Neon"
#	parameters:
#		path: %appDir%


extensions:
	translator: Translator\Bridges\Nette\Extension
