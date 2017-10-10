<pre><?php
// $sData = file_get_contents('C:\Users\Rik\Downloads\data.txt');
// preg_match_all('~"([^"]+)"\n\n([^\n]+)\n~', $sData, $aMatches);
// $aData = array_combine($aMatches[1], $aMatches[2]);
// var_export($aData);

$sDir = 'C:\Users\Rik\Seafile\PhD\Project\Organogolds\Crystals\Danila\asdg19';

$aParts = array('ligand', 'substrate', 'complex');
$aDeleteGold = array(true, false);

foreach ($aDeleteGold as $bDeleteGold) {
    foreach ($aParts as $sPart) {
        $sFile = sprintf('%s\%s.xyz', $sDir, $sPart);
        $sResponse = execute($sFile, $bDeleteGold);
        preg_match_all('~<strong>([^\<]+)</strong>~', $sResponse, $aMatches);
        $fValue = isset($aMatches[1][1]) ? $aMatches[1][1] : 0;
        printf("%s (%s) = %.1f\n", $sPart, $bDeleteGold ? 'true' : 'false', $fValue);
    }
}

function execute($sFile, $bDeleteGold = true) {
    $aData = array (
      'action' => 'upload',
      'xyzinput' => 'upload',
      'radii' => 'bondi2',
      'atom_center' => '1',
      'atom_axis' => '2',
      'atom_plane' => '2',
      'propC2' => '1.80',
      'propC3' => '2.00',
      'propN2' => '1.65',
      'propN3' => '1.87',
      'propP' => '1.90',
      'propH' => '1.20',
      'atom_coord' => '0',
      'atom_indexes' => '0 1',
      'which_axis' => '6',
      'plane_indexes' => '0 2',
      'atom_todelete' => $bDeleteGold ? 0 : null,
      'atoms_radius' => '1',
      'radius' => '3.5',
      'distance' => '0.0',
      'i_step' => '0.10',
      'submit_button' => 'submit',
    );
    $sUrl = 'https://www.molnac.unisa.it/OMtools/sambvca2.0/process/sambvca_result.php';
    $aData['input_file'] = trim(file_get_contents($sFile));
    $aData['input_file_handle'] = new CURLFile(realpath($sFile));
    $rCurl = curl_init();
    curl_setopt($rCurl, CURLOPT_URL, $sUrl);
    curl_setopt($rCurl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($rCurl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($rCurl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($rCurl, CURLOPT_POST, true);
    curl_setopt($rCurl, CURLOPT_POSTFIELDS, $aData);
    $sResponse = curl_exec($rCurl);
    curl_close($rCurl);
    // file_put_contents('result.html', $sResponse);
    // $sResponse = file_get_contents('result.html');
    $sResponse = str_replace(
        array('<head>'),
        array('<head><base href="https://www.molnac.unisa.it/OMtools/sambvca2.0/process/" rel="canonical" />'),
        $sResponse);
    // $sResponse = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $sResponse);
    return $sResponse;
}