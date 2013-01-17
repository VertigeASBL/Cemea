<?php
function fonclistewhere(&$lilang) {
	if (($tab = _request('email')) && count($tab) < 2) {
		if ($tab[0] == 'y')
			$sql .= ' AND email<>\'\'';
		if ($tab[0] == 'n')
			$sql .= ' AND email=\'\'';
	}

	$tab = _request('statut');
	if ($tab && count($tab) < 2 && $tab[0] == 'y')
		$sql = 'statut=\'6forum\'';
	else
		$sql = 'statut<=\'6z\' AND statut!=\'5poubelle\'';

/*	$lilang = 'fr';
	if (($tab = _request('pgp')) && count($tab) < 2) { //--- pgp = langue
		$lilang = $tab[0];
		if ($lilang != 'fr')
			$sql .= ' AND pgp LIKE \'%'.$lilang.'%\'';
	} */

	if (($tab = _request('typepart')) && count($tab) < 2)
		$sql .= ' AND typepart=\''.$tab[0].'\'';

	if (($tab = _request('archive_per')) && count($tab) < 2)
		$sql .= ' AND archive_per=\''.$tab[0].'\'';

	if (($tab = _request('adherent')) && count($tab) < 2)
		$sql .= ' AND adherent=\''.$tab[0].'\'';

	if (($tab = _request('envoi_diffusion')) && $tab[0] == 'Y')
		$sql .= ' AND envoi_diffusion=\'Y\' AND date_debut_diffusion<=CURDATE() AND date_fin_diffusion>=CURDATE()';

	
	if ($tab = _request('statut_form_cemea')) {
		$sql .= ' AND statut_form_cemea=\''.$tab.'\'';
	}
	
	if ($tab = _request('statut_anim_cemea')) {
		$sql .= ' AND statut_anim_cemea=\''.$tab.'\'';
	}

	if ($tab = _request('statut_ep')) {
		$sql .= ' AND statut_ep=\''.$tab.'\'';
	}

	if ($tab = _request('statut_sj')) {
		$sql .= ' AND statut_sj=\''.$tab.'\'';
	}
	
	if ($tab = _request('membre')){
		$sql .= ' AND membre=\'oui\'';
	}

	if (($tab = _request('diffusion')) && count($tab) < _request('n_diffusion')) {
		$sql .= ' AND (';
		reset($tab); $g = false;
		while (list(, $k) = each($tab)) {
			if ($g) $sql .= ' OR '; $g = true;
			$sql .= 'diffusion REGEXP \'(^|,)'.$k.'(,|$)\'';
		}
		$sql .= ')';
	}
//echo '<hr />',$sql,'<hr />';
	return $sql;
}
?>
