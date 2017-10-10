<?php
set_time_limit(0);
header('Content-Type: text/html;charset=utf-8');
?>
<pre><?php
/* Configuration */
error_reporting(E_ALL ^ E_DEPRECATED);
$sFile = file_get_contents('.reference');
$sAccount = file_get_contents('.account');
$bUpdateDoi = true;

/* Load dependencies */
set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), '../../')));
require_once 'vendor/autoload.php';

$sDatabase = file_get_contents($sFile);
$oBibtex = new Structures_BibTex();
if ($oBibtex->loadFile($sFile) && $oBibtex->parse()) {
    foreach ($oBibtex->data as $aEntry) {
        $bPages = isset($aEntry['pages']);
        if ($aEntry['type'] == 'article' && isset($aEntry['doi'])) {
            $bDoi = $bPages && strpos($aEntry['pages'], 'doi') !== false;
            if (!$bUpdateDoi || ($bPages && $aEntry['pages'] != '0-0' && $aEntry['pages'] != '0' && (is_numeric($aEntry['pages']) ? $aEntry['pages'] < 1000000 : true) && !$bDoi)) {
                continue;            
            }
            $sCite = $aEntry['cite'];
            $sDoi = $aEntry['doi'];
            printf("Processing %s (%s)...\n", $sCite, $bPages ? $aEntry['pages'] : null);

            preg_match(sprintf('~@article\{%s,.*?\}[\s]*\}~si', $sCite), $sDatabase, $aMatch);
            if (!isset($aMatch[0])) {
                printf("Entry not found!\n");
                continue;
            }
            $sOriginal = $aMatch[0];
            $sUrl = sprintf('http://www.crossref.org/openurl/?id=%s&noredirect=true&pid=%s&format=unixref', urlencode(trim($sDoi)), $sAccount);
            if (!($sContents = file_get_contents($sUrl))) {
                printf("Failed to fetch reference!\n");
                continue;
            }
            if (strpos($sContents, 'Malformed DOI') !== false) {
                echo "error!\n";
                continue;
            }
            $oXml = new SimpleXMLElement($sContents);            
            $oCrossrefXml = $oXml->doi_record->crossref;
            $oJournalXml = $oCrossrefXml->journal;
            $oArticleXml = $oJournalXml->journal_article;
            if ($oCrossrefXml === null || isset($oCrossrefXml->error)) {
                printf("Corrupt reference!\n");
                continue;
            }
            $oArticleXml = $oJournalXml->journal_article;
            $sPages = ($bSingle = empty($oArticleXml->pages->last_page))
                ? $oArticleXml->pages->first_page
                : sprintf('%d-%d', $oArticleXml->pages->first_page, $oArticleXml->pages->last_page);
            if ($sPages == '0-0' || $sPages == '0' || empty($sPages) || ($bSingle && $sPages > 100000)) {
                if ($bDoi) {
                    continue;
                }
                $sPages = sprintf('doi: %s', $sDoi);
            }
            printf("Set to: %s\n", $sPages);
            if (isset($aEntry['pages'])) {
                $sNew = str_replace(
                    $aEntry['pages'],
                    $sPages,
                    $sOriginal);
            } else {
                $sNew = str_replace(
                    sprintf('%s,', $sCite),
                    sprintf("%s,\npages = {%s},", $sCite, $sPages),
                    $sOriginal);
            }
            $sDatabase = str_replace($sOriginal, $sNew, $sDatabase);
        }
    }
}
file_put_contents($sFile, $sDatabase);