<pre><?php
$sDirectory = 'C:\Users\Rik\Git\latex\Report\phd-thesis';

$oDirectory = new RecursiveDirectoryIterator($sDirectory);
$oIterator = new RecursiveIteratorIterator($oDirectory);

define('NEED', 'Notes.tex');

$aCites = [];
foreach ($oIterator as $oFile) {
    $sFile = (string) $oFile;
    if (strpos($sFile, '.tex') && !strpos($sFile, '-tmp')) {
        $sFilePath = substr(str_replace($sDirectory, null, $sFile), 1);
        $sContents = file_get_contents($sFile);
        preg_match_all('~cite\{([^\}]+)\}~', $sContents, $aMatches, PREG_SET_ORDER);
        foreach ($aMatches as $aCite) {
            $sCites = str_replace(', ', ',', $aCite[1]);
            foreach (explode(',', $sCites) as $sCite) {
                if (isset($aCites[$sCite])) {
                     $aCites[$sCite][] = $sFilePath;
                    $aCites[$sCite] = array_unique($aCites[$sCite]);
                } else {
                    $aCites[$sCite] = array($sFilePath);
                }
            }
        }
    }
}

$iRepeated = 0;
$sBuffer = null;
ksort($aCites);
foreach ($aCites as $sCite => $aFiles) {
    $iCount = count($aFiles);
    if ($iCount > 1) {
        if (defined('NEED')) {
            $bFound = false;
            foreach ($aFiles as $sFile) {
                if (basename($sFile) == NEED) {
                    $bFound = true;
                }
            }
            if (!$bFound) {
                continue;
            }
        }
        ++$iRepeated;
        $sBuffer .= sprintf("<strong>%s=%d</strong>\n", $sCite, $iCount);
        foreach ($aFiles as $sFile) {
            $sBuffer .= sprintf("    - %s\n", $sFile);
        }
    }
    if (isset($aStats[$iCount])) {
        ++$aStats[$iCount];
    } else {
        $aStats[$iCount] = 1;
    }
}

printf("<em>Total number of unique citations: %d</em>\n\n", count($aCites));
printf("<em>Number of repeated citations: %d</em>\n\n", $iRepeated);
printf("%s\n\n", $sBuffer);

ksort($aStats);
foreach ($aStats as $iCount => $iNumber) {
    printf("%d > %d\n", $iCount, $iNumber);
}