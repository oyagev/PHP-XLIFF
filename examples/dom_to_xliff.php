<?php
require_once '../src/XliffDocument.php';

$doc = new DOMDocument();
$doc->loadXML(file_get_contents('/tmp/1.xliff'));
$xliff = XliffDocument::fromDOM($doc);

//var_dump($xliff);

var_dump($xliff->files());
