# Xliff Parser for PHP #

This library help in the process of creating and navigating within an XLIFF document.

## Usage Example ##

	<?php
	
	require_once '../src/XliffDocument.php';
	
	echo "Generating new XLIFF document:" . PHP_EOL;
	$xliff = new XliffDocument();
	
	$xliff
		//create a new file element
		->file(TRUE)
			//create a new body element
			->body(TRUE)
				//create a new trans-unit element
				->unit(TRUE)
					//create a new source element
					->source(TRUE)
						->setTextContent("text one")
						->setAttribute('xml:lang', 'en');
		
	$xliff
		//use same file element as before
		->file()
			//use same body element as before
			->body()
				//use same trans-unit element as before
				->unit()
					//create a new target element
					->target(TRUE)
						->setTextContent("texte un")
						->setAttribute('xml:lang', 'fr');
		
	$xliff
		->file()
			->body()
				->unit(TRUE)
					->source(TRUE)
						->setTextContent("Hello world")
						->setAttribute('xml:lang', 'en');
	$xliff
		->file()
			->body()
				->unit()
					->target(TRUE)
						->setTextContent("Bonjour le monde")
						->setAttribute('xml:lang', 'fr');
	
	
	$dom = $xliff->toDOM();
	echo $dom->saveXML();
	
	echo '=============================================='.PHP_EOL;
	echo "Generating DOM from XLIFF document and back:" . PHP_EOL;
	$xliff2 = XliffDocument::fromDOM($dom);
	echo $xliff2->toDOM()->saveXML();
	
## Lots of work to be done... ##

1. Not all XLIFF tags and attributed are supported. Complete XLIFF docs are [here](http://docs.oasis-open.org/xliff/xliff-core/xliff-core.html).
2. Custom namespaces support
3. Unit-tests are needed.
4. More examples.

 
