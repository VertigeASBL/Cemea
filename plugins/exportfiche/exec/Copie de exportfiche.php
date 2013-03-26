<?php
// execute automatiquement par le plugin au chargement de la page ?exec=exportfiche
function exec_exportfiche() {
	$exportdlim = _request('exportdlim');
	$typfich = (int) _request('typfich');

	if (_request('okconfirm')) {
		$exportdlim = $exportdlim == 'p' ? ';' : ',';

		function majuscule($chn) {
			$chn = strtr(strtoupper($chn), utf8_decode('àáâãäåæçèéêëìíîïðñòóôõöøùúûüý'), utf8_decode('ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝ'));
			return $chn;
		}
		function filtrecsv($chn, $col) {
			$chn = str_replace(chr(226).chr(128).chr(153), '\'', $chn); //--- remplacer  par '
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
			foreach ($fields as $k => $value)
				/*if ($k)*/ {
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

		//--- PA de l'année courante, condition sur id_rubrique
		$cond_annee = '';
		$k = date('Y');
		if (date('n') > 6)
			$k++; 
		if ($k = sql_getfetsel('id_rubrique','spip_rubriques','id_secteur=4 AND titre=\''.$k.'\' AND statut=\'publie\''))
			$cond_annee = ' AND id_rubrique='.$k;
/* $cond_annee = ' AND id_rubrique=20'; //--- test année 2010
$cond_annee = ' AND id_rubrique=39'; */

/* **** Ecrire CSV *****
	trel[] > 0 && trow[] == '' : mot_clé via spip_mots_articles
	trel[] > 0 && trow[] == champ : mot_clé via champ extra
	trel[] == -1 : numéro d'atelier
	trel[] == -2 : image document d'article
	trel[] == -3 : légende document d'article
*/
		switch ($typfich) {
		case 1: //--- auteurs personne
			$trow = array(0=>'id_auteur','nom','bio','email','login','statut','maj','idper','archive_per','prenom','codecourtoisie','fonction','typepart','nom_court_institution','nom_long_institution','description_institution','date_naissance','adresse','codepostal','localite','tel1','tel2','gsm1','gsm2','fax1','fax2','email2','date_creation','date_maj','statut_form_cemea','statut_anim_cemea','statut_ep','statut_sj','personne_reference','centre_reference','envoi_diffusion','date_debut_diffusion','date_fin_diffusion','diffusion','ndiffusion','adherent');
			$tlab = array(0=>'id_auteur','nom','bio','email','login','statut','maj','idper','archive_per','prenom','codecourtoisie','fonction','typepart','nom_court_institution','nom_long_institution','description_institution','date_naissance','adresse','codepostal','localite','tel1','tel2','gsm1','gsm2','fax1','fax2','email2','date_creation','date_maj','statut_form_cemea','statut_anim_cemea','statut_ep','statut_sj','personne_reference','centre_reference','envoi_diffusion','date_debut_diffusion','date_fin_diffusion','diffusion','ndiffusion','adherent');
			$k = 'personnes';
			$r_from = 'spip_auteurs';
			$r_cond = 'statut<>\'5poubelle\'';
			$r_ordr = 'nom';
			break;
		case 2: //--- auteurs_articles suivi
			$trow = array(0=>'S.id_auteur','P.nom','P.statut','S.id_article','A.titre','statutsuivi','date_suivi');
			$tlab = array(0=>'id_auteur','personne','statut','id_article','action','statutsuivi','date_suivi');
			$k = 'suivi_inscr';
			$r_from = 'spip_auteurs_articles AS S,spip_auteurs AS P,spip_articles AS A';
			$r_cond = 'S.inscrit<>\'\' AND P.id_auteur=S.id_auteur AND A.id_article=S.id_article';
			$r_ordr = 'S.date_suivi';
			break;
		case 12: //--- articles action
			$trow = array(0=>'id_article','titre','chapo','texte','date','maj','date_modif','nom_site','url_site','idact','archive_act','dossier','semestre','max_part','assurance','date_debut','dates_ra','titre_ra','lieu','commanditaire','nb_formateurs_ra','nb_part_ra','par_unite_ra','type_unite_ra','nb_unite_ra','insertion','cocof_rec','subord','dates_scir','titre_scir','nb_part_scir','par_unite_scir','type_unite_scir','nb_unite_scir','date_c','date_maj','type_act','centre_org','asbl','cac','fonctionnel','secteur','cursus_formation','diffusion');
			$tlab = array(0=>'id_article','titre','chapo','texte','date','maj','date_modif','nom_site','url_site','idact','archive_act','dossier','semestre','max_part','assurance','date_debut','dates_ra','titre_ra','lieu','commanditaire','nb_formateurs_ra','nb_part_ra','par_unite_ra','type_unite_ra','nb_unite_ra','insertion','cocof_rec','subord','dates_scir','titre_scir','nb_part_scir','par_unite_scir','type_unite_scir','nb_unite_scir','date_c','date_maj','type_act','centre_org','asbl','cac','fonctionnel','secteur','cursus_formation','diffusion');
			$k = 'actions';
			$r_from = 'spip_articles';
			$r_cond = 'statut=\'publie\''; //--- .$cond_annee;
			$r_ordr = 'titre';
			break;
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

		//--- les colonnes de la requête
		reset($trow); $data = '';
		while (list($k, $col) = each($trow))
			/*if ($col)*/ {
				$data .= $data ? ','.$col : $col;
				if ($col{1} == '.')
					$trow[$k] = substr($col, 2); //--- enlever les alias (1! lettre par exemple "R.")
			}
//echo '<hr />',$data,'<hr />';
		$req = sql_select($data, $r_from, $r_cond, null, $r_ordr); //--- , '10' : test 10 maximum

		unset($tab);
		while ($data = sql_fetch($req)) {
			reset($trow);
			while (list($k, $col) = each($trow)) {
				$tab[$k] = $data[$col];
				if ($col == 'titre')
					$tab[$k] = supprimer_numero($tab[$k]);
				if ($col == 'statut')
					switch ($tab[$k]) {
						case '6forum': $tab[$k] = 'visit'; break;
						case '1comite': $tab[$k] = 'redac'; break;
						case '0minirezo': $tab[$k] = 'admin'; break;
					}
				if ($col == 'maj' || $col == 'date' || $col == 'date_modif')
					$tab[$k] = substr($tab[$k], 0, 10);
				if (substr($col, 0, 5) == 'date_' && $tab[$k] == '0000-00-00')
					$tab[$k] = '';

				$tab[$k] = filtrecsv($tab[$k], $col);
			}
			maputcsv($tab, $exportdlim);
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

	echo '<form name="formRequete" method="post" action="',generer_url_ecrire('exportfiche'),'" style="margin:0; padding:0;">',"\n",'<br />Type de fiches <select name="typfich">',"\n";
	echo '<option value="12"',$typfich==12 ? ' selected="selected"' : '','>actions</option>';
	echo '<option value="1"',$typfich==1 ? ' selected="selected"' : '','>personnes</option>';
	echo '<option value="2"',$typfich==2 ? ' selected="selected"' : '','>suivi des inscriptions</option>';
	echo '</select>',"\n",'<br /><br />Format CSV avec s&eacute;parateur : virgule <input name="exportdlim" type="radio" value="v"',$exportdlim=='v' ? ' checked="checked"' : '',' /> ou point-virgule <input name="exportdlim" type="radio" value="p"',$exportdlim!='v' ? ' checked="checked"' : '',' />',"\n";
	echo '<br /><br /><input name="okconfirm" type="submit" value="Confirmer" class="fondo" /><br />&nbsp;',"\n",'</form>',"\n";

	echo fin_cadre_relief(true);
	echo fin_gauche();
	echo fin_page();
}
?>
