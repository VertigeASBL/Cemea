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

function instituer_auteur_ici($auteur=array()){
	$instituer_auteur = charger_fonction('instituer_auteur', 'inc');
	return $instituer_auteur($auteur);
}

// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_adherer_auteur_charger_dist($id_auteur='new', $retour='', $lier_id_article=0, $config_fonc='auteurs_adher_config', $row=array(), $hidden=''){
	$id_auteur = 0;
	$valeurs = formulaires_editer_objet_charger('auteur',$id_auteur,0,0,$retour,$config_fonc,$row,$hidden);
	$valeurs['id_auteur'] = 0;
	$valeurs['lier_id_article'] = 0;
	// forcer la prise en compte du post, sans verifier si c'est bien le meme formulaire, c'est trop hasardeux selon le contenud de $row
	$valeurs['_forcer_request'] = true;
	$valeurs['cotepublic'] = 'y';
	return $valeurs;
}

/**
 * Identifier le formulaire en faisant abstraction des parametres qui ne representent pas l'objet edite
 */
function formulaires_adherer_auteur_identifier_dist($id_auteur='new', $retour='', $lier_id_article=0, $config_fonc='auteurs_adher_config', $row=array(), $hidden=''){
	return serialize(array($id_auteur,$lier_id_article,$row));
}


// Choix par defaut des options de presentation
// http://doc.spip.org/@articles_edit_config
function auteurs_adher_config($row)
{
	global $spip_ecran, $spip_lang, $spip_display;

	$config = array();
	// pour instituer_auteur
	$config['auteur'] = $row;
	
	$auth_methode = $row['source'];
	include_spip('inc/auth');
	include_spip('inc/autoriser');
	$autoriser = autoriser('modifier','auteur',$row['id_auteur'],null, array('restreintes'=>true));
	$config['edit_login'] = (auth_autoriser_modifier_login($auth_methode) AND $autoriser);
	$config['edit_pass'] = (auth_autoriser_modifier_pass($auth_methode) AND ($GLOBALS['visiteur_session']['id_auteur'] == $row['id_auteur'] OR $autoriser));

	return $config;
}

function formulaires_adherer_auteur_verifier_dist($id_auteur='new', $retour='', $lier_id_article=0, $config_fonc='auteurs_adher_config', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('auteur',$id_auteur,array('nom'));

	$p = _request('jadhere');
	if (! $p)
		$erreurs['jadhere'] = _T('info_obligatoire');
	
	$p = _request('prenom');
	if (! $p)
		$erreurs['prenom'] = _T('info_obligatoire');
	$p = _request('adresse');
	if (! $p)
		$erreurs['adresse'] = _T('info_obligatoire');
	$p = _request('codepostal');
	if (! $p)
		$erreurs['codepostal'] = _T('info_obligatoire');
	$p = _request('localite');
	if (! $p)
		$erreurs['localite'] = _T('info_obligatoire');

	include_spip('inc/filtres'); //--- verifier 1e adr email
	$p = _request('email');
	if (! $p)
		$erreurs['email'] = _T('info_obligatoire');
	else if (! email_valide($p))
		$erreurs['email'] = _T('form_email_non_valide');

	include_spip('inc/date_gestion'); //--- verifier date naissance
	$err = array();
	$p = _request('date_naissance');
	if (! @verifier_corriger_date_saisie('naissance', false, $err))
		$erreurs['date_naissance'] = $err['date_naissance'];
	if (! $p)
		$erreurs['date_naissance'] = _T('info_obligatoire');

	if (! ($p = _request('localite')) && ($p = _request('otr_localite'))) //--- autre localite
		set_request('localite', $p);

	if (count($erreurs) && ! isset($erreurs['message_erreur']))
		$erreurs['message_erreur'] = 'Votre saisie contient des erreurs.';
	return $erreurs;
}

// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_adherer_auteur_traiter_dist($id_auteur='new', $retour='', $lier_id_article=0, $config_fonc='auteurs_adher_config', $row=array(), $hidden=''){
	if (_request('saisie_webmestre') OR _request('webmestre'))
		set_request('webmestre',_request('webmestre')?_request('webmestre'):'non');
	$retour = parametre_url($retour, 'email_confirm','');

	$id_article = 0;
	$id_auteur = 0;

	//--- Préparer le message
	$message_ok = 'Votre demande d\'adhésion est enregistrée.';

	$auteurnom = _request('nom');
	$auteurprenom = _request('prenom');
	$auteurmail = _request('email');
	$p = _request('date_naissance');
	if (preg_match('|^\d{2}\D\d{2}\D\d{4}$|', $p)) { //--- renverser la date
		$auteurnaiss = substr($p, 6, 4).'-'.substr($p, 3, 2).'-'.substr($p, 0, 2);
	}
	else
		$auteurnaiss = '0000-00-00';
	set_request('date_naissance', $auteurnaiss);
        
        //Generate login from name+2 first letters of fname+birth
        $auteurlogin=preg_replace('/[^a-z0-9]/', '', strtr(strtolower(utf8_decode($auteurnom)), utf8_decode('àâçèéêëîïôùûü'), 'aaceeeeiiouuu')).
            preg_replace('/[^a-z0-9]/', '', strtr(strtolower(utf8_decode($auteurprenom)), utf8_decode('àâçèéêëîïôùûü'), 'aaceeeeiiouuu'));
        $userpasse = substr(md5(time()), 0, 8);

	//--- nouvel auteur
	if (! $id_auteur) {
		//--- si deja inscrit, empecher re-post
		if (sql_getfetsel('id_auteur', 'spip_auteurs', 'nom='.sql_quote($auteurnom).' AND prenom='.sql_quote($auteurprenom).' AND date_naissance='.sql_quote($auteurnaiss).' AND typepart=\'S\'')) {
			$res['message_erreur'] = 'Vous semblez être déjà enregistré(e).'; //--- 'OR email<>\'\' AND email='.sql_quote($auteurmail)
			$res['editable'] = false;
			return $res;
		}

		set_request('statut', '6forum');
                
                //Check if login exists
                if ($found_logins=sql_getfetsel('login', 'spip_auteurs', 'login LIKE "'.$auteurlogin.'%"','login','login DESC')) {
                    $login_index=substr($found_logins, strlen($auteurlogin));
                    if($login_index!="") {
                        $next_login_index=number_format($login_index)+1;
                    } else {
                        $next_login_index=1;
                    }
                    $auteurlogin=$auteurlogin.$next_login_index;
		}
                
		set_request('login', $auteurlogin);
                set_request('pass', $userpasse);
                set_request('idper', date('Y'));
		set_request('archive_per', 'N');
		set_request('typepart', 'S');
		set_request('adherent', 'Y');
		$k = date('Y-m-d');
		set_request('date_creation', $k);
		set_request('date_maj', $k);
		set_request('personne_reference', 'adher');
		set_request('envoi_diffusion', 'N');
		set_request('date_debut_diffusion', $k);
		set_request('date_fin_diffusion', date('Y-m-d', time()+126230400));

		$p = _request('diffusion'); //--- envoyer 1 exemplaire par diffusion cochee
		if (is_array($p)) {
			$t2 = array(); reset($p);
			while (list(, $k) = each($p))
				{ $t2[] = $k; $t2[] = 1; }
			set_request('ndiffusion', $t2);
			unset($t2);
		}
	}
	if (! ($p = _request('localite')) && ($p = _request('otr_localite'))) //--- autre localite
		set_request('localite', $p);

	//--- creation de l'auteur
	$res = formulaires_editer_objet_traiter('auteur',$id_auteur,0,0,$retour,$config_fonc,$row,$hidden);
	if (isset($res['id_auteur']) && $res['id_auteur'] && is_numeric($res['id_auteur']))
		$id_auteur = (int) $res['id_auteur'];

	$envoyer_mail = charger_fonction('envoyer_mail','inc');

	//--- avertir admin
	if ($id_auteur) {
		$p = 'Bonjour,'."\n\n".'Voici une nouvelle adhésion :'."\n\n";
		$p .= 'Nom : '.$auteurnom."\n";
		$p .= 'Prénom : '.$auteurprenom."\n";
		$p .= 'id_auteur : '.$id_auteur."\n";
		$p .= "\n".'-----'."\n";

		$p = $envoyer_mail($GLOBALS['meta']['email_webmaster'].', inscriptions@cemea.be', $GLOBALS['meta']['nom_site'].' : adhésion '.$id_auteur, $p, $GLOBALS['meta']['email_webmaster']);
		if (! $p)
			$message_ok .= '<br />echec envoi adhésion';
	}

	//--- abonnement spip-listes à la liste publique principale
	if ($id_auteur && $auteurmail) {
		if ($p = sql_getfetsel('id_liste', 'spip_listes', 'lang='.sql_quote($GLOBALS['spip_lang']), '', 'id_liste', '0,1')) {
			if (! sql_getfetsel('id_auteur', 'spip_auteurs_elargis', 'id_auteur='.$id_auteur))
				sql_insertq('spip_auteurs_elargis', array('id_auteur'=>$id_auteur, 'spip_listes_format'=>'html'));
			$p = (int) $p;
			if (! sql_getfetsel('id_auteur', 'spip_auteurs_listes', 'id_auteur='.$id_auteur.' AND id_liste='.$p))
				sql_insertq('spip_auteurs_listes', array('id_auteur'=>$id_auteur, 'id_liste'=>$p, 'date_inscription'=>date('Y-m-d H:i:s'), 'statut'=>'a_valider', 'format'=>'html'));
		}

		//--- envoyer login et mot de passe
                $p = 'Bonjour,'."\n\n";
                $p .= 'Nom : '.$auteurnom."\n";
                $p .= 'Prénom : '.$auteurprenom."\n\n";
                if ($auteurlogin) {
                        $p .= 'Pour pouvoir vous connecter à '.$GLOBALS['meta']['nom_site'].', vous aurez besoin de ces données :'."\n\n";
                        $p .= 'Nom d\'utilisateur : '.$auteurlogin."\n".'Mot de passe : '.$userpasse."\n\n";
                        $p .= 'Et l\'adresse pour vous connecter : '.generer_url_entite_absolue(6, 'article')."\n\n";
                        $p .= 'Ces nom d\'utilisateur et mot de passe vous serviront lors de vos prochaines visites sur notre site'."\n\n";
                        $p .= 'Vous pouvez changer votre mot de passe ici : ';
                }
                else
                        $p .= 'Vous pouvez récupérer votre mot de passe oublié ou changer votre mot de passe ici : ';
                $p .= generer_url_public('spip_pass','lang='.$GLOBALS['spip_lang'],true)."\n\n"; //--- spip.php?page=spip_pass
                $p .= '    Avec les meilleures salutations des '.$GLOBALS['meta']['nom_site']."\n";

                if ($envoyer_mail($auteurmail, $GLOBALS['meta']['nom_site'].' : votre compte', $p, $GLOBALS['meta']['email_webmaster']))
                        $message_ok .= '<br />Vous recevrez d’ici peu un email de confirmation.';
                else
                        $message_ok .= '<br />L\'envoi de l\'email de confirmation a échoué.';

	}
                
	if (isset($res['message_ok']))
		$res['message_ok'] = $message_ok;
	unset($res['redirect']);
	return $res;
}
?>
