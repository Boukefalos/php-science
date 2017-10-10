<pre><?php
$sData = <<<NMR
0015   616.623    2.4652     0.0250      2264538
0016   614.244    2.4557     0.0502      4537255
0017   611.862    2.4462     0.0265      2399964
NMR;
/*
2.54 (q, J=1.2 Hz, H, )
2.52 (q, J=1.3 Hz, H, )
*/
$aData = explode("\n", trim($sData));

process($aData, $aFreqs, $aShifts);
switch (count($aData)) {
	case 2:
		echo doublet($aFreqs, $aShifts, false);
		break;
	case 3:
		echo triplet($aFreqs, $aShifts, false);
		break;
	case 4:
		echo quartet($aFreqs, $aShifts, false);
		echo doubletdoublet($aFreqs, $aShifts, false);
		break;
	case 6:
		//echo doubletriplet($aFreqs, $aShifts, false);
		echo tripletdoublet($aFreqs, $aShifts, false);
		break;	
}

function process($aData, &$aFreqs, &$aShifts) {
	foreach ($aData as $sRow) {
		$aRow = explode('   ', $sRow);
		$aFreqs[] = next($aRow);
		$aShifts[] = next($aRow);
	}
}

function average($aData) {
	return array_sum($aData) / count($aData);
}

function doublet($aFreqs, $aShifts, $bValues = true) {
	$aValues = array(
		($aShifts[0] + $aShifts[1]) / 2,
		$aFreqs[0] - $aFreqs[1]);
	return $bValues
		? $aValues
		: vsprintf('%.2f (d, J=%.1f Hz, H, )', $aValues);
}

function triplet($aFreqs, $aShifts, $bValues = true) {
	$aValues = array(
		$aShifts[1],
		average(array(
			$aFreqs[0] - $aFreqs[1],
			$aFreqs[1] - $aFreqs[2],
			($aFreqs[0] - $aFreqs[2]) / 2)));
	return $bValues
		? $aValues
		: vsprintf('%.2f (t, J=%.1f Hz, H, )', $aValues);
}

function quartet($aFreqs, $aShifts, $bValues = true) {
	$aValues = array(
		average($aShifts),
		average(array(
			$aFreqs[0] - $aFreqs[1],
			$aFreqs[1] - $aFreqs[2],
			$aFreqs[2] - $aFreqs[3])));
	return $bValues
		? $aValues
		: vsprintf('%.2f (q, J=%.1f Hz, H, )', $aValues);
}

function doubletdoublet($aFreqs, $aShifts, $bValues = true) {
	$aFirstDoublet = doublet(
		array_slice($aFreqs, 0, 2),
		array_slice($aShifts, 0, 2));
	$aSecondDoublet = doublet(
		array_slice($aFreqs, 2, 2),
		array_slice($aShifts, 2, 2));
	$aDoublet = doublet(
		array(($aFreqs[0] + $aFreqs[1]) / 2, ($aFreqs[2] + $aFreqs[3]) / 2),
		array($aFirstDoublet[0], $aSecondDoublet[0]));
	$aValues = array(
		$aDoublet[0],
		$aDoublet[1],
		($aFirstDoublet[1] + $aSecondDoublet[1]) / 2);
	return $bValues
		? $aValues
		: vsprintf("%.2f (dd, J=%.1f Hz, %.1f Hz, H, )", $aValues);
}

function doubletriplet($aFreqs, $aShifts, $bValues = true) {
	$aFirstTriplet = triplet(
		array_slice($aFreqs, 0, 3),
		array_slice($aShifts, 0, 3));
	$aSecondTriplet = triplet(
		array_slice($aFreqs, 3, 3),
		array_slice($aShifts, 3, 3));
	$aDoublet = doublet(
		array($aFreqs[1], $aFreqs[4]),
		array($aFirstTriplet[0], $aSecondTriplet[0]));
	$aValues = array(
		$aDoublet[0],
		$aDoublet[1],
		($aFirstTriplet[1] + $aSecondTriplet[1]) / 2);
	return $bValues
		? $aValues
		: vsprintf('%.2f (dt, J=%.1f Hz, J=%.1f Hz, H, )', $aValues);
}

function tripletdoublet($aFreqs, $aShifts, $bValues = true) {
	$aFirstDoublet = doublet(
		array_slice($aFreqs, 0, 2),
		array_slice($aShifts, 0, 2));
	$aSecondDoublet = doublet(
		array_slice($aFreqs, 2, 2),
		array_slice($aShifts, 2, 2));
	$aThirdDoublet = doublet(
		array_slice($aFreqs, 4, 2),
		array_slice($aShifts, 4, 2));
	$aFirstTriplet = triplet(
		array($aFreqs[0], $aFreqs[2], $aFreqs[4]),
		array($aShifts[0], $aShifts[2], $aShifts[4]));
	$aSecondTriplet = triplet(
		array($aFreqs[1], $aFreqs[3], $aFreqs[5]),
		array($aShifts[1], $aShifts[3], $aShifts[5]));
	$aValues = array(
		($aFirstTriplet[0] + $aSecondTriplet[0]) / 2,
		($aFirstTriplet[1] + $aSecondTriplet[1]) / 2,
		average(array(
			$aFirstDoublet[1],
			$aSecondDoublet[1],
			$aThirdDoublet[1])));
	return $bValues
		? $aValues
		: vsprintf('%.2f (td, J=%.1f Hz, J=%.1f Hz, H, )', $aValues);
}