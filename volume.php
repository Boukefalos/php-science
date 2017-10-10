<?php
set_time_limit(0);
header('Content-Type: text/html;charset=utf-8');
?>
<pre><?php
/* Configuration */
error_reporting(E_ALL ^ E_DEPRECATED);
$sFile = file_get_contents('.reference');
$sAccount = file_get_contents('.account');
$iCount = 0;

/* Load dependencies */
set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), '../../')));
require_once 'vendor/autoload.php';

$sDatabase = file_get_contents($sFile);
$oBibtex = new Structures_BibTex();
if ($oBibtex->loadFile($sFile) && $oBibtex->parse()) {
    foreach ($oBibtex->data as $aEntry) {
        if ($aEntry['type'] == 'article' && isset($aEntry['doi'])) {
            if (isset($aEntry['volume'])) {
               continue;            
            }
            $sCite = $aEntry['cite'];
            $sDoi = $aEntry['doi'];
            printf("Processing %s...\n", $sCite);
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
            $oXml = new SimpleXMLElement($sContents);            
            $oCrossrefXml = $oXml->doi_record->crossref;
            if ($oCrossrefXml === null || isset($oCrossrefXml->error)) {
                printf("Corrupt reference!\n");
                continue;
            }
            $oJournalXml = $oCrossrefXml->journal;
            $oArticleXml = $oJournalXml->journal_article;            
            $oIssueXml = $oJournalXml->journal_issue;
            if (isset($oIssueXml->journal_volume->volume)) {
                $iVolume = trim($oIssueXml->journal_volume->volume);
                $iYear = intval($oIssueXml->publication_date->year);
                $oMetadataXml = $oJournalXml->journal_metadata;
                $sJournal = $oMetadataXml->full_title;
                if ($sJournal == 'Eur. J. Org. Chem.' || $sJournal == 'Eur. J. Inorg. Chem.') {        
                    printf("Notorious journal (%s), skipping!\n", $sJournal);
                    continue;
                }
                if ($iVolume == $iYear) {                    
                    printf("Volume and year match, skipping!\n", $iVolume);
                    continue;
                }
                if (strlen($iVolume) > 0) {
                    printf("Set to: %s\n", $iVolume);
                    $sNew = str_replace('doi =', sprintf("volume = {%s},\n doi =", $iVolume), $sOriginal);
                    $sDatabase = str_replace($sOriginal, $sNew, $sDatabase);
                     if (++$iCount > 250) {
                        break;
                    }
                }
            }
        }
    }
}
file_put_contents($sFile, $sDatabase);