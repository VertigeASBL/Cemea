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

// chargement des valeurs par defaut des champs du formulaire
function formulaires_oubli_charger_dist(){
	$valeurs = array('oubli'=>'');
	return $valeurs;
}

// http://doc.spip.org/@message_oubli
function message_oubli($email, $login, $param)
{
	$r = formulaires_oubli_mail($email, $login);
	if (is_array($r) AND $r[1]) {
		include_spip('inc/acces'); # pour creer_uniqid
		include_spip('inc/texte'); # pour corriger_typo
		$cookie = creer_uniqid();
		sql_updateq("spip_auteurs", array("cookie_oubli" => $cookie), "id_auteur=" . $r[1]['id_auteur']);

		$nom = textebrut(corriger_typo($GLOBALS['meta']["nom_site"]));
		$envoyer_mail = charger_fonction('envoyer_mail','inc');

		if ($envoyer_mail($email,
				  ("[$nom] " .  _T('pass_oubli_mot')),
				  _T('pass_mail_passcookie',
				     array('nom_site_spip' => $nom,
					   'adresse_site' => url_de_base(),
					   'sendcookie' => generer_url_public('spip_pass',
					   "$param=$cookie", true)))) )
		  return _T('pass_recevoir_mail');
		else
		  return  _T('pass_erreur_probleme_technique');
	}
	return  _T('pass_erreur_probleme_technique');
}

// la saisie a ete validee, on peut agir
function formulaires_oubli_traiter_dist(){

	$message = message_oubli(_request('oubli'), _request('login'),'p');
	return array('message_ok'=>$message);
}

function selectionner_compte ($res) {

	$result = '<p>Cette adresse email est associ&eacute;e &agrave; plusieurs comptes.
Veuillez s&eacute;lectionner celui vous souhaitez r&eacute;initialiser.<p/>
<select name="login" id="login">';
	while  ($ligne = sql_fetch($res)) {
		$result .= '<option value="'. $ligne['login'] . '">' . $ligne['login'] . '</option>';
	}
	$result .= '</select><br/>';
	return $result;
}

// fonction qu'on peut redefinir pour filtrer les adresses mail
// http://doc.spip.org/@test_oubli
function test_oubli_dist($email, $login)
{
	include_spip('inc/filtres'); # pour email_valide()
	if (!email_valide($email) )
		return _T('pass_erreur_non_valide', array('email_oubli' => htmlspecialchars($email)));

	// on test si l'email est associé à plusieurs comptes.
	// Dans ce cas il faut choisir lequel reinitialiser.
	if ($res = sql_select('login', 'spip_auteurs', 'email='.sql_quote($email))) {
		if (sql_count($res) > 1) {
			if (! $login)
				return _T(selectionner_compte($res));
		}
	}

	return array('mail' => $email,
							 'login' => $login);
}

function formulaires_oubli_verifier_dist(){
	$erreurs = array();

	$email = strval(_request('oubli'));
	$login = strval(_request('login'));

	$r = formulaires_oubli_mail($email, $login);

	if (!is_array($r))
		$erreurs['oubli'] = $r;
	else {
		if (!$r[1])
			$erreurs['oubli'] = _T('pass_erreur_non_enregistre', array('email_oubli' => htmlspecialchars($email)));

		elseif ($r[1]['statut'] == '5poubelle' OR $r[1]['pass'] == '')
			$erreurs['oubli'] =  _T('pass_erreur_acces_refuse');
	}

	return $erreurs;
}

function formulaires_oubli_mail($email, $login)
{
	if (function_exists('test_oubli'))
		$f = 'test_oubli';
	else
		$f = 'test_oubli_dist';
	$declaration = $f($email, $login);

	if (!is_array($declaration))
		return $declaration;
	else {
		include_spip('base/abstract_sql');
		if (! $declaration['login']) {
			return array($declaration, sql_fetsel("id_auteur,statut,pass", "spip_auteurs", "email =" . sql_quote($declaration['mail'])));
		} else {
			return array($declaration, sql_fetsel("id_auteur,statut,pass", "spip_auteurs", "login =" . sql_quote($declaration['login'])));
		}
	}
}
?>
