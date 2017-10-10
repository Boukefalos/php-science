<?php
$sDirectory = 'C:\Users\Rik\Dropbox\PhD\NMR';
$sTopspin = sprintf('C:\Users\Rik\.topspin-%s\prop\browsedir2_Local.prop', gethostname());

$aExclusions = array('mnova');

$aDirectories = array();
foreach (glob(sprintf('%s\*', $sDirectory)) as $sFile) {
    $sBase = basename($sFile);
    if (is_dir($sFile) && !in_array($sBase, $aExclusions)) {
        $aDirectories[] =  $sBase;
    }
}

natsort($aDirectories);
$aDirectories = array_values($aDirectories);

$aContents = array();
foreach ($aDirectories as $i => $sBase) {
    $sFormatted = sprintf('%s:/%s', substr($sDirectory, 0, 1), str_replace('\\', '/', substr($sDirectory, 3)));
    $aContents[] = sprintf('%d=%s/%s\=alias\=%3$s', $i, $sFormatted, $sBase, $sBase);
    
}
file_put_contents($sTopspin, implode("\n", $aContents));