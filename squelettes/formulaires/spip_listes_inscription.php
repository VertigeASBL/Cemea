<?php
/**
 * Formulaire d'inscription
 * 
 * @since SPIP 2.0
 * @see http://www.spip.net/fr_article3796.html
 * @see formulaires/spip_listes_inscription.html
 * 		qui est le squelette de construction
 * 		utilisé ici
 * @package spiplistes
 */
 // $LastChangedRevision: 48919 $
 // $LastChangedBy: root $
 // $LastChangedDate: 2011-06-19 11:00:08 +0200 (Sun, 19 Jun 2011) $
	
include_spip('inc/acces');
include_spip('inc/spiplistes_api');

include_spip('inc/actions');
include_spip('inc/editer');

/**
 * @return array
 */
function formulaires_spip_listes_inscription_charger_dist ($id_liste='')
{
	$soumettre = _request('listeseul') ? 'oui' : '';
	$listeseul = is_numeric($id_liste) ? $id_liste : '';

	$valeurs = formulaires_editer_objet_charger('auteur',0,0,0,'','','','');

	include_spip('inc/filtres');
	$email = _request('email');
	if (trim($email)) {
		if (email_valide($email)) {
			// Verifier si le mail est deja connu
			if (sql_getfetsel('id_auteur','spip_auteurs',"id_auteur !='".intval($id_auteur)."' AND email = '$email'"))
				$valeurs['message_erreur'] = _T('spiplistes:cet_email_deja_enregistre') . '<br /><a style="left: 400px; position: relative" href="spip.php&#63;page&#61;spip_pass">(mot de passe oublié ?)</a><br />';
		}
		else
			$valeurs['message_erreur'] = _T('spiplistes:cet_email_pas_valide');
	}
	$valeurs['email'] = $email;

	$valeurs['id_liste'] = $id_liste;
	$valeurs['listeseul'] = $listeseul;
	$valeurs['soumettre'] = $soumettre;
	
	$valeurs['cotepublic'] = 'y';
	return $valeurs;
}

/**
 * @return array
 */
function formulaires_spip_listes_inscription_verifier_dist ($id_liste='')
{
	$erreurs = array();
	
	/* verifier que les champs obligatoires sont bien la :
	foreach(array('email') as $obligatoire) {
		if (!_request($obligatoire))
		{
			$erreurs[$obligatoire] = _T('spiplistes:champ_obligatoire');
		}
	} */
	
	if (!in_array(_request('format_abo'), array('html','texte')))
	{
		$erreurs['format'] = 'format inconnu';
	}
	
	$listes = _request('listes');
	
	$listes_sel = array();
	
	if (is_array($listes))
	{
		foreach($listes as $liste)
		{
			$id_liste = intval($liste);
			if ($id_liste > 0) 
			{
				$listes_sel[] = $id_liste;
			}
		}
	}

	// verifier que si un email a ete saisi, il est bien valide :
	include_spip('inc/filtres');
	$email = _request('email');
	if (trim($email)) {
		if (email_valide($email)) {
			// Verifier si le mail est deja connu
			if (sql_getfetsel('id_auteur','spip_auteurs',"id_auteur !='".intval($id_auteur)."' AND email = '$email'"))
				$erreurs['email'] = _T('spiplistes:cet_email_deja_enregistre') . '<br /><a style="left: 400px; position: relative" href="spip.php&#63;page&#61;spip_pass">(mot de passe oublié ?)</a><br />';
		}
		else
			$erreurs['email'] = _T('spiplistes:cet_email_pas_valide');
	}
	else
		$erreurs['email'] = _T('info_obligatoire');

	if (count($erreurs) && ! isset($erreurs['message_erreur']))
		$erreurs['message_erreur'] = _T('spiplistes:saisie_erreurs');
    return ($erreurs); // si c'est vide, traiter sera appele, sinon le formulaire sera re-soumis
}

/**
 * Traite les donnees du formulaire de saisie
 * - valide l'adresse mail
 * - l'enregistre si manquant
 * - l'abonne aux listes souhaitees
 * - envoie un mail de confirmation
 * @return array
 */
function formulaires_spip_listes_inscription_traiter_dist ($id_liste = '') {
	
	/**
	 * Un abonné doit etre enregistre
	 * dans spip_auteurs,
	 * spip_auteurs_elargis, (historique, pour le format de réception)
	 * spip_auteurs_listes (table des abonnements)
	 */
	
	include_spip('inc/spiplistes_api_courrier');
	
	$val['email'] = _request('email');
	$val['email'] = email_valide ($val['email']);
	
	if ($val['email'])
	{
		// Verifier si le mail est deja connu
		if (sql_getfetsel('id_auteur','spip_auteurs',"id_auteur !='".intval($id_auteur)."' AND email = '".$val['email']."'")) {
			$contexte = array('message_ok'=>_T('spiplistes:cet_email_deja_enregistre'),'editable' => false,);
			return ($contexte);
		}

		$val['nom'] = _request('email');
		$val['lang'] = _request('lang');
		if (!$val['lang']) {
			$val['lang'] = $GLOBALS['meta']['langue_site'];
		}
		$val['login'] = preg_replace('/[^a-z0-9]/', '', strtr(strtolower(utf8_decode($val['nom'])), utf8_decode('àâçèéêëîïôùûü'), 'aaceeeeiiouuu'));
		$val['pass'] = substr(md5(time()), 0, 8);

		$val['alea_actuel'] = creer_uniqid();
		$val['alea_futur'] = creer_uniqid();
		$val['low_sec'] = '';
		$val['statut'] = '6forum';
		
		$format = _request('format_abo');
		if (!$format) {
			$format = spiplistes_format_abo_default ();
		}
		$listes = _request('listes');

		//--- champs extra
		$val['idper'] = date('Y');
		$val['archive_per'] = 'N';
		$val['adherent'] = 'N';
		$k = date('Y-m-d');
		$val['date_creation'] = $k;
		$val['date_maj'] = $k;
		$val['personne_reference'] = 'abon';
		$val['envoi_diffusion'] = 'Y';
		$val['date_debut_diffusion'] = $k;
		$val['date_fin_diffusion'] = date('Y-m-d', time()+126230400);

		$k = _request('diffusion');
		$val['diffusion'] = is_array($k) ? implode(',', $k) : '';
	}
	
	// Si le compte n'existe pas, le créer
	if ($id_auteur = spiplistes_auteurs_auteur_insertq ($val))
	{
		spiplistes_format_abo_modifier ($id_auteur, $format);
                
                //Send login and PW via e-mail
                $p = 'Bonjour,'."\n\n".'Votre inscription'.' est enregistrée.'."\n\n";
                $p .= 'Pour pouvoir vous connecter à '.$GLOBALS['meta']['nom_site'].', vous aurez besoin de ces données :'."\n\n";
                $p .= 'Nom d\'utilisateur : '.$val['login']."\n".'Mot de passe : '.$val['pass']."\n\n";
                $p .= 'Et l\'adresse pour vous connecter : '.generer_url_entite_absolue(6, 'article')."\n\n";
                $p .= 'Ces nom d\'utilisateur et mot de passe vous serviront lors de vos prochaines visites sur notre site'."\n\n";
                $p .= 'Vous pouvez changer votre mot de passe ici : ';
                $p .= generer_url_public('spip_pass','lang='.$GLOBALS['spip_lang'],true)."\n\n"; //--- spip.php?page=spip_pass
                $p .= '    Avec les meilleures salutations des '.$GLOBALS['meta']['nom_site']."\n";
                
                $login_mail=spiplistes_envoyer_mail($val['email'], $GLOBALS['meta']['nom_site'].' : votre inscription', $p, $GLOBALS['meta']['email_webmaster']);                        
                
	}
	$contexte['nouvel_inscription'] = 'oui';
	$contexte['id_auteur'] = $id_auteur;
	$contexte['nom'] = $val['nom'];
	$contexte['statut'] = $val['statut'];
	$contexte['lang'] = $GLOBALS['meta']['langue_site'];

	spiplistes_debug_log ('NEW inscription email:'.$val['email']);
	
	if ($listes && is_array($listes) && count($listes))
	{
		spiplistes_abonnements_ajouter ($id_auteur, $listes);
		$contexte['ids_abos'] = array_values($listes);
	}
	
	/**
	 * Construit le message à partir du patron
	 */
	if ($id_auteur > 0)
	{
		$cur_format = spiplistes_format_abo_demande ($id_auteur);
		if (!$cur_format)
		{
			$cur_format = $format;
			spiplistes_format_abo_modifier ($id_auteur, $format);
		}
		$contexte['format'] = $cur_format;
		$nom_site_spip = spiplistes_nom_site_texte ($lang);
		$email_objet = '['.$nom_site_spip.'] '._T('spiplistes:confirmation_inscription');

		/**
		 * Le cookie pour le lien direct
		 */
		$cookie = creer_uniqid();
		spiplistes_auteurs_cookie_oubli_updateq($cookie, $val['email'], false);
		spiplistes_debug_log ('COOKIE: '.$cookie);
		$contexte['cookie_oubli'] = $cookie;
		
		/**
		* Assemble le patron
		* Obtient en retour le contenu en version html et texte
		*/
		$path_patron = _SPIPLISTES_PATRONS_MESSAGES_DIR . spiplistes_patron_message();
		
		list($courrier_html, $courrier_texte) = spiplistes_courriers_assembler_patron (
			$path_patron
			, $contexte);
		//spiplistes_debug_log ('Messages size: html: '.strlen($courrier_html));
		//spiplistes_debug_log ('Messages size: text: '.strlen($courrier_texte));
		
		$email_contenu = array(
				/**
				 * La version HTML du message
				 */
				'html' => '<html>' . PHP_EOL
					. '<body>' . PHP_EOL
					. $courrier_html
					. '</body>' . PHP_EOL
					. '</html>' . PHP_EOL
				/**
				 * Et la version texte
				 */
				, 'texte' => $courrier_texte
				);
	}
	
	/**
	 * envoyer mail de confirmation
	 */
	if ($id_auteur
		&& spiplistes_envoyer_mail (
			$val['email']
			, $email_objet
			, $email_contenu
			, false
			, ''
			, $format
	   )
	) {
		$contexte = array('message_ok'=>_T('spiplistes:demande_ok'),'editable' => false,);
	}
	else {
		$contexte = array('message_ok'=>_T('spiplistes:demande_ko'),'editable' => false,);
	}
	
	return ($contexte);
}
