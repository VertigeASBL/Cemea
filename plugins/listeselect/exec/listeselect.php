<?php
/* --- richir vertige --- */
// execute automatiquement par le plugin au chargement de la page ?exec=listeselect
function exec_listeselect() {
	if (!defined('_ECRIRE_INC_VERSION')) return;

	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page('Selection de destinataires', '', '');

	echo '<br />',gros_titre('Selection de destinataires');

	echo debut_gauche('', true);
	echo debut_boite_info(true);
	echo 'Parmi les auteurs au statut de visiteur, sélectionner des destinataires et créer une liste de diffusion temporaire ou exporter des coordonnées au format CSV.',"\n";
	echo '<br /><br /><br />Pour un critère avec des cases à cocher, si toutes les cases sont cochées ou si aucune case n\'est cochée, alors la recherche ne tient pas compte de ce critère.',"\n";
	echo fin_boite_info(true);

	echo debut_droite('', true);
//--- echo debut_cadre_relief('', true, '', 'Selection de destinataires');

	
	if (_request('operliste')) $oper = 'lst';
	elseif (_request('opercsv')) $oper = 'csv';
	elseif (_request('operpdf')) $oper = 'pdf';

	$lilang = 'fr';

	if ($oper == 'lst') {
	/* ----------------------------------------------------
	   ----- Générer la liste de diffusion temporaire -----
	   ---------------------------------------------------- */
		include_spip('plugins/listeselect/exec/listewhere');
		$sql = fonclistewhere($lilang);
//echo '<hr />',$sql,'<hr />';

		$res = sql_select('A.id_auteur,E.spip_listes_format', 'spip_auteurs A,spip_auteurs_elargis E', 'E.id_auteur=A.id_auteur AND email<>\'\' AND '.$sql);
		$k = sql_count($res);
		echo '<b>Générer la liste de diffusion temporaire</b><br />Nombre de destinataires sélectionnés : ',$k,"\n";

		if ($k) {
			//--- si nécessaire, créer et vider la liste temporaire
			$idliste = sql_getfetsel('id_liste','spip_listes','titre LIKE \'%temporaire%\' AND statut=\'liste\'');
			if ($idliste) {
				sql_delete('spip_auteurs_listes', 'id_liste='.$idliste);
				sql_updateq('spip_listes', array('titre'=>'Liste de diffusion temporaire - '.date('Y-m-d H:i'),'lang'=>$lilang,'maj'=>time()), 'id_liste='.$idliste);
			}
			else
				$idliste = sql_insertq('spip_listes', array('titre'=>'Liste de diffusion temporaire - '.date('Y-m-d H:i'),'pied_page'=>'aucun','date'=>time(),'patron'=>'patron_even_FR','lang'=>$lilang,'maj'=>time(),'statut'=>'liste'));

			//--- abonner à la liste temporaire
			while ($tab = sql_fetch($res))
				sql_insertq('spip_auteurs_listes', array('id_auteur'=>$tab['id_auteur'], 'id_liste'=>$idliste, 'statut'=>'a_valider', 'format'=>$tab['spip_listes_format']));

			echo '<br />Ok, la liste de diffusion temporaire est mise à jour',"\n";
		}
		echo '<br />&nbsp;',"\n";
	}

	if ($oper == 'csv') {
	/* -------------------------------------------
	   ----- Exporter les coordonnées en CSV -----
	   ------------------------------------------- */
		include_spip('plugins/listeselect/exec/listewhere');
		$sql = fonclistewhere($lilang);

//echo '<hr />',$sql,'<hr />';

		$k = sql_countsel('spip_auteurs', $sql);
		echo '<b>Exporter les coordonnées en CSV</b><br />Nombre de destinataires sélectionnés : ',$k,"\n";

		if ($k) {
			echo '<br />Téléchargement du fichier CSV...',"\n";
			echo '<script type="text/javascript">',"\n",'<!--',"\n",'jQuery(document).ready(function(){',"\n";
			echo "\t",'fo = document.getElementById("idformsel");',"\n";
			echo "\t",'chn = fo.action; fo.action = "?exec=listexport"; fo.submit(); fo.action = chn;',"\n";
			echo '});',"\n",'//-->',"\n",'</script>',"\n";
		}
		echo '<br />&nbsp;',"\n";
	}

	if ($oper == 'pdf') {
		/* -------------------------------------------
	   ----- Exporter les coordonnées en PDF -----
	   ------------------------------------------- */
		include_spip('plugins/listeselect/exec/listewhere');
		$sql = fonclistewhere($lilang);
		
		echo '<hr />',$sql,'<hr />';

		$k = sql_countsel('spip_auteurs', $sql);
		echo '<b>Exporter les coordonnées en PDF</b><br />Nombre de destinataires sélectionnés : ',$k,"\n";

		if ($k) {
			echo '<br />Téléchargement du fichier PDF...',"\n";
			echo '<script type="text/javascript">',"\n",'<!--',"\n",'jQuery(document).ready(function(){',"\n";
			echo "\t",'fo = document.getElementById("idformsel");',"\n";
			echo "\t",'chn = fo.action; fo.action = "?exec=generer_etiquette"; '.$alert.' fo.submit(); fo.action = chn;',"\n";

			echo '});',"\n",'//-->',"\n",'</script>',"\n";
		}
		echo '<br />&nbsp;',"\n";

	}


	//----- Formulaire de critères -----
	$auteur = array(); $id_auteur = 0; $echec = ''; $new = 'oui'; $redirect = '';

	$auteur_infos = charger_fonction('auteur_liselect', 'inc');
	echo $auteur_infos($auteur, $new, $echec, _request('edit'), intval(_request('lier_id_article')), $redirect, 'edit');

	//--- echo fin_cadre_relief(true);
	echo fin_gauche();
	echo fin_page();
}
?>
