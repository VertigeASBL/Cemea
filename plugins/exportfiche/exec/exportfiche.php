<?php
/* --- richir vertige ---

function majuscule($chn) {
	$chn = strtr(strtoupper($chn), utf8_decode('àáâãäåæçèéêëìíîïðñòóôõöøùúûüý'), utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ'));
	return $chn;
} */
function filtrecsv($chn, $col) {
	$chn = str_replace("\n", '', $chn);
	$chn = str_replace(chr(226).chr(128).chr(153), '\'', $chn); //--- remplacer ’ par '
	$chn = str_replace(chr(0xE2).chr(0x82).chr(0xAC), 'EUR', $chn); //--- remplacer eur
	$chn = $col=='texte' || $col=='chapo' || $col=='descriptif' || $col=='description_institution' ? propre($chn) : typo($chn);
	$chn = str_replace("\n", '', $chn);
	$chn = str_replace('&mdash;', '-', $chn);
	$chn = html_entity_decode(textebrut($chn));
	$chn = str_replace("\n\n", "\n", $chn);
	$chn = utf8_decode($chn);
	return $chn;
}

function maputcsv($fields = array(), $delimiter = ',', $enclosure = '"') {
	$str1 = '';
	foreach ($fields as $col) {
		$str2 = $enclosure;
		$escaped = 0;
		$len = strlen($col);
		for ($i = 0; $i < $len; $i++) {
			if ($col[$i] == '\\')
				$escaped = 1;
			else if (! $escaped && $col[$i] == $enclosure)
				$str2 .= $enclosure;
			else
				$escaped = 0;
			$str2 .= $col[$i];
		}
		$str2 .= $enclosure;
		$str1 .= $str2.$delimiter;
	}
	echo substr($str1, 0, -1),"\r\n";
}

function colonnes_motcle($tit, $id_mot, &$trow, &$tlab, &$t_prem, &$t_dern) {
	$t_prem[] = count($trow);
	$res = sql_select('id_mot,titre', 'spip_mots', 'id_groupe='.$id_mot, null, 'titre+1');
	while ($tab = sql_fetch($res)) {
		$k = supprimer_numero($tab['titre']);
		$trow[] = $k; //--- $tab['id_mot'];
		$tlab[] = $tit.'_'.preg_replace('/[^a-z_0-9]/', '', strtr(strtolower(utf8_decode($k)), 'àâçèéêëîïôùûü \'', 'aaceeeeiiouuu__'));
	}
	$t_dern[] = count($trow);
}

// execute automatiquement par le plugin au chargement de la page ?exec=exportfiche
function exec_exportfiche() {
	$exportdlim = _request('exportdlim');
	$typfich = (int) _request('typfich');
	$date_cond = _request('date_cond');

	include_spip('inc/date_gestion');
	$k = array();
	if (@verifier_corriger_date_saisie('cond', false, $k)) //--- preg_match('|^\d{2}/\d{2}/\d{4}$|', $date_cond)
		$date_cond = substr($date_cond, 6, 4).'-'.substr($date_cond, 3, 2).'-'.substr($date_cond, 0, 2);
	else
		$date_cond = date('Y-m-d');

	if (_request('okconfirm')) {	/* **** Ecrire CSV ***** */
		$exportdlim = $exportdlim == 'p' ? ';' : ',';

		//--- pouvoir utiliser la class ChampExtra
		include_spip('inc/cextras');
		$tab = unserialize($GLOBALS['meta']['iextras']);

		static $trow = array();
		static $tlab = array();
		static $t_prem = array();
		static $t_dern = array();

		switch ($typfich) {
		case 1: //--- auteurs personne ---
			$trow = array(0=>'id_auteur','nom','bio','email','login','statut','maj');
			$tlab = array(0=>'id_auteur','nom','bio','email','login','statut','maj');
			reset($tab);
			while (list(,$obj) = each($tab))
				if ($obj->table == 'auteur') {
					$trow[] = $obj->champ;
					$tlab[] = $obj->champ;
				}
			$k = 'personnes';
			$r_from = 'spip_auteurs';
			$r_cond = 'statut<>\'5poubelle\' AND date_maj>=\''.$date_cond.'\'';
			$r_ordr = 'id_auteur';
			break;
		case 2: //--- auteurs_articles suivi ---
		  $trow = array(0=>'S.id_article','A.idact','A.titre', 'date_debut', 'dates_ra');
		  $tlab = array(0=>'id_article','idact','action', 'dates_ra');
			reset($tab);
			while (list(,$obj) = each($tab))
				if ($obj->table == 'auteurs_article')
					if ($obj->champ != 'inscrit') {
						$trow[] = 'S.'.$obj->champ;
						$tlab[] = $obj->champ;
					}
			$trow = array_merge($trow, array('S.id_auteur','P.nom','P.bio','P.email','P.login','P.statut','P.maj'));
			$tlab = array_merge($tlab, array('id_auteur','nom','bio','email','login','statut','maj'));
			reset($tab);
			while (list(,$obj) = each($tab))
				if ($obj->table == 'auteur') {
					$trow[] = 'P.'.$obj->champ;
					$tlab[] = $obj->champ;
				}
			$k = 'inscriptions';
			$r_from = 'spip_auteurs_articles AS S,spip_auteurs AS P,spip_articles AS A';
			$r_cond = 'S.inscrit<>\'\' AND P.id_auteur=S.id_auteur AND A.id_article=S.id_article AND P.statut<>\'5poubelle\' AND date_suivi>=\''.$date_cond.'\'';
			$r_ordr = 'S.id_article,date_suivi,heure_suivi';
			break;
		case 3: //--- articles action ---
			$trow = array(0=>'id_article','titre','chapo','texte','descriptif','date','maj','date_modif','nom_site','url_site');
			$tlab = array(0=>'id_article','titre','chapeau','texte','tarif','date','maj','date_modif','nom_site','url_site');
			$k = 'actions';
			$r_from = 'spip_articles AS A,spip_mots_rubriques M';
			$r_cond = 'statut=\'publie\' AND M.id_rubrique=A.id_rubrique AND M.id_mot=5 AND date_maj>=\''.$date_cond.'\'';
			$r_ordr = 'id_article';
			reset($tab);
			while (list(,$obj) = each($tab))
				if ($obj->table == 'article') {
					$trow[] = 'A.'.$obj->champ;
					$tlab[] = $obj->champ;
				}
			break;
		}
		//--- requete sql
		$res = sql_select(implode(',', $trow), $r_from, $r_cond, null, $r_ordr);

		//--- enlever les alias (1! lettre par exemple "R.")
		reset($trow);
		while (list($num, $col) = each($trow))
			if ($col{1} == '.')
				$trow[$num] = substr($col, 2);

		//--- ajouter des colonnes pour mots-clés (diffusion kw 2)
		if ($typfich == 1 || $typfich == 2) {
			colonnes_motcle('dif', 2, $trow, $tlab, $t_prem, $t_dern);

/*			//--- enlever les champs qui seront décomposés
			$col = array_search('diffusion', $trow);  if ($col !== false) unset($trow[$col], $tlab[$col]);
			$col = array_search('ndiffusion', $trow); if ($col !== false) unset($trow[$col], $tlab[$col]); */
		}

		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
		header('Content-Disposition: attachment; filename="'.$k.'_'.date('md').'_'.mt_rand(1000,9999).'.csv";');
		header('Content-Type: text/plain; charset=ISO-8859-1;'); //--- UTF-8
		header('Content-Transfer-Encoding: binary');
		header("Expires: 0");
		header('Pragma: no-cache');

//echo '<pre>';
		maputcsv($tlab, $exportdlim);

		function retirer_element($tableau, $rem_key, $to_remove) {
		  $acc = array();
		  foreach($tableau as $key => $value) {
		    if (($rem_key ? $key : $value) != $to_remove) {
		      $acc[$key] = $value;
		    }
		  }
		  return $acc;
		}

		function retirer_element_par_cle($tableau, $key_to_remove) {
		  return retirer_element($tableau, true, $key_to_remove);
		}

		function retirer_element_par_valeur($tableau, $value_to_remove) {
			return retirer_element($tableau, false, $value_to_remove);
		}

		$trow = retirer_element_par_valeur($trow, 'date_debut');

		while ($tab = sql_fetch($res)) {

		  if ($typfich == 2) {
		    if ( (! $tab['dates_ra']) || $tab['dates_ra'] == '' ) {
					$tab['dates_ra'] = $tab['date_debut'];
		    }
				$tab = retirer_element_par_cle($tab, 'date_debut');
		  }

			if (isset($tab['ndiffusion']))
				$tndiffusion = explode(',', $tab['ndiffusion']);
			else
				$tndiffusion = false;

			$data = array(); reset($trow);
			while (list($num, $col) = each($trow)) {
				if ($col == 'titre')
					$tab[$col] = supprimer_numero($tab[$col]);
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
					if ($num >= $t_prem[0]) {
						$ta2 = explode(',', $tab['diffusion']);
						for ($g = $t_prem[0]; $g < $t_dern[0]; $g++)
							if (in_array($trow[$g], $ta2)) {
								$k = $tndiffusion ? obtenirpaire($tndiffusion, $trow[$g]) : false;
								$data[$g] = $k ? $k : '1';
							}
							else
								$data[$g] = '0';
					}
				}
				else
					$data[$num] = filtrecsv($tab[$col], $col);
			}
			maputcsv($data, $exportdlim);
		}
		exit;
//echo '</pre>';
		$exportdlim = $exportdlim == ';' ? 'p' : 'v';
	}
	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page('Exporter des fiches', '', '');

	echo '<br />',gros_titre('Exporter des fiches');

	echo debut_gauche('', true);
	echo debut_boite_info(true);
	echo 'Les donn&eacute;es des fiches peuvent &ecirc;tre obtenues dans un fichier au format CSV.<br /><br />Vous pouvez enregistrer ce fichier sur votre disque et/ou l\'ouvrir dans votre application tableur (Excel par exemple).',"\n";
	echo fin_boite_info(true);

	echo debut_droite('', true);
	echo debut_cadre_relief('', true, '', 'Exporter des fiches');

	echo recuperer_fond('plugins/agenda_2_0/formulaires/inc-editer_evenement_pickers');

	echo '<form name="formRequete" method="post" action="',generer_url_ecrire('exportfiche'),'" style="margin:0; padding:0;">',"\n",'<br />Type de fiches <select name="typfich">',"\n";
	echo '<option value="3"',$typfich==3 ? ' selected="selected"' : '','>actions</option>';
	echo '<option value="1"',$typfich==1 ? ' selected="selected"' : '','>personnes</option>';
	echo '<option value="2"',$typfich==2 ? ' selected="selected"' : '','>suivi des inscriptions</option>';
	echo '</select>',"\n";
	echo '<br /><br />A partir de la date <input type="text" name="date_cond" id="date_cond" value="',date('d/m/Y', time() - 2592000),'" class="text date" />',"\n";
	echo '<br /><br />Format CSV avec s&eacute;parateur : virgule <input name="exportdlim" type="radio" value="v"',$exportdlim=='v' ? ' checked="checked"' : '',' /> ou point-virgule <input name="exportdlim" type="radio" value="p"',$exportdlim!='v' ? ' checked="checked"' : '',' />',"\n";
	echo '<br /><br /><input name="okconfirm" type="submit" value="Confirmer" class="fondo" /><br />&nbsp;',"\n",'</form>',"\n";

	echo fin_cadre_relief(true);
	echo fin_gauche();
	echo fin_page();
}
?>
