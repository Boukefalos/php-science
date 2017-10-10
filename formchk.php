<?php
$sWebMO = 'C:\Program Files\WebMO\user';
$sTarget = 'D:\Computations\WebMO';
$aJobs = glob(sprintf('%s\*', $sWebMO));
foreach ($aJobs as $sJob) {
    $iJob = basename($sJob);
    $sChk = sprintf('%s\output.chk', $sJob);
    $sFch = sprintf('%s\output.fch', $sJob);
    if (file_exists($sChk)) {
        if (!file_exists($sFch)) {
            system(printf('formchk "%s"', $sChk));
        }
        copy($sFch, sprintf('%s\%d.fch', $sTarget, $iJob));
    }
}