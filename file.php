<pre><?php
set_time_limit(0);

/* Configuration */
$sFiles = 'C:\Users\Rik\Dropbox\Reference';
$sBibliography = file_get_contents('.reference');
$sDatabase = file_get_contents($sBibliography);

/* Load dependencies */
set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), '../../')));
require_once 'explore.php';


$aFound = [];
explore($sFiles, '\*.pdf', $aFound);

foreach ($aFound as $sFile) {
    $sBase = basename($sFile, '.pdf');
    if (strpos($sBase, '.') === false) {
        $aFiles[$sBase] = ltrim(str_replace($sFiles, null, $sFile), '\\/');
    }    
}

preg_match_all('~@[\w]+\{(.*?),.*?\},?[\s]*\}~s', $sDatabase, $aMatches);
foreach ($aMatches[0] as $i => $sOriginal) {
    $sKey = $aMatches[1][$i];
    if (isset($aFiles[$sKey])) {        
        $sFile = str_replace('\\', '\\\\', $aFiles[$sKey]);
        $sField = sprintf(':%s:PDF', $sFile);
        $sNew = preg_replace('~[\s]*file[\s]*=[\s]*\{[^\}]*\},?([\n\r])+~', '$1', $sOriginal);
        $aFields = explode(',', $sNew);
        $sNew = rtrim($sNew, "\n\r}");
        if (substr($sNew, -1) !== ',') {
            $sNew .= '},';
        }
        $sNew = sprintf("%s\nfile = {%s}\n}", $sNew, $sField);
        $sDatabase = str_replace($sOriginal, $sNew, $sDatabase);
    }
}
file_put_contents($sBibliography, $sDatabase);