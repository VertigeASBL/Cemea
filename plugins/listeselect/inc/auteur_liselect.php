<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined('_ECRIRE_INC_VERSION')) return;

// Affiche la fiche de renseignements d'un auteur
// eventuellement editable
// $quoi introduit pour pouvoir demander simplement les infos ou la partie editable
// ""=>tout, "infos"=>infos simplement, "edit"=>formulaire d'edition simplement
// http://doc.spip.org/@inc_auteur_liselect_dist
function inc_auteur_liselect_dist($auteur, $new, $echec, $edit, $id_article, $redirect, $quoi="") {

	if (!$new AND $quoi!="edit") {
		$infos = legender_auteur_voir($auteur);
	} else
		$infos = '';

	$id_auteur = intval($auteur['id_auteur']);
	
	// Elaborer le formulaire
	$corps = "<div id='auteur_liselect_edit'>\n";

	$editer = ($new=='oui');
	if ($editer&$redirect) {
		$retour = rawurldecode($redirect);
	} elseif ($id_auteur){
		$retour = generer_url_ecrire('auteur_liselect','id_auteur='.$id_auteur, false, true);
	} else {
		$retour = "";
	}

	$contexte = array(
		'icone_retour'=>($retour)?icone_inline(_T('icone_retour'),$retour,"auteur-24.gif","rien.gif",$GLOBALS['spip_lang_left'],false,($editer&$redirect)?"":" onclick=\"jQuery('#auteur_liselect_edit').hide();jQuery('#auteur-voir').show();return false;\""):"",
		'redirect'=>$redirect?rawurldecode($redirect):generer_url_ecrire('auteur_liselect','id_auteur='.$id_auteur, '&',true),
		'titre'=>($auteur['nom']?$auteur['nom']:_T('nouvel_auteur')),
		'new'=>$new == "oui"?$new:$id_auteur,
		'config_fonc'=>'auteurs_edit_config',
		'lier_id_article' => $id_article,
		'auteur' => $auteur
	);

	$corps .= recuperer_fond("plugins/listeselect/auteursel", $contexte);
	$corps .= '</div>';

	// ajouter les infos, si l'on ne demande pas simplement le formulaire d'edition
	if ($quoi!="edit") {
		$corps =  $infos . $corps;
	}

	// Installer la fiche "auteur_liselect_voir"
	// et masquer le formulaire si on n'en a pas besoin

	if (!$new AND !$echec AND !$edit) {
	  $corps .= http_script("if (jQuery('#auteur_liselect_edit span.erreur_message,#auteur_liselect_edit .reponse_formulaire_erreur').length){jQuery('#auteur-voir').hide();}else{jQuery('#auteur_liselect_edit').hide();}");
	} else {
	  $corps .= http_script("jQuery('#auteur-voir').hide();");
	}

	return $corps;
}

// http://doc.spip.org/@afficher_erreurs_auteur
function afficher_erreurs_auteur($echec) {
	foreach (explode('@@@',$echec) as $e)
		$corps .= '<p>' . _T($e) . "</p>\n";

	$corps = debut_cadre_relief('', true)
	.  "<span style='color: red; left-margin: 5px'>"
	.  http_img_pack("warning.gif", _T('info_avertissement'), "style='width: 48px; height: 48px; float: left; margin: 5px;'")
	. $corps
	.  _T('info_recommencer')
	.  "</span>\n"
	. fin_cadre_relief(true);

	return $corps;
}


// http://doc.spip.org/@legender_auteur_saisir
//
// Apparaitre dans la liste des redacteurs connectes
//

// http://doc.spip.org/@apparait_auteur_liselect
function apparait_auteur_liselect($id_auteur, $auteur) {
	return '';
}

// http://doc.spip.org/@legender_auteur_voir
function legender_auteur_voir($auteur) {
	return '';
}
?>
