<?php

require_once './src/XliffDocument.php';

use PHPUnit\Framework\TestCase;

class XliffDocumentTest extends TestCase
{

    public function testXliffDocument()
    {
        echo PHP_EOL . "Generating new XLIFF document:" . PHP_EOL;
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
            ->setTextContent("text 1")
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
            ->setTextContent("1 txet")
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
            ->setTextContent("world hello")
            ->setAttribute('xml:lang', 'fr');


        $dom = $xliff->toDOM();
        echo $dom->saveXML();

        echo '=============================================='.PHP_EOL;
        echo "Generating DOM from XLIFF document and back:" . PHP_EOL;
        $xliff2 = XliffDocument::fromDOM($dom);
        echo $xliff2->toDOM()->saveXML();

        $this->assertNotEmpty($xliff2->toDOM()->saveXML());
    }

}
