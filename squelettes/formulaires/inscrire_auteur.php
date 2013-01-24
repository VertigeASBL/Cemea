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
include_spip('inc/filtres');

function instituer_auteur_ici($auteur=array()){
	$instituer_auteur = charger_fonction('instituer_auteur', 'inc');
	return $instituer_auteur($auteur);
}

// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_inscrire_auteur_charger_dist($id_auteur='new', $retour='', $lier_id_article=0, $lier_id_rubrique=0, $config_fonc='auteurs_inscr_config', $row=array(), $hidden=''){
	if (! isset($GLOBALS['visiteur_session']) || $GLOBALS['visiteur_session']['statut'] > '6forum' || $GLOBALS['visiteur_session']['id_auteur'] != $id_auteur)
		$id_auteur = 0;
	$valeurs = formulaires_editer_objet_charger('auteur',$id_auteur,0,0,$retour,$config_fonc,$row,$hidden);
        
        $valeurs['id_auteur'] = (int) $valeurs['id_auteur'];
	if ($lier_id_article)
		$valeurs['lier_id_article'] = (int) $lier_id_article;
        if ($lier_id_rubrique)
		$valeurs['lier_id_rubrique'] = (int) $lier_id_rubrique;
        
        $valeurs['id_meta_actions'] = array();
        if($p=_request('id_meta_actions'))
            $valeurs['id_meta_actions']=$p;
        
        //Define Extra Values by hand
        $valeurs["sante_comportement"]=_request("sante_comportement");
        $valeurs["alimentation"]=_request("alimentation");
        $valeurs["ecole"]=_request("ecole");
        $valeurs["places_voitures"]=_request("places_voitures");
        $valeurs["brevet_animateur"]=_request("brevet_animateur");
        $valeurs["remarques_inscription"]=_request("remarques_inscription");
        
        
        // forcer la prise en compte du post, sans verifier si c'est bien le meme formulaire, c'est trop hasardeux selon le contenud de $row
	$valeurs['_forcer_request'] = true;
	$valeurs['cotepublic'] = 'y';
        
        //print_r($valeurs);
        
	return $valeurs;
}

/**
 * Identifier le formulaire en faisant abstraction des parametres qui ne representent pas l'objet edite
 */
function formulaires_inscrire_auteur_identifier_dist($id_auteur='new', $retour='', $lier_id_article=0, $lier_id_rubrique=0, $config_fonc='auteurs_inscr_config', $row=array(), $hidden=''){
	return serialize(array($id_auteur,$lier_id_article,$row));
}


// Choix par defaut des options de presentation
// http://doc.spip.org/@articles_edit_config
function auteurs_inscr_config($row)
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

function formulaires_inscrire_auteur_verifier_dist($id_auteur='new', $retour='', $lier_id_article=0, $lier_id_rubrique=0, $config_fonc='auteurs_inscr_config', $row=array(), $hidden=''){
	$erreurs = formulaires_editer_objet_verifier('auteur',$id_auteur,array('nom'));

	$auth_methode = sql_getfetsel('source','spip_auteurs','id_auteur='.intval($id_auteur));
	$auth_methode = ($auth_methode ? $auth_methode : 'spip');
	include_spip('inc/auth');
	include_spip('inc/autoriser');

	if ($err = auth_verifier_login($auth_methode, _request('new_login'), $id_auteur)){
		$erreurs['new_login'] = $err;
		$erreurs['message_erreur'] .= $err;
	}
	else {
		// pass trop court ou confirmation non identique
		if ($p = _request('new_pass')) {
			if ($p != _request('new_pass2')) {
				$erreurs['new_pass'] = _T('info_passes_identiques');
				$erreurs['message_erreur'] .= _T('info_passes_identiques');
			}
			elseif ($err = auth_verifier_pass($auth_methode, _request('new_login'),$p, $id_auteur)){
				$erreurs['new_pass'] = $err;
				$erreurs['message_erreur'] .= $err;
			}
		}
	}
	$p = _request('prenom');
//        echo("prenom:".($p=="")."/".is_null($p));
	if (! $p)
		$erreurs['prenom'] = _T('info_obligatoire');
	$p = _request('adresse');
	if (! $p)
		$erreurs['adresse'] = _T('info_obligatoire');
	$p = _request('adresse_no');
	if (! $p)
		$erreurs['adresse_no'] = _T('info_obligatoire');
	$p = _request('codepostal');
	if (! $p)
		$erreurs['codepostal'] = _T('info_obligatoire');
	$p = _request('localite');
	if (! $p)
		$erreurs['localite'] = _T('info_obligatoire');
        $p = _request('alimentation');
//        print_r($p);
//        echo(is_null($p));
        if ($p=="" && !is_null($p))
		$erreurs['alimentation'] = _T('info_obligatoire');
//        echo("alimentation:".($p=="")."/".is_null($p));

	include_spip('inc/filtres'); //--- verifier 1e adr email
	$p = _request('email');
	if (! $p)
		$erreurs['email'] = _T('info_obligatoire');
	else if (! email_valide($p))
		$erreurs['email'] = _T('form_email_non_valide');
        
        $p = _request('id_meta_actions');
//        echo($lier_id_article."/".$lier_id_rubrique);
        if ((! $p) && (!isset($lier_id_article)))
                $erreurs['id_meta_actions'] = _T('info_obligatoire');
        
        //Debugging = Auto error
//        $erreurs['id_meta_actions'] = _T('info_obligatoire');
        
/*	$p = _request('email2'); //--- verifier 2e adr email
	if ($p)
		if (! email_valide($p))
			$erreurs['email2'] = _T('form_email_non_valide'); */

	include_spip('inc/date_gestion'); //--- verifier date naissance
	$err = array();
	$p = _request('date_naissance');
	if (! @verifier_corriger_date_saisie('naissance', false, $err))
		$erreurs['date_naissance'] = $err['date_naissance'];
	if (! $p)
		$erreurs['date_naissance'] = _T('info_obligatoire');
//        echo("date:".$p."/".  _request("date_naissance"));
        if (preg_match('|^\d{2}\D\d{2}\D\d{4}$|', $p)) { //--- renverser la date
		$auteurnaiss = substr($p, 6, 4).'-'.substr($p, 3, 2).'-'.substr($p, 0, 2);
	}
	else
		$auteurnaiss = '0000-00-00';
	set_request('date_naissance', $auteurnaiss);

        
	if (! ($p = _request('localite')) && ($p = _request('otr_localite'))) //--- autre localite
		set_request('localite', $p);

//        print_r($erreurs);
        
	if (count($erreurs) && ! isset($erreurs['message_erreur'])) {
		$erreurs['message_erreur'] = 'Votre saisie contient des erreurs.';
//                print_r($erreurs);
        }
        
        
	return $erreurs;
}

// http://doc.spip.org/@inc_editer_mot_dist
function formulaires_inscrire_auteur_traiter_dist($id_auteur='new', $retour='', $lier_id_article=0, $lier_id_rubrique=0, $config_fonc='auteurs_inscr_config', $row=array(), $hidden=''){
	$addon_recipients=', inscriptions@cemea.be';
//	$addon_recipients='vertige@vertige-ubuntu';
    
        if (_request('saisie_webmestre') OR _request('webmestre'))
		set_request('webmestre',_request('webmestre')?_request('webmestre'):'non');
	$retour = parametre_url($retour, 'email_confirm','');

	$id_article = (int) $lier_id_article;
        $id_rubrique = (int) $lier_id_rubrique;
	$id_auteur = (int) $id_auteur;
	$jeminscris = _request('jeminscris');
	if (_request('typepart') != 'S')
		$jeminscris = false;
	$userlogin = '';
	$statutsuivi = 'T';
        
        //--- Get id_articleS from id_rubrique for meta-actions ---
        // && $id_auteur //Removed condition to avoid problems with new authors
        if($jeminscris && $id_rubrique) {
//            print_r($id_rubrique);
            $id_article=_request('id_meta_actions');
//            print_r($id_article);
        }
        
//        exit;

	//--- Préparer le message
	$message_ok = 'L\'enregistrement de vos données est réussi.';
	if ($jeminscris && $id_article)
		$message_ok .= "<br />Votre demande d'inscription à cette action nous est bien parvenue.";

	//--- action ok ?
	if ($jeminscris && $id_article) {
            if(is_array($id_article)) {
                foreach ($id_article as $key => $value) {
                    
                    $p = sql_fetsel('max_part,titre,date_debut,dates_ra', 'spip_articles', 'id_article='.$value.' AND idact IS NOT NULL AND date_debut>=CURDATE() AND archive_act<>\'Y\' AND (id_trad=0 OR id_trad='.$value.')');
                    $actiontitre_local = isset($p['titre']) ? supprimer_numero($p['titre']) : '?';
                    $datesaction_local = (isset($p['dates_ra'])&&($p['dates_ra']!="")) ? "du ".$p['dates_ra'] : (isset($p['date_debut']) ? "le ".date('d/m/Y', strtotime($p['date_debut'])): '');
                    
//                    echo($value." : ".$p['max_part'].$p['dates_ra'].$p['date_debut']."<br/>");
                    
                    if (isset($p['max_part'])) {
                        $actiontitre.=$actiontitre_local." , ";
                        $datesaction.=$datesaction_local." , ";
                    
                        $p = $p['max_part'];
			$q = sql_getfetsel('COUNT(*)', 'spip_auteurs_articles', 'id_article='.$value.' AND inscrit!=\'\'');
			if (is_numeric($p) && ($p <= $q)) {
				$statutsuivi = 'L';
				$message_ok .= '<br />Vous êtes inscrit(e) en liste d\'attente pour '.$actiontitre_local.'.';
			}
                    } else {
                        $message_ok .= '<br />L\'inscription à '.$actiontitre_local.' n\'est plus possible.';
                        $id_article[$key]=NULL;
                    }
                    
                }
                
                //recreate array, and return if empty
                $id_article=shrink_array($id_article);
                if(!(is_array($id_article)) && ($id_article==0)) {
                    $res['message_erreur'] = 'Désolé, ces actions ne peuvent pas recevoir d\'inscription';
                    $res['editable'] = false;
                }
                
//                print_r($message_ok);
//                print_r($id_article);
//                exit;
                
                
            } else {
                
		$p = sql_fetsel('max_part,titre,date_debut,dates_ra', 'spip_articles', 'id_article='.$id_article.' AND idact IS NOT NULL AND date_debut>=CURDATE() AND archive_act<>\'Y\' AND (id_trad=0 OR id_trad='.$id_article.')');
		$actiontitre = isset($p['titre']) ? supprimer_numero($p['titre']) : '?';
                $datesaction = (isset($p['dates_ra'])&&($p['dates_ra']!="")) ? "du ".$p['dates_ra'] : (isset($p['date_debut']) ? "le ".date('d/m/Y', strtotime($p['date_debut'])): '');
                
//                echo($value." : ".$p['max_part'].$p['dates_ra'].$p['date_debut']."<br/>");
                
		if (isset($p['max_part'])) {
			$p = $p['max_part'];
			$q = sql_getfetsel('COUNT(*)', 'spip_auteurs_articles', 'id_article='.$id_article.' AND inscrit!=\'\'');
			if (is_numeric($p) && ($p <= $q)) {
				$statutsuivi = 'L';
				$message_ok .= '<br />Vous êtes inscrit(e) en liste d\'attente.';
//				$res['message_erreur'] = 'Désolé, l\'inscription à cette action est impossible.<br />Le nombre maximum de participants est atteint.';
//				$res['editable'] = false;
//				return $res; 
			}
//                        $res['message_erreur'] = 'Désolé, cette action ne peut pas recevoir d\'inscription. p='.$p;
//                        return $res;
		}
		else {
			$res['message_erreur'] = 'Désolé, cette action ne peut pas recevoir d\'inscription';
			$res['editable'] = false;
			return $res;
		}
            }
	}
	$auteurnom = _request('nom');
	$auteurprenom = _request('prenom');
	$auteurmail = _request('email');
        
        //Action Fields
        $sante_comportement = _request("sante_comportement");
        $alimentation = _request("alimentation");
        $remarques_inscription = _request("remarques_inscription");
        $ecole = _request("ecole");
                
	$p = $auteurnaiss = _request('date_naissance');
//        echo($p);
//	if (preg_match('|^\d{2}\D\d{2}\D\d{4}$|', $p)) { //--- renverser la date
//		$auteurnaiss = substr($p, 6, 4).'-'.substr($p, 3, 2).'-'.substr($p, 0, 2);
//	}
//	else
//		$auteurnaiss = '0000-00-00';
//	set_request('date_naissance', $auteurnaiss);
        
  
        //Generate login from name+2 first letters of fname+birth
        $auteurlogin=preg_replace('/[^a-z0-9]/', '', strtr(strtolower(utf8_decode($auteurnom)), utf8_decode('àâçèéêëîïôùûü'), 'aaceeeeiiouuu')).
            preg_replace('/[^a-z0-9]/', '', strtr(strtolower(utf8_decode($auteurprenom)), utf8_decode('àâçèéêëîïôùûü'), 'aaceeeeiiouuu'));

	//--- nouvel auteur
	if (! $id_auteur) {
		//--- si deja inscrit, empecher re-post
		$p = _request('typepart');
		if (sql_getfetsel('id_auteur', 'spip_auteurs', 'nom='.sql_quote($auteurnom).' AND prenom='.sql_quote($auteurprenom).' AND date_naissance='.sql_quote($auteurnaiss).' AND typepart='.sql_quote($p))) {
			$res['message_erreur'] = 'Vous semblez être déjà enregistré(e).<br />Veuillez d\'abord <a href="'.generer_url_entite_absolue(6, 'article').'">vous connecter</a>.';
			$res['editable'] = false;
			return $res;
		}

		set_request('statut', '6forum');
		
                //Check if login exists
                if ($found_logins=sql_getfetsel('login', 'spip_auteurs', 'login LIKE "'.$auteurlogin.'%"','login','login DESC')) {
                    $login_index=substr($found_logins, strlen($auteurlogin));
//                    echo($login_index);
                    if($login_index!="") {
                        $next_login_index=number_format($login_index)+1;
                    } else {
                        $next_login_index=1;
                    }
                    $auteurlogin=$auteurlogin.$next_login_index;
		}
//                print_r($found_logins);
//                echo($auteurlogin);
//                exit;
                
                $userlogin = $auteurlogin;
		set_request('login', $userlogin);
                
		$userpasse = substr(md5(time()), 0, 8);
		set_request('pass', $userpasse);
		set_request('idper', date('Y'));
		set_request('archive_per', 'N');
		set_request('adherent', 'N');
		$k = date('Y-m-d');
		set_request('date_creation', $k);
		set_request('date_maj', $k);
		set_request('personne_reference', 'inscr');
		set_request('envoi_diffusion', 'Y');
		
		/* Didier: On teste l'age de la personne */
		/* On a besoin de mes fonctions de gestion pour faire ça */
		include_spip('fonctions_gestion_filtre');
		$age = age(_request('date_naissance'));

		/* Didier: S'il a plus de 16 ans on fait comme d'habiture */
		if ($age > 16) {
			set_request('date_debut_diffusion', $k);
			set_request('date_fin_diffusion', date('Y-m-d', time()+126230400));
		}
		/* S'il a moins de 16 ans */
		else {
			/* Le début de la date_diffusion est à l'age de 16 ans */
			
			/* On calcule le nombre d'année qu'il manque pour avoir 16 ans */
			$annee_a_ajouter = 16 - $age;
			
			/* On créer un Objet DateTime, c'est tellement plus pratique pour manipuler les dates. */
			$date_debut = new DateTime(_request('date_naissance'));

			/* On ajoute le nombre d'année grace à cette objet, la méthode add fait tout pour nous. */
			$date_debut->add(new DateInterval('P'.$annee_a_ajouter.'Y'));

			/* On ajoute a la base de donnée. */
			set_request('date_debut_diffusion', $date_debut->format('Y-m-d'));
			
			/* On ajoute encore 3 ans pour avoir la date de fin de diffusion. */
			$date_debut->add(new DateInterval('P3Y'));			
			/* Go dans la base de donnée. */
			set_request('date_fin_diffusion', $date_debut->format('Y-m-d'));
		}

		$p = _request('diffusion'); //--- envoyer 1 exemplaire par diffusion cochee
		if (is_array($p)) {
			$t2 = array(); reset($p);
			while (list(, $k) = each($p))
				{ $t2[] = $k; $t2[] = 1; }
			set_request('ndiffusion', $t2);
			unset($t2);
		}
	}
	else { //--- auteur connecté, mettre a jour ndiffusion, conserver les nombres d'exemplaires
		$p = _request('diffusion');
		if (! is_array($p))
			$p = array();
		$t0 = _request('ndiffusion');
		$t1 = explode(',', _request('cle_ndiffusion'));
		$t2 = array();
		if (count($t1) && is_array($t0)) {
			reset($t1);
			while (list($k, $q) = each($t1))
				if (in_array($q, $p))
					{ $t2[] = $t1[$k]; $t2[] = is_numeric($t0[$k]) && $t0[$k] > 1 ? (int) $t0[$k] : 1; }
		}
		set_request('ndiffusion', $t2);
		unset($t0, $t1, $t2);

		/* Didier: Mise à jours de Fin diffusion quand on s'inscrit à une action. */
		/* Récupération de la date de début d'activité. */
		$debut_activite = sql_getfetsel('date_debut', 'spip_articles', 'id_article='.sql_quote($id_article));
		/* Mettre à jours la date de Fin diffusion */
		sql_update('spip_auteurs', array('date_fin_diffusion' => 'DATE_ADD(\''.$debut_activite.'\', INTERVAL 3 YEAR)'), 'id_auteur='.sql_quote($id_auteur));
	}
	if (! ($p = _request('localite')) && ($p = _request('otr_localite'))) //--- autre localite
		set_request('localite', $p);
//
	//--- creation ou mise a jour de l'auteur
	$res = formulaires_editer_objet_traiter('auteur',$id_auteur,0,0,$retour,$config_fonc,$row,$hidden);
	if (isset($res['id_auteur']) && $res['id_auteur'] && is_numeric($res['id_auteur']))
		$id_auteur = (int) $res['id_auteur'];

	$envoyer_mail = charger_fonction('envoyer_mail','inc');
//        
        //--- inscrire : relation auteurs_articles
	if ($jeminscris && $id_article && $id_auteur) {
            if(is_array($id_article)) {
                foreach ($id_article as $key => $value) {
                    $p = sql_getfetsel('S.id_auteur', "spip_auteurs AS A,spip_auteurs_articles AS S", "S.id_auteur=$id_auteur AND S.id_article=$value AND A.id_auteur=$id_auteur");
                    
                    if ($p)
			sql_updateq('spip_auteurs_articles', 
                                array('inscrit'=>'Y', 
                                    'statutsuivi'=>$statutsuivi, 
                                    'date_suivi'=>date('Y-m-d'), 
                                    'heure_suivi'=>date('H:i:s'),
                                    'sante_comportement'=>$sante_comportement,
                                    'alimentation'=>$alimentation,
                                    'remarques_inscription'=>$remarques_inscription,
                                    'ecole'=>$ecole
                                    
                                ), 
                                "id_auteur=$id_auteur AND id_article=$value"
                        );
                    else
			sql_insertq('spip_auteurs_articles', 
                                array('id_auteur'=>$id_auteur, 'id_article'=>$value, 'inscrit'=>'Y', 
                                    'statutsuivi'=>$statutsuivi, 'date_suivi'=>date('Y-m-d'), 'heure_suivi'=>date('H:i:s'),
                                    'sante_comportement'=>$sante_comportement,
                                    'alimentation'=>$alimentation,
                                    'remarques_inscription'=>$remarques_inscription,
                                    'ecole'=>$ecole
                                    ));

                }
                
                $list_articles=implode("/", $id_article);
		
                
            } else {
                $p = sql_getfetsel('S.id_auteur', "spip_auteurs AS A,spip_auteurs_articles AS S", "S.id_auteur=$id_auteur AND S.id_article=$id_article AND A.id_auteur=$id_auteur");
                
                if ($p)
                    sql_updateq('spip_auteurs_articles', 
                            array('inscrit'=>'Y', 
                                'statutsuivi'=>$statutsuivi, 
                                'date_suivi'=>date('Y-m-d'), 
                                'heure_suivi'=>date('H:i:s'),
                                'sante_comportement'=>$sante_comportement,
                                'alimentation'=>$alimentation,
                                'remarques_inscription'=>$remarques_inscription,
                                'ecole'=>$ecole

                            ), 
                            "id_auteur=$id_auteur AND id_article=$id_article"
                    );
		else
                    sql_insertq('spip_auteurs_articles', 
                            array('id_auteur'=>$id_auteur, 'id_article'=>$id_article, 'inscrit'=>'Y', 
                                'statutsuivi'=>$statutsuivi, 'date_suivi'=>date('Y-m-d'), 'heure_suivi'=>date('H:i:s'),
                                'sante_comportement'=>$sante_comportement,
                                'alimentation'=>$alimentation,
                                'remarques_inscription'=>$remarques_inscription,
                                'ecole'=>$ecole,
                                'etudiant' => _request('demandeur_emploi')
                                ));
                $list_articles=$id_article;

            }

            $actiontitre=trim($actiontitre," ,");
            $datesaction=trim($datesaction," ,");
            
            //Retrieve other data from form to insert into e-mail
            $auteursexe = _request('codecourtoisie');
            $auteurlieunaiss = _request('lieunaissance');
            $auteuradresse = _request('adresse');
            $auteurno = _request('adresse_no');
            $auteurcp = _request('codepostal');
            $auteurlocalite = _request('localite');
            $auteurtel = _request('tel1');
            $auteurgsm = _request('gsm1');
            $auteurfax = _request('fax1');
            
            $auteuretude = _request('etude_etablissement');
            $auteurprof = _request('profession');
            $auteurdemandeur = _request('demandeur_emploi');
            $auteurassoc = _request('membre_assoc');
            $auteurpratique = _request('pratique');
            $auteurformation = _request('formation');
            $auteurfacture = _request('facture');
            $auteuradrfact = _request('adresse_facturation');
            $auteuralim = _request('alimentation');
            $auteurvoiture = _request('places_voitures');
            $auteurbrevet = _request('brevet_animateur');
            $auteurrem = _request('remarques_inscription');
            
            $p = 'Bonjour,'."\n\n".'Voici une nouvelle inscription :'."\n\n";
            $p .= 'Sexe : '.$auteursexe."\n";
            $p .= 'Prénom : '.$auteurprenom."\n";
            $p .= 'Nom : '.$auteurnom."\n";
            $p .= 'e-mail : '.$auteurmail."\n";
            $p .= 'Date naissance : '.$auteurnaiss."\n";
            $p .= 'Lieu naissance : '.$auteurlieunaiss."\n";
            
            $p .= 'Adresse : '.$auteuradresse."\n";
            $p .= 'No : '.$auteurno."\n";
            $p .= 'Code postal : '.$auteurcp."\n";
            $p .= 'Localité : '.$auteurlocalite."\n";
            $p .= 'Téléphone : '.$auteurtel."\n";
            $p .= 'GSM : '.$auteurgsm."\n";
            $p .= 'Fax : '.$auteurfax."\n";
            
            $p .= "\n*******\n\n";
            
            $p .= 'Études en cours et établissement : '.$auteuretude."\n";
            $p .= 'Profession : '.$auteurprof."\n";
            $p .= 'Demandeur d’emploi : '.$auteurdemandeur."\n";
            $p .= 'Membre d’une association : '.$auteurassoc."\n";
            $p .= 'Pratique : '.$auteurpratique."\n";
            $p .= 'Formations : '.$auteurformation."\n";
            $p .= 'Facture : '.$auteurfacture."\n";
            $p .= 'Adresse de facturation : '.$auteuradrfact."\n";
            $p .= 'Régime alimentaire : '.$auteuralim."\n";
            $p .= 'Places dans votre voiture : '.$auteurvoiture."\n";
            $p .= 'Brevet d’animateur : '.$auteurbrevet."\n";
            $p .= 'Remarques : '.$auteurrem."\n";
            
            $p .= "\n*******\n\n";
            
            $p .= 'id_auteur : '.$id_auteur."\n";
            $p .= 'Statut : '.$statutsuivi."\n";
            $p .= 'Action : '.$actiontitre."\n";
            $p .= 'Dates : '.$datesaction."\n";
            $p .= 'id_article : '.$list_articles."\n";
            $p .= "\n".'-----'."\n";

            $p = $envoyer_mail($GLOBALS['meta']['email_webmaster'].$addon_recipients, $GLOBALS['meta']['nom_site'].' : nouvelle inscription '.$list_articles.'-'.$id_auteur, $p, $GLOBALS['meta']['email_webmaster']);
            if (! $p)
                $message_ok .= '<br />echec envoi inscription';
	}

	if ($id_auteur && $auteurmail) {
		//--- abonnement spip-listes à la liste publique principale
		if ($p = sql_getfetsel('id_liste', 'spip_listes', 'lang='.sql_quote($GLOBALS['spip_lang']), '', 'id_liste', '0,1')) {
			if (! sql_getfetsel('id_auteur', 'spip_auteurs_elargis', 'id_auteur='.$id_auteur))
				sql_insertq('spip_auteurs_elargis', array('id_auteur'=>$id_auteur, 'spip_listes_format'=>'html'));
			$p = (int) $p;
			if (! sql_getfetsel('id_auteur', 'spip_auteurs_listes', 'id_auteur='.$id_auteur.' AND id_liste='.$p))
				sql_insertq('spip_auteurs_listes', array('id_auteur'=>$id_auteur, 'id_liste'=>$p, 'date_inscription'=>date('Y-m-d H:i:s'), 'statut'=>'a_valider', 'format'=>'html'));
		}

		//--- envoyer login et mot de passe
//		if ($jeminscris) {  //Removed condition, to avoid accounts being created without login being sent
		
                
                        $p = 'Bonjour,'."\n\n";
                        if ($jeminscris) {  //Removed condition, to avoid accounts being created without login being sent
                            $mail_title="votre inscription";
                            $p .= "Nous avons bien reçu votre demande d'inscription".($statutsuivi == 'L' ? ' (liste d\'attente)' : '');
                            $p .= " à l'action \" $actiontitre \", ";
                            $p .= "se déroulant $datesaction.";
                            
                        } else {
                            $mail_title="vos données";
                            $p .= "Vos données ont bien été enregistrées.";
                        }
                        $p .= "\n";
                        $p .= "Nous restons à votre disposition pour toute information et vous adressons nos salutations les plus cordiales.";
                        $p .= "\n\n";
                        $p .= "Le Service Inscriptions, 02/543.05.92";
			$p .= "\n\n\n";
                        $p .= 'Nom : '.$auteurnom."\n";
			$p .= 'Prénom : '.$auteurprenom."\n";
			if(isset($actiontitre)) $p .= 'Action : '.$actiontitre."\n";
                        $p .="\n";
			if ($userlogin) {
				$p .= 'Pour pouvoir vous connecter à '.$GLOBALS['meta']['nom_site'].', vous aurez besoin de ces données :'."\n\n";
				$p .= 'Nom d\'utilisateur : '.$userlogin."\n".'Mot de passe : '.$userpasse."\n\n";
				$p .= 'Et l\'adresse pour vous connecter : '.generer_url_entite_absolue(6, 'article')."\n\n";
                                $p .= 'Ces nom d\'utilisateur et mot de passe vous serviront lors de vos prochaines visites sur notre site'."\n\n";
				$p .= 'Vous pouvez changer votre mot de passe ici : ';
			}
			else
				$p .= 'Vous pouvez récupérer votre mot de passe oublié ou changer votre mot de passe ici : ';
			
                        $list_articles=(is_array($id_article))?implode("/", $id_article):$id_article; 
                        //$inscription_titre=($list_articles.""=="0")?"":$list_articles.'-'.$id_auteur;
                        $inscription_titre="";
                        $p .= generer_url_public('spip_pass','lang='.$GLOBALS['spip_lang'],true)."\n\n"; //--- spip.php?page=spip_pass
			$p .= '    Avec les meilleures salutations des '.$GLOBALS['meta']['nom_site']."\n";

//			echo($auteurmail);
                        if ($envoyer_mail($auteurmail, $GLOBALS['meta']['nom_site'].' : '.$mail_title.' '.$inscription_titre, $p, $GLOBALS['meta']['email_webmaster']))
				$message_ok .= '<br />Vous recevrez d’ici peu un email reprenant votre demande.';
			else
				$message_ok .= '<br />L\'envoi de l\'email de confirmation a échoué.';
//		}
	}

	//--- envoyer login et mot de passe
	if ($jeminscris) {
		$message_ok .= "\n".'<br /><br /><a href="';
		$message_ok .= generer_url_action('logout','logout=public&url='.generer_url_entite_absolue(6, 'article').rawurlencode('?url='.rawurlencode(parametre_url(self(),'id_auteur',''))));
		$message_ok .= '">Voulez-vous vous connecter sous un autre identifiant pour inscrire une autre personne ?</a><br />'."\n";
	}

	if (isset($res['message_ok']))
		$res['message_ok'] = $message_ok;
	unset($res['redirect']);
	return $res;
}

//Function to remove NULL elements in array, or make an int if only one element remaining
function shrink_array($array) {
    $new_array=array();
    foreach ($array as $key => $value) {
        if(!is_null($value)) $new_array[]=$value;
    }
    if(count($new_array)==1) $new_array=(int) $new_array[0];
    else if(count($new_array)==0) $new_array=0;
    
    return($new_array);
    
}

?>
