<?php

require_once '../src/XliffDocument.php';

$xliff = new XliffDocument();
$xliff->file()->body()->unit()->source()->setTextContent("text 1");
$xliff->file()->body()->unit()->target()->setTextContent("1 txet");


$dom = $xliff->toDOM();
echo $dom->saveXML();