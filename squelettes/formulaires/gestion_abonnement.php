<?php
/**
 * @since SPIP 2.0
 * @see http://www.spip.net/fr_article3800.html (les formulaires CVT)
 * @see http://www.spip.net/fr_article3796.html (CVT par l'exemple)
 * @package spiplistes
 */
 // $LastChangedRevision: 50641 $
 // $LastChangedBy: root $
 // $LastChangedDate: 2011-08-22 08:00:04 +0200 (Mon, 22 Aug 2011) $

if (!defined("_ECRIRE_INC_VERSION")) return;	#securite

include_spip('inc/spiplistes_api');
include_spip('inc/spiplistes_api_globales');

include_spip('inc/actions');
include_spip('inc/editer');

function formulaires_gestion_abonnement_charger_dist($id_liste=''){
	//spiplistes_debug_log ('formulaires_gestion_abonnement_charger_dist()');

	$d = _request('d');
	$stop = intval(_request('stop'));

	if($auteur = spiplistes_auteur_cookie_ou_session($d))
	{
		$id_auteur = $auteur['id_auteur'];

		$valeurs = formulaires_editer_objet_charger('auteur',$id_auteur,0,0,'','','','');

		$valeurs['id_liste'] = $id_liste;
		$valeurs['d'] = $d;
		$valeurs['editable'] = false;

		$valeurs['id_auteur'] = intval($id_auteur);
		$valeurs['format'] = spiplistes_format_abo_demande($id_auteur);
		$valeurs['editable'] = true;
		
		/**
		 * Recupere la liste des abonnements en cours
		 * pour cet auteur (avec titre des  listes)
		 */
		$mes_abos = spiplistes_abonnements_listes_auteur ($id_auteur, true);
		
		/**
		 * Si c'est un desabonnement a une liste
		 * affiche juste la demande de confirmation
		 */
		if ($stop > 0)
		{
			if ($id_auteur > 0)
			{
				$id_liste = $stop;
				
				// verifier qu'il est encore abonne' a cette liste
				if (
					$mes_abos
					&& isset($mes_abos[$id_liste])
				)
				{
					$row = spiplistes_listes_liste_fetsel ($id_liste, 'titre,descriptif');
					$valeurs['titre_liste'] = $row['titre'];
					$valeurs['descriptif'] = $row['descriptif'];
					$valeurs['stop'] = $stop;
				}
				else
				{
					$valeurs['errormsg'] = _T('spiplistes:pas_abonne_liste');
				}
			}
			else
			{
				unset ($valeurs['d']);
				unset ($valeurs['editable']);
			}
		}
	}
	else
	{
		$valeurs['id_liste'] = $id_liste;
		$valeurs['d'] = $d;
		$valeurs['editable'] = false;

		spiplistes_log ('ERR: UNSUBSCRIBE id_auteur #'.$id_auteur.' id_liste #'.$id_liste);
		/* // on essaie sans spécifier le cookie_oubli, au cas où il est simplement obsolète. */
		/* if ($d) */
		/* 	header('Location: spip.php?page=abonnement'); */
		$valeurs['errormsg'] = 'Le lien que vous avez cliqué est périmé, vous devez vous connecter pour changer votre abonnement.';//_T('spiplistes:action_interdite');
	}
	
	$valeurs['nb_abos'] = count($mes_abos);
	
	$valeurs['cotepublic'] = 'y';
	return ($valeurs);
}

function formulaires_gestion_abonnement_verifier($id_liste='') {
	//spiplistes_debug_log('formulaires_gestion_abonnement_verifier()');
	
	$erreurs = array();

	if (count($erreurs) && ! isset($erreurs['message_erreur']))
		$erreurs['message_erreur'] = 'Votre saisie contient des erreurs.';
	return $erreurs;
}

function formulaires_gestion_abonnement_traiter_dist($id_liste='') {
	//spiplistes_debug_log('formulaires_gestion_abonnement_traiter_dist()');
	
	$d = _request('d');
	$listes = _request('listes');
	$format = _request('suppl_abo');
	$stop = intval(_request('stop'));
	
	if ($auteur = spiplistes_auteur_cookie_ou_session($d))
	{
		$id_auteur = $auteur['id_auteur'];
		$email = $auteur['email'];
		
		// la liste des abonnements en cours
		// pour cet auteur
		$mes_abos = spiplistes_abonnements_listes_auteur ($id_auteur, FALSE);
							  
		// demander de stopper une inscription ?
		if ($stop > 0)
		{
			$id_liste = $stop;
			
			if (isset ($mes_abos[$id_liste]))
			{
				spiplistes_abonnements_auteur_desabonner ($id_auteur, $id_liste);
				$message_ok = _T('spiplistes:abonnement_modifie').'.'
					. '<br />' . PHP_EOL
					. _T('spiplistes:vous_n_etes_plus_abonne_aux_listes')
					;
			}
		}
		else
		{
			$prev_format = spiplistes_format_abo_demande($id_auteur);
		
			$listes_souhaitees =
				(is_array($listes) && count($listes))
				? $listes
				: array()
				;
			
			/**
			 * supprime d'abord tous les abonnements
			 */
			$id_desabos = array();
			foreach ($mes_abos as $id_liste)
			{
				if (!in_array ($id_liste, $listes_souhaitees))
				{
					$id_desabos[] = $id_liste;
				}
			}
			if ($ii = count ($id_desabos))
			{
				if ($ii == 1) { $id_desabos = current ($id_desabos); }
				spiplistes_abonnements_auteur_desabonner ($id_auteur, $id_desabos);
				$message_ok = _T('spiplistes:abonnement_modifie').'.';
			}
			
			/**
			 * Abonne aux listes sélectionnées
			 */
			if (count ($listes_souhaitees))
			{
									  
				// les cles sont les id_listes
				$listes_souhaitees = array_flip ($listes_souhaitees);
				
				spiplistes_abonnements_ajouter ($id_auteur
												, array_keys($listes_souhaitees)
												, 'valide'
												);
				$nb = count ($listes_souhaitees);
				if ($nb >= 1)
				{
					$message_ok .= '<br />' . PHP_EOL;
					$message_ok .=
						($nb > 1)
						? _T('spiplistes:vous_etes_abonne_aux_listes_selectionnees')
						: _T('spiplistes:vous_etes_abonne_a_la_liste_selectionnee')
						;
				}
			}
			else if (count($mes_abos))
			{
				$message_ok .= '<br />' . PHP_EOL
					. _T('spiplistes:vous_n_etes_plus_abonne_aux_listes')
					;
			}
			
			if($format != $prev_format)
			{
				if ($format == 'non')
				{
					if (count ($mes_abos))
					{
						spiplistes_abonnements_auteur_desabonner ($id_auteur, 'toutes');
					}
					
					$message_ok = _T('spiplistes:abonnement_modifie').'.'
						. '<br />' . PHP_EOL
						. _T('spiplistes:vous_n_etes_plus_abonne_aux_listes')
						; 
				}
				else {
					spiplistes_format_abo_modifier($id_auteur, $format);
					$message_ok = _T('spiplistes:abonnement_modifie');
					$message_ok .= '<br />'._T('spiplistes:abonnement_nouveau_format').$format;
				}
			}
			
			spiplistes_auteurs_cookie_oubli_updateq ('', $d, $true);
		
			$contexte = array(
				'editable' => true,
				'message_ok' => $message_ok,
				'format' => $format,
				'id_auteur' => $id_auteur
			);
		}

		//--- champs extra
		$k = _request('diffusion');
		$k = is_array($k) ? implode(',', $k) : '';
		sql_updateq('spip_auteurs', array('diffusion' => $k), 'id_auteur='.$id_auteur);
	}
	
	return ($contexte);
}

/**
 * Recuperer id_auteur, statut, nom et email pour :
 * - l'auteur associé au cookie de l'environnement
 * - ou l'auteur de la session en cours
 * @return array
 */
function spiplistes_auteur_cookie_ou_session ($d)
{
	//spiplistes_debug_log ("spiplistes_auteur_cookie_ou_session($d)");
	$return = array();
	// si pas de cookie on chope l'auteur de la session
	if(empty($d)) {
		if($id_auteur=$GLOBALS['visiteur_session']['id_auteur']) {
			$return['id_auteur'] = intval($id_auteur);
			$row = sql_fetsel(
				'id_auteur,statut,nom,email',
				'spip_auteurs',
				'id_auteur='.sql_quote($id_auteur)
			);
			if($row) {
				$return['id_auteur'] = $row['id_auteur'];
				$return['statut'] = $row['statut'];
				$return['nom'] = $row['nom'];
				$return['email'] = $row['email'];
			}
		}
	}
	// recuperer les donnes de l'auteur associe au cookie
	if(!empty($d))
	{
		$row = sql_fetsel(
			'id_auteur,statut,nom,email',
			'spip_auteurs',
			'cookie_oubli='.sql_quote($d).' AND statut<>'.sql_quote('5poubelle')
		);
		if($row)
		{
			$return['id_auteur'] = $row['id_auteur'];
			$return['statut'] = $row['statut'];
			$return['nom'] = $row['nom'];
			$return['email'] = $row['email'];
		}
		else {
			spiplistes_debug_log ("spiplistes_auteur_cookie_ou_session ni cookie, ni id ?");
		}
	}
	return $return;
}