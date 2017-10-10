<pre><?php
$sFile = file_get_contents('.reference');
$sContents = file_get_contents($sFile);

$aEntries = array();
preg_match_all('~@(?:article|(?:phd|master)thesis)\{(.+?)\}\s*\}~is', $sContents, $aMatches);

foreach ($aMatches[1] as $sArticle) {
    $sKey = current(explode(',', $sArticle));

    if (preg_match('~pdf[^=]*=[^\{]*\{([^\}]+)~si', $sArticle, $aMatch) > 0) {
        $aEntries[$sKey] = $aMatch[1];
    }
}

preg_match_all('~@comment\{([^\}]+)\}~s', $sContents, $aMatches);
$aGroups = array();
foreach ($aMatches[1] as $sComment) {
    $aParts = explode(';', $sComment);
    $aType = explode(':', array_shift($aParts));
    if (trim($aType[0]) == 'jabref-meta' && trim($aType[1]) == 'groupstree') {
        foreach ($aParts as $sPart) {
            $sPart = str_replace(array('\\', "\r", "\n"), null, trim($sPart));
            if (!empty($sPart)) {
                if (preg_match('~group:(.+)~i', $sPart, $aMatch) > 0) {
                    $sGroup = $aMatch[1];
                    $sSearch = $sGroup;//str_replace(array('Thesis'), array('Theses'), $sGroup);

                    $aGroup = array($sPart . '\\;0\\');
                    foreach ($aEntries as $sKey => $sPdf) {

                        if (strpos($sPdf, $sSearch) !== false) {
                            $aGroup[] = $sKey . '\\';
                        }
                    }
                    $aGroup[] = null;
                    $aGroups[] = implode(';', $aGroup);
                }
            }
        }
    }
}
echo implode(";\n", $aGroups);