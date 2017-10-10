<?php
/* Configuration */
$sAccount = file_get_contents('.account');
$aTemplate = array(
    'article' => "@article{%s,\n%s\n}\n",
    'entry' => '  %s = {%s},',
    'author' => '%s, %s');
    
require_once 'remove-accents.php';

function doi_bibtex($sDoi, $mKey = null, $aDuplicates = null, &$aError = null) {
    global $sAccount, $aTemplate;

    $sUrl = sprintf('http://www.crossref.org/openurl/?id=%s&noredirect=true&pid=%s&format=unixref', urlencode(trim($sDoi)), $sAccount);
    if (!($sContents = file_get_contents($sUrl))) {
        $aError[] = $sDoi;
        return false;
    }
    
    $oXml = new SimpleXMLElement($sContents);
    $oCrossrefXml = $oXml->doi_record->crossref;
    if ($oCrossrefXml === null || isset($oCrossrefXml->error)) {
        $aError[] = $sDoi;
        return false;
    }
    $oJournalXml = $oCrossrefXml->journal;
    $oArticleXml = $oJournalXml->journal_article;

    $aAuthors = array();
    if (isset($oArticleXml->contributors)) {
        foreach ($oArticleXml->contributors->person_name as $oPersonXml) {
            $aAuthors[] = sprintf($aTemplate['author'], $oPersonXml->surname, $oPersonXml->given_name);
        }
    }

    $oIssueXml = $oJournalXml->journal_issue;
    $sAuthors = implode(' and ', $aAuthors);
    $sAuthorsAccentless = remove_accents($sAuthors);
    if (!isset($oIssueXml->publication_date)) {
        $aError[] = $sDoi;
        return false;
    }
    $iYear = intval($oIssueXml->publication_date->year);
    
    if (is_string($mKey)) {
        $sKey = $mKey;
    } else {
        $aFound = array();
        if (isset($mKey[$iYear])) {
            foreach ($mKey[$iYear] as $sAuthor) {
                if (strpos(str_replace(' ', null, $sAuthorsAccentless), $sAuthor) !== false) {
                    $sKey = sprintf('%s%d%s', $sAuthor, $iYear, isset($aDuplicates[$iYear][$sAuthor]) ? $aDuplicates[$iYear][$sAuthor] : null);
                    $aFound[] = $sKey;
                }
            }    
        }
        $sKey = empty($aFound) ? md5($sDoi) : implode(';', $aFound);
    }

    $aEntries = array();
    $aEntries[] = sprintf($aTemplate['entry'], 'author', $sAuthors);
    $aEntries[] = sprintf($aTemplate['entry'], 'title', trim($oArticleXml->titles->title));
    $oMetadataXml = $oJournalXml->journal_metadata;
    $aEntries[] = sprintf($aTemplate['entry'], 'journal', $oMetadataXml->full_title);
    
    $aEntries[] = sprintf($aTemplate['entry'], 'year', $iYear);
    $aEntries[] = sprintf($aTemplate['entry'], 'volume', $oIssueXml->journal_volume->volume);
    $sPages = empty($oArticleXml->pages->last_page)
        ? $oArticleXml->pages->first_page
        : sprintf('%d-%d', $oArticleXml->pages->first_page, $oArticleXml->pages->last_page);
    $aEntries[] = sprintf($aTemplate['entry'], 'pages', $sPages);
    $aEntries[] = sprintf($aTemplate['entry'], 'number', $oIssueXml->issue);
    $aEntries[] = sprintf($aTemplate['entry'], 'month', $oIssueXml->publication_date->month);
    $aEntries[] = sprintf($aTemplate['entry'], 'doi', $sDoi);
    $aEntries[] = sprintf($aTemplate['entry'], 'url', $oArticleXml->doi_data->resource);

    return sprintf($aTemplate['article'], $sKey, implode("\n", $aEntries));
}