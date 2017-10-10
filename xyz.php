<pre><?php
$sDir = 'C:\Users\Rik\Google Drive\PhD\Project\Tetrafluoroborate\33\Coordinates';
$aMolecules = [];
foreach (glob(sprintf('%s/*.xyz', $sDir)) as $sFile) {
    $sName = substr(basename($sFile, '.xyz'), 3);
    $sContents = file_get_contents($sFile);
    $sContents = str_replace("\r", null, $sContents);
    $sContents = str_replace("\n\n", sprintf("\n%s\n", $sName), $sContents);
    $aMolecules[] = $sContents;
}
echo implode("\n", $aMolecules);