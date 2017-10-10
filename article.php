<?php
set_time_limit(0);
header('Content-Type: text/html;charset=utf-8');
?>
<pre><?php
/* Load dependencies */
require_once 'bibtex.php';

$sDois = <<<DOIS

DOIS;

$sKeys = <<<KEYS

KEYS;

$aKeys = array();
$aDuplicates = array();
foreach (explode("\n", trim($sKeys)) as $sKey) {
    $sKey = trim(current((explode('(', $sKey))));
    $aMatch = array();
    preg_match('~([a-z]+)([\d]+)([[a-z]*)~i', $sKey, $aMatch);
    if (isset($aMatch[2])) {
        $iYear = intval($aMatch[2]);
        $aKeys[$iYear] = isset($aKeys[$iYear]) ? array_merge($aKeys[$iYear], array($aMatch[1])) : array($aMatch[1]);
        if (!empty($aMatch[3])) {
            if (!isset($aDuplicates[$iYear][$aMatch[1]])) {
                $aDuplicates[$iYear][$aMatch[1]] = $aMatch[3];
            } else {
                $aDuplicates[$iYear][$aMatch[1]] .= sprintf('/%s', $aMatch[3]);
            }
        }
    }
}

$aKeys = array_map('array_unique', $aKeys);
$aDois = explode("\r\n", $sDois);
$aError = array();
foreach ($aDois as $sDoi) {
    if ($sDoi == null) continue;
    if (($sArticle = doi_bibtex($sDoi, $aKeys, $aDuplicates, $aError)) !== false) {
        echo $sArticle;
    }
}
if (!empty($aError)) {
    print_r($aError);
}