<?php
function filtrecsv($chn, $col) {
	$chn = str_replace(chr(226).chr(128).chr(153), '\'', $chn); //--- remplacer ’ par '
	$chn = $col == 'texte' ? propre($chn) : typo($chn);
	$chn = str_replace('</p>', '', str_replace('<p>', '', $chn));
	$chn = str_replace("\n", '<br />', str_replace("\r\n", '<br />', $chn));
	$chn = html_entity_decode(textebrut($chn));
	$chn = str_replace("\n\n", "\n", $chn);
	$chn = utf8_decode($chn);
	return $chn;
}

function maputcsv($fields = array(), $delimiter = ',', $enclosure = '"') {
	$str1 = '';
	foreach ($fields as $value) {
		$str2 = $enclosure;
		$escaped = 0;
		$len = strlen($value);
		for ($i = 0; $i < $len; $i++) {
			if ($value[$i] == '\\')
				$escaped = 1;
			else if (! $escaped && $value[$i] == $enclosure)
				$str2 .= $enclosure;
			else
				$escaped = 0;
			$str2 .= $value[$i];
		}
		$str2 .= $enclosure;
		$str1 .= $str2.$delimiter;
	}
	echo substr($str1, 0, -1),"\r\n";
}

function colonnes_motcle($tit, $col, $id_mot, &$trow, &$tlab, &$t_prem, &$t_dern) {
	$t_prem[] = count($trow);
	$res = sql_select('id_mot,titre', 'spip_mots', 'id_groupe='.$id_mot, null, 'titre+1');
	while ($tab = sql_fetch($res)) {
		$k = supprimer_numero($tab['titre']);
		$trow[] = $k; //--- $tab['id_mot'];
		$tlab[] = $tit.'_'.preg_replace('/[^a-z_0-9]/', '', strtr(strtolower(utf8_decode($k)), 'àâçèéêëîïôùûü \'', 'aaceeeeiiouuu__'));
	}
	$t_dern[] = count($trow);
}

function exec_listexport() {
	if (!defined('_ECRIRE_INC_VERSION')) return;

	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
	header('Content-Disposition: attachment; filename="destinataires_'.date('md').'_'.mt_rand(1000,9999).'.csv";');
	header('Content-Type: text/plain; charset=ISO-8859-1;'); //--- UTF-8
	header('Content-Transfer-Encoding: binary');
	header("Expires: 0");
	header('Pragma: no-cache');

	$lilang = 'fr';
	include_spip('plugins/listeselect/exec/listewhere');
	$sql = fonclistewhere($lilang);

//echo '<html><head><title>csv</title><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" /></head><body><pre>',"\n";

	$exportdlim = _request('exportdlim'); $exportdlim = $exportdlim == 'p' ? ';' : ',';

	//--- pouvoir utiliser la class ChampExtra --- ,'idper','archive_per','prenom','codecourtoisie','fonction','typepart','nom_court_institution','nom_long_institution','description_institution','date_naissance','adresse','codepostal','localite','tel1','tel2','gsm1','gsm2','fax1','fax2','email2','date_creation','date_maj','statut_form_cemea','statut_anim_cemea','statut_ep','statut_sj','personne_reference','centre_reference','envoi_diffusion','date_debut_diffusion','date_fin_diffusion','adherent'
	include_spip('inc/cextras');
	$tab = unserialize($GLOBALS['meta']['iextras']);

	static $trow = array(0=>'id_auteur','nom','bio','email','login','statut','maj');
	static $tlab = array(0=>'id_auteur','nom','bio','email','login','statut','maj');
	reset($tab);
	while (list(,$obj) = each($tab))
		if ($obj->table == 'auteur')
			if ($obj->champ != 'diffusion' && $obj->champ != 'ndiffusion') {
				$trow[] = $obj->champ;
				$tlab[] = $obj->champ;
			}
	static $t_prem = array();
	static $t_dern = array();

	//--- ajouter des colonnes pour mots-clés (kw 2)
	colonnes_motcle('dif', 'diffusion', 2, $trow, $tlab, $t_prem, $t_dern);

	maputcsv($tlab, $exportdlim);

	$res = sql_select('*', 'spip_auteurs', $sql, null, 'date_maj');
	while ($tab = sql_fetch($res)) {
		$tndiffusion = explode(',', $tab['ndiffusion']);

		$data = array(); reset($trow);
		while (list($num, $col) = each($trow)) {
			if ($col == 'statut')
				switch ($tab[$col]) {
					case '6forum': $tab[$col] = 'visit'; break;
					case '1comite': $tab[$col] = 'redac'; break;
					case '0minirezo': $tab[$col] = 'admin'; break;
				}
			if ($col == 'maj' || $col == 'date' || $col == 'date_modif')
				$tab[$col] = substr($tab[$col], 0, 10);
			if (substr($col, 0, 5) == 'date_' && $tab[$col] == '0000-00-00')
				$tab[$col] = '';

			if ($num >= $t_prem[0] && $num < $t_dern[0]) {
				$ta2 = explode(',', $tab['diffusion']);
				for ($g = $t_prem[0]; $g < $t_dern[0]; $g++)
					if (in_array($trow[$g], $ta2)) {
						$k = obtenirpaire($tndiffusion, $trow[$g]);
						$data[$g] = $k ? $k : '1';
					}
					else
						$data[$g] = '0';
			}
			else
				$data[$num] = filtrecsv($tab[$col], $col);
		}
		maputcsv($data, $exportdlim);
	}
//echo '</pre></body></html>',"\n";
}
?>
