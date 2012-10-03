<?php

require_once '../src/XliffDocument.php';

echo "Generating new XLIFF document:" . PHP_EOL;
$xliff = new XliffDocument();

$xliff->file(TRUE)->body(TRUE)->unit(TRUE)->source(TRUE)
	->setTextContent("text 1")
	->setAttribute('xml:lang', 'en');
	
$xliff->file()->body()->unit()->target(TRUE)
	->setTextContent("1 txet")
	->setAttribute('xml:lang', 'fr');
	
$xliff->file()->body()->unit(TRUE)->source(TRUE)
	->setTextContent("Hello world")
	->setAttribute('xml:lang', 'en');
$xliff->file()->body()->unit()->target(TRUE)
	->setTextContent("world hello")
	->setAttribute('xml:lang', 'fr');


$dom = $xliff->toDOM();
echo $dom->saveXML();

echo '=============================================='.PHP_EOL;
echo "Generating DOM from XLIFF document and back:" . PHP_EOL;
$xliff2 = XliffDocument::fromDOM($dom);
//var_dump($xliff2);
echo $xliff2->toDOM()->saveXML();


