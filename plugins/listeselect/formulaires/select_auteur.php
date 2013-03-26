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

include_spip('inc/actions');
include_spip('inc/editer');

/* function instituer_auteur_ici($auteur=array()){
	$instituer_auteur = charger_fonction('instituer_auteur', 'inc');
	return $instituer_auteur($auteur);
}
*/
// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_select_auteur_charger_dist($id_auteur='new', $retour='', $lier_id_article=0, $config_fonc='auteurs_edit_config', $row=array(), $hidden=''){
	$valeurs = formulaires_editer_objet_charger('auteur',$id_auteur,0,0,$retour,$config_fonc,$row,$hidden);
	// forcer la prise en compte du post, sans verifier si c'est bien le meme formulaire,
	// c'est trop hasardeux selon le contenud de $row
	$valeurs['_forcer_request'] = true;
	$valeurs['exportdlim'] = 'p';

	$valeurs['action_eti'] = _request('action_eti');

	return $valeurs;
}

/**
 * Identifier le formulaire en faisant abstraction des parametres qui
 * ne representent pas l'objet edite
 */
function formulaires_select_auteur_identifier_dist($id_auteur='new', $retour='', $lier_id_article=0, $config_fonc='auteurs_edit_config', $row=array(), $hidden=''){
	return serialize(array($id_auteur,$lier_id_article,$row));
}


// Choix par defaut des options de presentation
// http://doc.spip.org/@articles_edit_config
function auteurs_edit_config($row)
{
	global $spip_ecran, $spip_lang, $spip_display;

	$config = $GLOBALS['meta'];
	$config['lignes'] = ($spip_ecran == "large")? 8 : 5;
	$config['langue'] = $spip_lang;

	// pour instituer_auteur
	$config['auteur'] = $row;
	
	//$config['restreint'] = ($row['statut'] == 'publie');
	$auth_methode = $row['source'];
	include_spip('inc/auth');
	include_spip('inc/autoriser');
	$autoriser = autoriser('modifier','auteur',$row['id_auteur'],null, array('restreintes'=>true));
	$config['edit_login'] = false;
	$config['edit_pass'] = false;

	return $config;
}

function formulaires_select_auteur_verifier_dist($id_auteur='new', $retour='', $lier_article=0, $config_fonc='auteurs_edit_config', $row=array(), $hidden=''){
//	if ($email = _request('email'))
//	$email!=($email_ancien=sql_getfetsel('email', 'spip_auteurs', 'id_auteur='.intval($id_auteur)))
//	$erreurs['email'] = _T('form_email_non_valide');

	return $erreurs;
}

// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_select_auteur_traiter_dist($id_auteur='new', $retour='', $lier_article=0, $config_fonc='auteurs_edit_config', $row=array(), $hidden=''){
	$res['message_ok'] = '';
	unset($res['redirect']);
	return $res;
}
?>
