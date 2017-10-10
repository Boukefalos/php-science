<?php
header('Content-Type: text/xml');
$sUrl = 'http://www.sciencemag.org/rss/current.xml';
$oXml = new SimpleXMLElement(file_get_contents($sUrl));
for ($i = count($oXml->item) - 1; $i >= 0; --$i) {
	$oItemXml = $oXml->item[$i];
	if (strpos($oItemXml->title, 'Chemistry:') === false) {
		unset($oXml->item[$i]);
	}
}
echo $oXml->asXML();