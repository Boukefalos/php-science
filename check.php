<?php
set_time_limit(0);
error_reporting(E_ALL ^ E_DEPRECATED);

/* Load dependencies */
chdir(dirname(__FILE__));
set_include_path(implode(PATH_SEPARATOR, array(get_include_path(), '../../', '../../Pear/')));
require_once 'vendor/autoload.php';

/* Configuration */
$sBibtexFile = 'C:\Users\Rik\Git\latex\Report\phd-thesis\Bibliography\reference.bib';
$sEntriesFile = 'C:\Users\Rik\Dropbox\Reference\check.txt';
$sFailFile = 'C:\Users\Rik\Dropbox\Reference\fail.txt';
$sTmpDir = 'C:\tmp';

/* Latex template */
$sLatex = '\documentclass{article}
\usepackage[utf8]{inputenc}
\usepackage[backend=bibtex8,style=chem-angew]{biblatex}
\addbibresource{reference.bib}
\begin{document}
\cite{%s}
\printbibliography
\end{document}';

/* Parse bibtex file */
$bReset = isset($_GET['reset']) || (isset($argc) && $argc == 2 && $argv[1] == 'reset');
if ($bReset || !file_exists($sEntriesFile)) {
    $aEntries = array();
    $oBibtex = new Structures_BibTex();
    if ($oBibtex->loadFile($sBibtexFile) && $oBibtex->parse()) {
        foreach ($oBibtex->data as $aEntry) {
            if ($aEntry['type'] != 'comment') {
                $aEntries[] = $aEntry['cite'];
            }
        }
    }
} else {
    $aEntries = explode("\n", file_get_contents($sEntriesFile));
}

/* Prepare temporary directory */
@mkdir($sTmpDir);
chdir($sTmpDir);
copy($sBibtexFile, 'reference.bib');

/* Loop over entries */
@unlink($sFailFile);
foreach ($aEntries as $iKey => $sEntry) {
    if (!empty($sEntry)) {
        printf('Checking "%s"... ', $sEntry);
        command('rm check*.*');
        file_put_contents('check.tex', sprintf($sLatex, $sEntry));
        command('latex -interaction=nonstopmode check');
        command('bibtex check');
        command('latex -interaction=nonstopmode check');
        $sLog = file_get_contents('check.log');
        if (strpos($sLog, 'inputenc Error') === false) {        
            printf("success!\n");
            unset($aEntries[$iKey]);
        } else {
            printf("fail!\n");
            file_put_contents($sFailFile, sprintf("%s\n", $sEntry), FILE_APPEND);
        }
    }
}

/* Clean up */
command('rm check*.*');
unlink('reference.bib');

/* Keep track of failed files */
file_put_contents($sEntriesFile, implode("\n", $aEntries));

/* Helper functions */
function command($sCommand) {
    ob_start();
    exec(sprintf('%s 2>&1', $sCommand));
    return ob_get_clean();
}