<pre><?php
set_time_limit(0);

/* Configuration */
$sFiles = 'C:\Users\Rik\Dropbox\Reference';
$sFile = file_get_contents('.reference');
$sDatabase = file_get_contents($sFile);

// define('TEXT_NOLINK', true);

$bLink = !defined('TEXT_NOLINK');
$bDois = defined('TEXT_NOLINK');
$bText = defined('TEXT_NOLINK');
$bLoose = true;
$aIgnore = array('Todo', 'Theses', 'Authors', 'Patent');
//$aIgnore = array();

$aMissing = array(
    'file' => array( # entries without citation
        'Schmidbaur2002a',
        'Anderson2011',
        'Cadierno2012',
        'Selander2017a'
    ),
    'keys' => array( # entries without file
        'Finkelstein1910',
        'Williamson1851',
        'Ullmann1901',
        'Ullmann1904',        
        'Saito1973',
        'Lee1991',
        'Hayashi2012',
        'Rix1956',
        'Sheldrick2008',        
        'Smykalla1991',
        'Kuzmina1994',
        'Tanisaki1973',
        'Hayton2010',
        'Cadierno2013',
        'Gutmann1975',
        'Schwerdtfeger1993'
    ),
    'type' => array(
        'online',
        'book',
        'inbook',
        'incollection',
        'phdthesis'));

/* Load dependencies */
set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), '../../')));
require_once 'vendor/autoload.php';
require_once 'explore.php';
require_once 'pdf2text.php';

use ZendPdf\PdfDocument;
use ZendPdf\Exception\CorruptedPdfException;
use ZendPdf\Exception\NotImplementedException;

preg_match_all('~@([^{]+){([^,]+),~i', $sDatabase, $aMatches);

$aEntries = array_combine($aMatches[2], $aMatches[1]);
$aDois = array();

echo "<strong>No entry found for:</strong>\n";
explore($sFiles, '\*.pdf', $aFound);

$aKeys = [];
foreach ($aFound as $sFile) {
    $sBase = basename($sFile, '.pdf');
    if (ctype_upper(substr($sBase, 0, 1)) && substr($sBase, -2, 1) !== '.' && strpos($sBase, ' ') === false && in_array($sBase, $aMissing['file']) === false) {
        $bIgnore = false;
        foreach ($aIgnore as $sIgnore) {
            if (strpos($sFile, sprintf('%s\\', $sIgnore)) !== false) {
                $bIgnore = true;
                break;
            }
        }
        if (!$bIgnore) {
            $aKeys[] = $sBase;
        }

        $bEntry = isset($aEntries[$sBase]);
        if ($bIgnore || $bEntry) {
            unset($aEntries[$sBase]);
        } else {
            if ($bDois) {
                $sDoi = null;
                try {
                    $oPdf = new PdfDocument($sFile, 0, true);
                    if (isset($oPdf->properties['WPS-ARTICLEDOI'])) {
                        $sDoi = $oPdf->properties['WPS-ARTICLEDOI'];
                    }
                } catch (CorruptedPdfException $e) {
                } catch (NotImplementedException $e) {}

                if (isset($sDoi)) {
                    $aDois[] = $sDoi;
                } elseif ($bText) {
                    $sText = pdf2text($sFile);
                    if (preg_match('~(dx\.doi\.org/|doi:\s?)(\d+\.\d+/(:?\w+\.)?\w+)~i', $sText, $aMatch)) {
                        $aDois[] = $aMatch[2];
                    } else if (preg_match('~\s(\d+\.\d+/(:?\w+\.)?\w+)CCC~i', $sText, $aMatch)) {
                        $aDois[] = $aMatch[1];
                    } else if ($bLoose && preg_match('~(\d{2}\.\d+/\s*(:?\w+\.)?\w+)~i', $sText, $aMatch)) {
                        $aDois[] = str_replace(' ', null, $aMatch[0]);
                    }
                }
            }
            if ($bLink) {
                printf("%-20s (<a href=\"file://%s\" target=\"_blank\">%s</a>)\n", $sBase, $sFile, str_replace(array($sFiles, basename($sFile)), null, $sFile));
            } else {
                printf("%s\n", $sBase);
            }
        }
    }
}

foreach ($aEntries as $sBase => $sType) {
    if (in_array(strtolower($aEntries[$sBase]), $aMissing['type']) || strpos($sBase, 'ange_') === 0) {
        unset($aEntries[$sBase]);
    }
}

echo "\n<strong>DOIs:</strong>\n";
echo count($aDois) > 0 ? implode("\n", $aDois) . "\n" : null;

echo "\n<strong>File missing for:</strong>\n";
echo implode("\n", array_diff(array_keys($aEntries), $aMissing['keys'])). "\n";

// print_r(array_unique($aKeys));
// print_r(($aKeys));
echo "\n<strong>Duplicate files:</strong>\n";
echo implode("\n", array_diff_assoc($aKeys, array_unique($aKeys)));