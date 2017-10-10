<pre><?php
$sFile = file_get_contents('.reference');
$sDatabase = file_get_contents($sFile);
preg_match_all('~@[\w]+\{(.*?),.*?\}(?!,)~s', $sDatabase, $aMatches);
foreach ($aMatches[0] as $i => $sOriginal) {
    preg_match_all('~[\s]+volume[\s]*=[\s]*\{.*?\},~si', $sOriginal, $aVolumes);
    $iCount = count($aVolumes[0]);
    if ($iCount > 1) {
        printf("Deleting %d volume fields from %s!\n", $iCount, $aMatches[1][$i]);
        $sNew = str_replace($aVolumes[0], null, $sOriginal);
        $sDatabase = str_replace($sOriginal, $sNew, $sDatabase);
    }
}
file_put_contents($sFile, $sDatabase);