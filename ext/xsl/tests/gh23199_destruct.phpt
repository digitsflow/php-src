--TEST--
Fix GH-23199: importStylesheet() handles document destruction cleanly
--SKIPIF--
<?php
if (!extension_loaded("xsl")) print "skip xsl not loaded";
?>
--FILE--
<?php
class GuardedDoc extends DOMDocument {
    public function __destruct() {
        @$name = $this->documentElement ? $this->documentElement->nodeName : null;
    }
}

$xsl = new XSLTProcessor();
$doc = new GuardedDoc();
$doc->loadXML('<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"/>');

$xsl->importStylesheet($doc);
unset($xsl);
unset($doc);
echo "PASS\n";
?>
--EXPECT--
PASS
