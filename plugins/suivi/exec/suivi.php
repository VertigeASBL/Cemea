<?php
/* --- richir vertige --- */
// execute automatiquement par le plugin au chargement de la page ?exec=suivi
function exec_suivi() {
	$id_auteur = (int) _request('id_auteur');
	$id_article = (int) _request('id_article');
	$nouv_auteur = (int) _request('nouv_auteur');
	$contexte = array();
	$idper = '';
	$nom = '';
	$prenom = '';
	$statutauteur = '6forum';
	$inscrit = '';
	$statutsuivi = '';
	$date_suivi = '';
	$heure_suivi = '';

	//Addon fields
	$sante_comportement = '';
	$alimentation = '';
	$remarques_inscription = '';
	$ecole = '';
	$places_voitures = '';
	$brevet_animateur = '';
	$historique_payement = '';
	$extrait_de_compte = '';
	$statut_payement = '';
	$tableau_exception = '';
	$recus_fiche_medical = '';


	//----------- lire DB ---------- AND id_secteur=2
	$req = sql_select('id_article,idact,titre', 'spip_articles', "id_article=$id_article");
	if ($data = sql_fetch($req)) {
		$idact = $data['idact'];
		$titre = $data['titre'];
	}
	else
		$id_article = 0;

	$req = sql_select('*', 
                            "spip_auteurs AS A LEFT JOIN spip_auteurs_articles AS S ON S.id_auteur=$id_auteur AND S.id_article=$id_article AND S.inscrit<>''", "A.id_auteur=$id_auteur");
	if ($data = sql_fetch($req)) {
		$idper = $data['idper'];
		$nom = $data['nom'];
		$prenom = $data['prenom'];
		$statutauteur = $data['statut'];
		if ($data['inscrit']) {
			$inscrit = 'Y';
			$statutsuivi = $data['statutsuivi'];
			$date_suivi = $data['date_suivi'];
			$heure_suivi = $data['heure_suivi'];

			$sante_comportement = $data['sante_comportement'];
			$alimentation = $data['alimentation'];
			$remarques_inscription = $data['remarques_inscription'];
			$ecole = $data['ecole'];
			$places_voitures = $data['places_voitures'];
			$brevet_animateur = $data['brevet_animateur'];
			$historique_payement = $data['historique_payement'];
			$extrait_de_compte = $data['extrait_de_compte'];
			$statut_payement = $data['statut_payement'];
			$tableau_exception = $data['tableau_exception'];
			$recus_fiche_medical = $data['recus_fiche_medical'];
			$prix_special = $data['prix_special'];
		}
	}
	else
		$id_auteur = 0;

	//-------- form soumis -----------
	if (_request('okconfirm') && $id_article && ($id_auteur || $nouv_auteur))
		if ($GLOBALS['connect_statut']!='0minirezo' || ! autoriser('modifier', 'article', $id_article))
			$contexte['message_erreur'] = 'Autorisation refusée';
		else {
			$statutsuivi = _request('statutsuivi');
			$date_suivi = _request('date_suivi');
			$heure_suivi = _request('heure_suivi');
                        
                $sante_comportement = _request('sante_comportement');
                $alimentation = _request('alimentation');
                $remarques_inscription = _request('remarques_inscription');
                $ecole = _request('ecole');
                $places_voitures = _request('places_voitures');
                $brevet_animateur = _request('brevet_animateur');
                $extrait_de_compte = _request('extrait_de_compte');
                $historique_payement = str_replace(',', '.', _request('historique_payement'));
                $statut_payement = _request('statut_payement');
                $tableau_exception = _request('tableau_exception');
                $recus_fiche_medical = _request('recus_fiche_medical');
                $prix_special = _request('prix_special');

			include_spip('inc/date_gestion');
			$contexte['erreurs'] = array();
			if (@verifier_corriger_date_saisie('suivi', false, $contexte['erreurs']))
				$date_suivi = substr($date_suivi, 6, 4).'-'.substr($date_suivi, 3, 2).'-'.substr($date_suivi, 0, 2);
			else
				$contexte['message_erreur'] = 'Erreur';

			if (! $contexte['message_erreur'])
				if ($nouv_auteur) {
					$req = sql_select('A.id_auteur,id_article',"spip_auteurs AS A LEFT JOIN spip_auteurs_articles AS S ON S.id_auteur=$nouv_auteur AND S.id_article=$id_article", "A.id_auteur=$nouv_auteur");
					if ($data = sql_fetch($req)) {
						$id_auteur = $data['id_auteur'];
						if (! $data['id_article'])
							sql_insertq('spip_auteurs_articles', array('id_auteur'=>$id_auteur, 'id_article'=>$id_article, 'inscrit'=>'Y'));
					}
					else {
						$contexte['message_erreur'] = 'Erreur';
						$contexte['erreurs']['nouv_auteur'] = 'auteur ID inconnu';
						$id_auteur = 0;
						$inscrit = '';
					}
				}
			if ($id_auteur && ! $contexte['message_erreur']) {
				sql_updateq('spip_auteurs_articles', 
                                        array(
                                        		'inscrit'=>'Y', 
                                        		'statutsuivi'=>$statutsuivi, 
                                        		'date_suivi'=>$date_suivi, 
                                        		'heure_suivi'=>$heure_suivi,
                                               	'sante_comportement'=>$sante_comportement,
                                               	'alimentation'=>$alimentation,
                                               	'remarques_inscription'=>$remarques_inscription,
                                               	'ecole'=>$ecole,
                                               	'brevet_animateur'=>$brevet_animateur,
                                               	'places_voitures'=>$places_voitures,
                                               	'extrait_de_compte' => $extrait_de_compte,
                                               	'historique_payement' => $historique_payement,
                                               	'statut_payement' => $statut_payement,
                                               	'tableau_exception' => $tableau_exception,
                                               	'recus_fiche_medical' => $recus_fiche_medical,
                                               	'prix_special' => $prix_special
                                            ), "id_auteur=$id_auteur AND id_article=$id_article");
                
                // On fait l'update de la date_validation via sql_update plutôt que sql_updateq.
                sql_update('spip_auteurs_articles', array('date_validation' => 'NOW()'), 'id_auteur='.sql_quote($id_auteur).' AND id_article='.sql_quote($id_article));
				$contexte['message_ok'] = 'Ok, l\'inscription est mise à jour';
				$inscrit = 'Y';

                /*
                *   Si c'est une nouvelle inscription faite par un admin, on envoie un mail
                */
                if (_request('new') == 'oui') {
                    $p = 'Bonjour,'."\n\n".'Voici une nouvelle inscription :'."\n\n";
                    $p .= 'Sexe : '.$data['codecourtoisie']."\n";
                    $p .= 'Prénom : '.$prenom."\n";
                    $p .= 'Nom : '.$nom."\n";
                    $p .= 'e-mail : '.$data['email']."\n";
                    $p .= 'Date naissance : '.$data['date_naissance']."\n";
                    $p .= 'Lieu naissance : '.$data['lieunaissance']."\n";
                    
                    $p .= 'Adresse : '.$data['adresse']."\n";
                    $p .= 'No : '.$data['adresse_no']."\n";
                    $p .= 'Code postal : '.$data['codepostal']."\n";
                    $p .= 'Localité : '.$data['localite']."\n";
                    $p .= 'Téléphone : '.$data['tel1']."\n";
                    $p .= 'GSM : '.$data['gsm1']."\n";
                    $p .= 'Fax : '.$data['fax1']."\n";
                    
                    $p .= "\n*******\n\n";
                    
                    $p .= 'Études en cours et établissement : '.$data['etude_etablissement']."\n";
                    $p .= 'Profession : '.$data['profession']."\n";
                    $p .= 'Demandeur d’emploi : '.$data['demandeur_emploi']."\n";
                    $p .= 'Membre d’une association : '.$data['membre_assoc']."\n";
                    $p .= 'Pratique : '.$data['pratique']."\n";
                    $p .= 'Formations : '.$data['formation']."\n";
                    $p .= 'Facture : '.$data['facture']."\n";
                    $p .= 'Adresse de facturation : '.$data['adresse_facturation']."\n";
                    $p .= 'Régime alimentaire : '.$alimentation."\n";
                    $p .= 'Places dans votre voiture : '.$places_voitures."\n";
                    $p .= 'Brevet d’animateur : '.$brevet_animateur."\n";
                    $p .= 'Remarques : '.$remarques_inscription."\n";
                    
                    $p .= "\n*******\n\n";
                    
                    $p .= 'id_auteur : '.$id_auteur."\n";
                    $p .= 'Statut : '.$statutsuivi."\n";
                    $p .= 'Action : '.$actiontitre."\n";
                    $p .= 'Dates : '.$datesaction."\n";
                    $p .= 'id_article : '.$list_articles."\n";
                    $p .= "\n".'-----'."\n";


                    $envoyer_mail = charger_fonction('envoyer_mail','inc');
                    $p = $envoyer_mail(
                                        $GLOBALS['meta']['email_webmaster'].', inscriptions@cemea.be',
                                        $GLOBALS['meta']['nom_site'].' : nouvelle inscription '.$list_articles.'-'.$id_auteur, 
                                        $p, 
                                        $GLOBALS['meta']['email_webmaster']);
                    
                }


				include_spip('inc/headers');
				redirige_par_entete(parametre_url('?exec=articles', 'id_article', $id_article, '&'));
				exit();
			}
		}

	//-------- desinscrire -----------
	if (_request('noinscr') && $id_article && $id_auteur)
		if ($GLOBALS['connect_statut']!='0minirezo' || ! autoriser('modifier', 'article', $id_article))
			$contexte['message_erreur'] = 'Autorisation refusée';
		else {
			if ($statutauteur == '6forum')
				sql_delete('spip_auteurs_articles', "id_auteur=$id_auteur AND id_article=$id_article");
			else
				sql_updateq('spip_auteurs_articles', array('inscrit'=>''), "id_auteur=$id_auteur AND id_article=$id_article");
			$inscrit = '';
			$contexte['message_ok'] = 'Ok, la désinscription est faite';
			include_spip('inc/headers');
			redirige_par_entete(parametre_url('?exec=articles', 'id_article', $id_article, '&'));
			exit();
		}

	//--------- page + formulaire ---------
		$commencer_page = charger_fonction('commencer_page', 'inc');
		echo $commencer_page('Suivi des inscriptions', '', '');

		echo '<br />',gros_titre('Suivi des inscriptions');

		echo debut_gauche('', true);
		echo debut_boite_info(true);
		echo 'Suivi des inscriptions<br /><br />Explications',"\n";
		echo fin_boite_info(true);

		echo debut_droite('', true);

		include_spip('fonctions_gestion_cemea');
		include_spip('prive/gestion_update_db');

		echo debut_cadre_relief('', true, '', '');

		$contexte['id_article'] = $id_article;
		$contexte['id_auteur'] = $id_auteur;
		$contexte['idact'] = $idact;
		$contexte['titre'] = $titre;
		$contexte['idper'] = $idper;
		$contexte['nom'] = $nom;
		$contexte['prenom'] = $prenom;
		$contexte['inscrit'] = $inscrit;
		$contexte['statutsuivi'] = $statutsuivi;
		$contexte['date_suivi'] = $date_suivi;
		$contexte['heure_suivi'] = $heure_suivi;

		$contexte['sante_comportement'] = $sante_comportement;
		$contexte['alimentation'] = $alimentation;
		$contexte['remarques_inscription'] = $remarques_inscription;
		$contexte['ecole'] = $ecole;
		$contexte['places_voitures'] = $places_voitures;
		$contexte['brevet_animateur'] = $brevet_animateur;
		$contexte['extrait_de_compte'] = $extrait_de_compte;
		$contexte['historique_payement'] = str_replace('.', ',', $historique_payement);
		$contexte['statut_payement'] = $statut_payement;
		$contexte['tableau_exception'] = $tableau_exception;
		$contexte['recus_fiche_medical'] = $recus_fiche_medical;
		$contexte['prix_special'] = $prix_special;

		$contexte['editable'] = ' ';

		$milieu = recuperer_fond("prive/form_suivi", $contexte);
		echo pipeline('editer_contenu_objet',array('args'=>array('type'=>'auteurs_article','contexte'=>$contexte),'data'=>$milieu));

		echo fin_cadre_relief(true);
		echo fin_gauche();
		echo fin_page();
}
?>
