<?php
set_time_limit(0);
$sList = 'C:\Users\Rik\Downloads\OHBF3.gcd';
$sData = 'C:\Users\Rik\Downloads\data-OHBF3.csv';
$aList = explode("\n", trim(file_get_contents($sList)));
$bHead = false;
$aHead = [];
$aEntries = [];
foreach ($aList as $sEntry) {
    $sUrl = sprintf('https://summary.ccdc.cam.ac.uk/structure-summary?refcode=%s', trim($sEntry));
    $sContents = file_get_contents($sUrl);
    $aParts = explode('<script>', $sContents);
    if (!isset($aParts[3])) continue;
    $sJavascript = trim(current(explode('</script>', $aParts[3])));
    preg_match_all('~"([\w]+)":\s*"([^"]+)"~', $sJavascript, $aMatches, PREG_PATTERN_ORDER);
    $aData = [];
    $aHead = array_unique(array_merge($aHead, $aMatches[1]));
    foreach ($aMatches[1] as $iKey => $sKey) {
        $aData[$sKey] = $aMatches[2][$iKey];
    }
    $aEntries[] = $aData;
    printf("%s\n", $aData['RefCode']);
}
file_put_contents($sData, implode("\t", $aHead) . PHP_EOL);
$aEmpty = array_flip($aHead);
foreach ($aEntries as $aEntry) {
    $aEntry = array_merge($aEmpty, $aEntry);
    file_put_contents($sData, implode("\t", $aEntry) . PHP_EOL, FILE_APPEND);    
}