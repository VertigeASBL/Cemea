<?php
if (!defined("_ECRIRE_INC_VERSION")) return;

// pouvoir utiliser la class ChampExtra
include_spip('inc/cextras');

// recuperer un tableau des indications fournies pour des selections (enum, radio...)
function cextras_enum_array($enum) {
	$enums = array();
	// 2 possibilites : enum deja un tableau (vient certainement d'un plugin),
	// sinon texte a decouper (vient certainement de interfaces pour champs extra).
	if (is_array($enum)) {
		$enums = $enum;
	} else {
		foreach ($vals = explode("\n", $enum) as $x) {
			list($cle, $desc) = explode(',', trim($x), 2);
			$enums[$cle] = _TT($desc);
		}
	}
	return $enums;	
}

// Creer les item d'un select a partir des enum
function cextras_enum($enum, $val='', $type='valeur', $name='', $class='') {

	// transformer la saisie utilisateur en tableau
	$enums = cextras_enum_array($enum);

	$val_t = explode(',', $val);
	$class = $class ? " class='$class'" : '';
	foreach($enums as $cle => $desc) {
		switch($type) {
			case 'valeur':
				$enums[$cle] =
					($cle == $val
					OR in_array($cle, $val_t))
						? sinon(sinon($desc,$cle),_T('cextras:cextra_par_defaut'))
						: '';
				break;
			case 'option':
				$enums[$cle] = '<option value="'.entites_html($cle).'"'
					. ($cle == $val
						? " selected='selected'"
						: ''
					) .'> '.sinon(sinon($desc,$cle),_T('cextras:cextra_par_defaut'))
					.'</option>'
					."\n";
				break;
			case 'radio':
				$enums[$cle] = "<div class='choix'><input type='radio' name='$name'$class id='${name}_$cle' value=\"".entites_html($cle).'"'
					. ($cle == $val
						? " checked='checked'"
						: ''
					) ."><label for='${name}_$cle'>"
					. sinon(sinon($desc,$cle),_T('cextras:cextra_par_defaut'))
					.'</label></div>'
					."\n";
				break;
			case 'cases':
				$enums[$cle] = "<div class='choix'><input type='checkbox' name='${name}[]'$class id='${name}_$cle' value=\"".entites_html($cle).'"'
					. (in_array($cle, $val_t)
						? " checked='checked'"
						: ''
					) ." /><label for='${name}_$cle'>"
					. sinon(sinon($desc,$cle),_T('cextras:cextra_par_defaut'))
					.'</label></div>'
					."\n";
				break;
		}
	}

	return trim(join("\n", $enums));
}


// Calcule des elements pour le contexte de compilation
// des squelettes de champs extras
// en fonction des parametres donnes dans la classe ChampExtra
function cextras_creer_contexte($c, $contexte_flux, $prefixe='') {
	$contexte = array();
	$nom_champ = $prefixe . $c->champ;
	$contexte['champ_extra'] = $nom_champ;
	$contexte['label_extra'] = _TT($c->label);
	$contexte['precisions_extra'] = _TT($c->precisions);
	if (isset($c->saisie_parametres['explication']) and $c->saisie_parametres['explication'])
		$contexte['precisions_extra'] = _TT($c->saisie_parametres['explication']);
	$contexte['obligatoire_extra'] = $c->obligatoire ? 'obligatoire' : '';
	$contexte['verifier_extra'] = $c->verifier;
	$contexte['verifier_options_extra'] = $c->verifier_options;
	$contexte['valeur_extra'] = $contexte_flux[$nom_champ];
	$contexte['enum_extra'] = $c->enum;
	$contexte['class_extra'] = $c->saisie_parametres['class']; // class CSS sur les champs (input, textarea, ...)
	// ajouter 'erreur_extra' dans le contexte s'il y a une erreur sur le champ
	if (isset($contexte_flux['erreurs'])
	and is_array($contexte_flux['erreurs'])
	and array_key_exists($nom_champ, $contexte_flux['erreurs'])) {
		$contexte['erreur_extra'] = $contexte_flux['erreurs'][$nom_champ];
	}

	return array_merge($contexte_flux, $contexte);
}


// recuperation d'une saisie interne
function ce_calculer_saisie_interne($c, $contexte, $prefixe='') {
	// le contexte possede deja l'entree SQL,
	// calcule par le pipeline formulaire_charger.
	$contexte = cextras_creer_contexte($c, $contexte, $prefixe);

	// calculer le bon squelette et l'ajouter
	if (!find_in_path(
	($f = 'extra-saisies/'.$c->type).'.html')) {
		// si on ne sait pas, on se base sur le contenu
		// pour choisir ligne ou bloc
		$f = strstr($contexte[$prefixe . $c->champ], "\n")
			? 'extra-saisies/bloc'
			: 'extra-saisies/ligne';
	}
	return array($f, $contexte);
}


// en utilisant le plugin "saisies"
function ce_calculer_saisie_externe($c, $contexte, $prefixe='') {
	
	$nom_champ = $prefixe . $c->champ;
	$contexte['nom'] = $nom_champ;
	$contexte['type_saisie'] = $c->type;
	$contexte['label'] = _T($c->label);
	if (isset($contexte[$nom_champ]) and $contexte[$nom_champ]) {
		$contexte['valeur'] = $contexte[$nom_champ];
	}
	// enum -> data
	if ($c->enum) {
		$contexte['datas'] = cextras_enum_array($c->enum);
	}

	$params = $c->saisie_parametres;

	// remapper les precisions
	if ($c->precisions) {
		$params['explication'] = $c->precisions;
	}

	// traductions a faire
	$contexte['explication'] = _T($params['explication']);
	$contexte['attention'] = _T($params['attention']);

	unset (	$params['explication'],
			$params['attention']);

	// tout inserer le reste des champs
	$contexte = array_merge($contexte, $params);

	// lorsqu'on a 'datas', c'est qu'on est dans une liste de choix.
	// Champs Extra les stocke separes par des virgule.
	if ($contexte['datas']) {
		$contexte['valeur'] = explode(',', $contexte['valeur']);
	}

	return array('saisies/_base', $contexte);
}




// recuperer en bdd les valeurs des champs extras
// en une seule requete...

function cextra_quete_valeurs_extras($extras, $type, $id){

	// nom de la table et de la cle primaire
	$table = table_objet_sql($type);
	$_id = id_table_objet($type);

	// liste des champs a recuperer
	$champs = array();
	foreach ($extras as $e) {
		$champs[] = $e->champ;
	}
	if (is_array($res = sql_fetsel($champs, $table, $_id . '=' . sql_quote($id)))) {
		return $res;
	}
	return array();
}

// recuperer tous les extras qui verifient le critere demande :
// l'objet sur lequel s'applique l'extra est comparee a $type
function cextras_get_extras_match($type) {
	static $champs = false;
	if ($champs === false) {
		$champs = pipeline('declarer_champs_extras', array());
	}
	
	$extras = array();
	if ($champs) {
		$type = objet_type(table_objet($type));
		foreach ($champs as $c) {
			// attention aux cas compliques site->syndic !
			if ($type == $c->_type and $c->champ and $c->sql) {
				$extras[] = $c;
			}
		}
	}
	
	return $extras;
}


/**
 * Retourne la description (classe ChampExtra) d'un champ extra d'un objet donné.
 *
 * @param $type : type d'objet (article)
 * @param $champ : nom du champ (puissance)
 * 
 * @return ChampExtra|false
**/
function cextras_get_extra($type, $champ) {
	$extras = cextras_get_extras_match($type);
	foreach ($extras as $c) {
		if ($c->champ == $champ) {
			return $c;
		}
	}
	return false;
}


// ---------- pipelines -----------


// ajouter les champs sur les formulaires CVT editer_xx
function cextras_editer_contenu_objet($flux){
    
    
	/* ----- richir vertige : selection de champs extra selon le contexte
			+ champs extra dans la table auxiliaire auteurs_articles
			plugins/suivi/
			plugins/champs_extras2/inc/cextras.php
			plugins/champs_extras2/inc/cextras_gerer.php
			plugins/champs_extras2/cextras_options.php		----- */
	$taff = array();
        $other_extras = FALSE;
        
	if ($flux['args']['type']=='article') {
		$k = isset($flux['args']['contexte']['id_trad']) ? $flux['args']['contexte']['id_trad'] : 0;
		if ($k == $flux['args']['id'])
			$k = 0;
		if (! $k)
			$k = _request('lier_trad');
		if ($k) //--- article copie
			echo '<div style="padding:4px 15px; color:#cc0000; border:1px solid #cc0000;">Redirection vers l\'article ID ',$k,'.</div>',"\n";
		else if (sql_getfetsel('id_mot', 'spip_mots_rubriques', 'id_mot IN (5,3627) AND id_rubrique='.sql_quote($flux['args']['contexte']['id_parent'])))
			//--- Actions ---
			$taff = array('idact','archive_act','dossier','semestre','max_part','assurance','date_debut','dates_ra','titre_ra','lieu','commanditaire','nb_formateurs_ra','nb_part_ra','par_unite_ra','type_unite_ra','nb_unite_ra','insertion','cocof_rec','subord','dates_scir','titre_scir','nb_part_scir','par_unite_scir','type_unite_scir','nb_unite_scir','date_c','date_maj','type_act','residentiel','centre_org','asbl','fonctionnel','secteur','cursus_formation','diffusion', 'prix', 'prix_etudiant', 'prix_organisme', 'telephone_orga', 'text_presentation', 'heure_formation', 'cloturer', 'heure_debut', 'heure_fin', 'heure_accueil', 'date_fin');
		else if (in_array($flux['args']['contexte']['id_parent'], array(56, 57))) {
			$taff = array('asbl');
		}
	}
	else if ($flux['args']['type']=='auteur') {
		//--- Personnes ---
		switch ($flux['args']['contexte']['form']) {
		case 'editer_auteur': //--- cote admin
			$taff = array('idper','typepart','archive_per','codecourtoisie','prenom','fonction','nom_court_institution','nom_long_institution','description_institution','date_naissance','lieunaissance','adresse','adresse_no','codepostal','localite','tel1','tel2','gsm1','gsm2','fax1','fax2','email2','date_creation','date_maj','statut_form_cemea','statut_anim_cemea','statut_ep','statut_sj','personne_reference','centre_reference','envoi_diffusion','date_debut_diffusion','date_fin_diffusion','diffusion','ndiffusion','adherent', 'demandeur_emploi', 'stage_pratique_animateur', 'stage_pratique_coordinateur_adjoint', 'stage_pratique_coordinateur', 'membre', 'send_email');
			break;
		case 'editer_auteur_hack': //--- cote admin pour le hack // => Vertige Didier
			$taff = array('idper','typepart','archive_per','codecourtoisie','prenom','fonction','nom_court_institution','nom_long_institution','description_institution','date_naissance','lieunaissance','adresse','adresse_no','codepostal','localite','tel1','tel2','gsm1','gsm2','fax1','fax2','email2','date_creation','date_maj','statut_form_cemea','statut_anim_cemea','statut_ep','statut_sj','personne_reference','centre_reference','envoi_diffusion','date_debut_diffusion','date_fin_diffusion','diffusion','ndiffusion','adherent','adherent', 'demandeur_emploi', 'stage_pratique_animateur', 'stage_pratique_coordinateur_adjoint', 'stage_pratique_coordinateur', 'membre', 'send_email');
			break;
		
		case 'select_auteur': //--- selection des destinataires
			$taff = array('diffusion');
			break;
		case 'spip_listes_inscription': case 'gestion_abonnement': //--- abonnement
			$taff = array('diffusion');
			break;
		case 'inscrire_auteur': //--- cote public : modifier un auteur, inscrire a une action
                        //Added extra action fields (only those for all actions)
			$taff = array('alimentation','typepart','codecourtoisie','prenom','fonction','nom_court_institution','nom_long_institution','date_naissance','lieunaissance','adresse','adresse_no','codepostal','localite','tel1','gsm1','fax1','diffusion','ndiffusion', 'send_email'
                            
                            );
                        //,'description_institution'
                        $other_extras=cextras_get_extras_match("auteurs_article");
			break;

		case 'adherer_auteur': //--- adherent
			$taff = array('codecourtoisie','prenom','date_naissance','lieunaissance','adresse','adresse_no','codepostal','localite','tel1','gsm1','diffusion');
			break;
		}
	}
	else if ($flux['args']['type']=='auteurs_article') {
		//--- Suivi des inscriptions ---
		$taff = array('statutsuivi','date_suivi','heure_suivi','alimentation','responsable','responsable_lien','remarques_inscription','sante_comportement','ecole','places_voitures','brevet_animateur','statut_payement', 'historique_payement', 'extrait_de_compte', 'tableau_exception', 'recus_fiche_medical', 'prix_special');
	}
// echo '<pre>'; print_r($flux['args']['contexte']); echo '</pre><hr />',"\n";
	if (! count($taff))
		return $flux;

	// recuperer les champs crees par les plugins
	if ($extras = cextras_get_extras_match($flux['args']['type'])) {
		// les saisies a ajouter seront mises dedans.
		$inserer_saisie = '';

		// Il peut arriver qu'un prefixe soit appliqué sur les noms de champs de formulaire
		// (mais pas en base) ceci pour permettre d'inserer les champs de formulaire d'un objet dans
		// le formulaire d'un autre objet, en prefixant tous ses champs, par exemple
		// pour spip_auteurs_elargis et spip_auteurs. Dans ce cas il ne pourra pas y avoir
		// conflits si spip_auteurs a un champ extra 'nom' et spip_auteurs_elargis aussi.
		// La contrainte est que le formulaire inseré doit appeler le pipeline 'editer_contenu_objet'
		// en lui indiquant quel est le prefixe utilisé d'une part, et d'autre part
		// il faut qu'il s'occupe lui même d'ajouter les données via
		// le pipeline formulaire_charger de spip_auteurs (pour cet exemple) avec les bons prefixe.
		if (isset($flux['args']['prefixe_champs_extras']) and $prefixe = $flux['args']['prefixe_champs_extras']) {
			$inserer_saisie .= "<input type='hidden' name='prefixe_champs_extras_" . $flux['args']['type'] . "' value='$prefixe' />\n";
		} else {
			$prefixe = '';
		}
		
                //Get id_action or meta_action
                $id_action=_request("idaction");
                $id_meta_action_rub=_request("idrubrique");
                
                //Check if action is animation
                $add_anim_fields=FALSE;  //Add fields for Animation
                if($id_action) {
                    //Check if Action is in "animation" (ID 27) section and add other extra fields
                    $req = sql_select('id_rubrique', 'spip_articles', "id_article=".$id_action);
                    if ($data = sql_fetch($req)) {
                        $id_parent_section=$data['id_rubrique'];
                        if($id_parent_section=="27") {
                            $add_anim_fields=TRUE;
                        } else {
                            //check one level higher, for single actions contained in meta-actions sections
                            $req = sql_select('id_parent', 'spip_rubriques', "id_rubrique=".$id_parent_section);
                            if ($data = sql_fetch($req)) {
                                $id_parent_section=$data['id_parent'];
                                if($id_parent_section=="27")
                                    $add_anim_fields=TRUE;
                            }
                        }
                    }
                }
                if($id_meta_action_rub) {
                    $req = sql_select('id_parent', 'spip_rubriques', "id_rubrique=".$id_meta_action_rub);
                    if ($data = sql_fetch($req)) {
                        $id_parent_section=$data['id_parent'];
                        if($id_parent_section=="27") {
                            $add_anim_fields=TRUE;
                        }
                    }
                }

                
                //Add extras from Author if in Formation action
                if($id_action) {
                    if($req = sql_getfetsel('id_groupe', array('spip_mots','spip_mots_articles'), "spip_mots.id_mot = spip_mots_articles.id_mot AND spip_mots_articles.id_article=".$id_action." AND spip_mots.id_groupe = 14")) {
//                        echo("req = $req");
                        array_push($taff,
                                'places_voitures','brevet_animateur','remarques_inscription',
                                'etude_etablissement','profession','demandeur_emploi','membre_assoc','pratique',
                                'formation','facture','adresse_facturation');
                    }
                }
                
                //Add extras from Author specific to Animations
                if($add_anim_fields) {
                    array_push($taff,
                                'responsable','responsable_lien');
                }
                //print_r($extras);

		foreach ($extras as $c) {
			if (! in_array($c->champ, $taff)) //--- richir vertige
				continue;

			// on affiche seulement les champs dont la saisie est autorisee
			$type = $c->_type . '_' . $c->champ;
			include_spip('inc/autoriser');
			if (autoriser('modifierextra', $type, $flux['args']['id'], '', array(
				'type' => $flux['args']['type'],
				'id_objet' => $flux['args']['id'],
				'contexte' => $flux['args']['contexte'])))
			{

				if ($c->saisie_externe) {
					list($f, $contexte) = ce_calculer_saisie_externe($c, $flux['args']['contexte'], $prefixe);
				} else {
					list($f, $contexte) = ce_calculer_saisie_interne($c, $flux['args']['contexte'], $prefixe);
				}
				// Si un prefixe de champ est demande par le pipeline
				// par exemple pour afficher et completer un objet different dans
				// le formulaire d'un premier objet (ex: spip_auteurs_etendus et spip_auteurs)
				// l'indiquer !
				$saisie = recuperer_fond($f, $contexte);

				// Signaler a cextras_pre_edition que le champ est edite
				// (cas des checkbox multiples quand on renvoie vide
				//  qui n'envoient rien de rien, meme pas un array vide)
				$saisie .= '<input type="hidden" name="cextra_' . $prefixe . $c->champ.'" value="1" />';
                                
                                //Add non-extra fields where needed
//                                echo($f."|".$c->champ."<br/>");
//                                print_r($flux['args']['contexte']);
//                                print_r($contexte);
                                
                                //Add name and e-mail after first name, only in front office
                                if(($c->champ=="prenom") && (!isset($_GET["exec"]))){
//                                    echo "ajouter nom ".$f;
//                                    print_r($contexte);
                                    
                                    //Name
                                    $cbis=array(
                                        "champ"=>"nom",
                                        "label"=>"Nom",
                                        "obligatoire"=>1
                                    );
                                    $cbis=cemea_creer_contexte($cbis,$contexte);
                                    
                                    $saisie_bis=recuperer_fond($f,$cbis);
                                    $saisie_bis.= '<input type="hidden" name="cextra_nom" value="1" />';
                                    $saisie.=$saisie_bis;
                                    
                                    //E-mail
                                    $cbis=array(
                                        "champ"=>"email",
                                        "label"=>"entree_adresse_email",
                                        "obligatoire"=>1
                                    );
                                    $cbis=cemea_creer_contexte($cbis,$contexte);
//                                    print_r($cbis);
                                    
                                    $saisie_bis=recuperer_fond($f,$cbis);
                                    $saisie_bis.= '<input type="hidden" name="cextra_email" value="1" />';
                                    $saisie.=$saisie_bis;
                                }

				// ajouter la saisie.
				$inserer_saisie .= $saisie;
			}
		}
                
                //Add extras from action (auteur_article) if needed
                if($other_extras && (($id_action) || ($id_meta_action_rub))) {
                    
                    
                    //For single Actions
                    if($id_action) {
                        
//                        if($add_anim_fields) { //Removed condition because alimentation is always required
                            //Get values from DB
                            $req = sql_select('sante_comportement,ecole,remarques_inscription,alimentation,places_voitures,brevet_animateur', 'spip_auteurs_articles', "id_article=".$id_action." AND id_auteur=".$flux['args']['id'].' AND inscrit != \'\'');
                            if ($data = sql_fetch($req)) {
                                $flux['args']['contexte']['sante_comportement'] = $data['sante_comportement'];
                                $flux['args']['contexte']['ecole'] = $data['ecole'];
                                $flux['args']['contexte']['remarques_inscription'] = $data['remarques_inscription'];
                                $flux['args']['contexte']['alimentation'] = $data['alimentation'];
                                $flux['args']['contexte']['places_voitures'] = $data['places_voitures'];
                                $flux['args']['contexte']['brevet_animateur'] = $data['brevet_animateur'];

                            }
//                            echo("add fields id_article $id_action");
//                            print_r($flux['args']['contexte']);
//                        }
                    }
                    
                    //For meta-actions
                    if($id_meta_action_rub) {
                        
//                        if($add_anim_fields) { //Removed condition because alimentation is always required
                            //Get values from DB
                            $req = sql_select('sante_comportement,ecole,remarques_inscription,alimentation,places_voitures,brevet_animateur', 'spip_auteurs_articles', "(id_article IN (SELECT id_article FROM spip_articles WHERE id_rubrique=".$id_meta_action_rub.") AND id_auteur=".$flux['args']['id'].' AND inscrit != \'\')');
                            if ($data = sql_fetch($req)) {
                                $flux['args']['contexte']['sante_comportement'] = $data['sante_comportement'];
                                $flux['args']['contexte']['ecole'] = $data['ecole'];
                                $flux['args']['contexte']['remarques_inscription'] = $data['remarques_inscription'];
                                $flux['args']['contexte']['alimentation'] = $data['alimentation'];
                                $flux['args']['contexte']['places_voitures'] = $data['places_voitures'];
                                $flux['args']['contexte']['brevet_animateur'] = $data['brevet_animateur'];

                            }
//                        }
                    }
                    
//                    echo("add_fields: $add_anim_fields");
                    if($add_anim_fields) {
                        array_push($taff,
                                    'ecole','sante_comportement','remarques_inscription');
                    }
                    
                    
                    
//                    print_r($flux['args']);
                    
                    foreach($other_extras as $c) {
                        if (! in_array($c->champ, $taff)) //--- richir vertige
                            continue;
                        
                        // on affiche seulement les champs dont la saisie est autorisee
			$type = $c->_type . '_' . $c->champ;
			include_spip('inc/autoriser');
			if (autoriser('modifierextra', $type, $flux['args']['id'], '', array(
				'type' => $flux['args']['type'],
				'id_objet' => $flux['args']['id'],
				'contexte' => $flux['args']['contexte'])))
			{
                            if ($c->saisie_externe) {
                                    list($f, $contexte) = ce_calculer_saisie_externe($c, $flux['args']['contexte'], $prefixe);
                            } else {
                                    list($f, $contexte) = ce_calculer_saisie_interne($c, $flux['args']['contexte'], $prefixe);
                            }
                            // Si un prefixe de champ est demande par le pipeline
                            // par exemple pour afficher et completer un objet different dans
                            // le formulaire d'un premier objet (ex: spip_auteurs_etendus et spip_auteurs)
                            // l'indiquer !
                            
//                            print_r($contexte);
                            
                            $saisie = recuperer_fond($f, $contexte);

                            // Signaler a cextras_pre_edition que le champ est edite
                            // (cas des checkbox multiples quand on renvoie vide
                            //  qui n'envoient rien de rien, meme pas un array vide)
                            $saisie .= '<input type="hidden" name="cextra_' . $prefixe . $c->champ.'" value="1" />';

                            // ajouter la saisie.
                            $inserer_saisie .= $saisie;
                        }
                        
                            

                    }
                }

        /*
		*	Didier: J'ai aucune idée de pourquoi ou comment sont générer les champs extras.
		*	Par contre je sais que tout le html fini dans $inserer_saisie. Du coup on installer phpquery et on manipule le html !
        */
		/*On appel phpQuery*/
		include_once('phpQuery.php');
		/*Initialisation*/
		$doc = phpQuery::newDocument($inserer_saisie);
		/*On récupère le champ diffusion avec un séléecteur*/
		$diffusion = pq('.editer_diffusion');
		/*On retir le champs diffusion*/
		pq('.editer_diffusion')->remove();

		/*On modifie la saisie pour quelle s'affiche correctement, en placant diffusion à la fin.*/
		$inserer_saisie = $doc->getDocument().'<li class="editer_diffusion">'.$diffusion->html().'</li>';

		// inserer les differentes saisies entre <ul>
		if ($inserer_saisie) {
			$flux['data'] = preg_replace('%(<!--extra-->)%is', '<ul>'.$inserer_saisie.'</ul>'."\n".'$1', $flux['data']);
		}
	}

	return $flux;
}

function cemea_creer_contexte($c, $contexte_flux, $prefixe='') {
        $contexte = array();
	$nom_champ = $prefixe . $c['champ'];
	$contexte['champ_extra'] = $nom_champ;
	$contexte['label_extra'] = _TT($c['label']);
//	$contexte['precisions_extra'] = _TT($c['precisions']);
//	if (isset($c->saisie_parametres['explication']) and $c->saisie_parametres['explication'])
//		$contexte['precisions_extra'] = _TT($c->saisie_parametres['explication']);
	$contexte['obligatoire_extra'] = $c['obligatoire'] ? 'obligatoire' : '';
//	$contexte['verifier_extra'] = $c->verifier;
//	$contexte['verifier_options_extra'] = $c->verifier_options;
	$contexte['valeur_extra'] = $contexte_flux[$nom_champ];
//	$contexte['enum_extra'] = $c->enum;
//	$contexte['class_extra'] = $c->saisie_parametres['class']; // class CSS sur les champs (input, textarea, ...)
	// ajouter 'erreur_extra' dans le contexte s'il y a une erreur sur le champ
	if (isset($contexte_flux['erreurs'])
	and is_array($contexte_flux['erreurs'])
	and array_key_exists($nom_champ, $contexte_flux['erreurs'])) {
		$contexte['erreur_extra'] = $contexte_flux['erreurs'][$nom_champ];
	}
        
//        print_r($contexte["erreur_extra"]);
//        print_r($contexte_flux);

	return array_merge($contexte_flux, $contexte);
}


// ajouter les champs extras soumis par les formulaire CVT editer_xx
function cextras_pre_edition($flux){

	// recuperer les champs crees par les plugins
	if ($extras = cextras_get_extras_match($flux['args']['table'])) {
		// recherchons un eventuel prefixe utilise pour poster les champs
		$type = objet_type(table_objet($flux['args']['table']));
		$prefixe = _request('prefixe_champs_extras_' . $type);
		if (!$prefixe) {
			$prefixe = '';
		}
		foreach ($extras as $c) {
			if (_request('cextra_' . $prefixe . $c->champ)) {
				$extra = _request($prefixe . $c->champ);
				if (is_array($extra))
					$extra = join(',',$extra);
				$flux['data'][$c->champ] = corriger_caracteres($extra);
			}
		}
	}

	return $flux;
}


// ajouter le champ extra sur la visualisation de l'objet
function cextras_afficher_contenu_objet($flux){
	if ($flux['args']['type']=='article') {
		$k = sql_getfetsel('id_trad', 'spip_articles', 'id_article='.sql_quote($flux['args']['id_objet']));
		if ($k && $k != $flux['args']['id_objet']) { //--- richir vertige
			$flux['data'] .= '<span style="color:#cc0000;">Redirection vers l\'article ID '.$k.'.</span>'."\n";
			return $flux;
		}
	}
	// recuperer les champs crees par les plugins
	if ($extras = cextras_get_extras_match($flux['args']['type'])) {

		$contexte = cextra_quete_valeurs_extras($extras, $flux['args']['type'], $flux['args']['id_objet']);
		$contexte = array_merge($flux['args']['contexte'], $contexte);
		foreach($extras as $c) {

			// on affiche seulement les champs dont la vue est autorisee
			$type = $c->_type . '_' . $c->champ;
			include_spip('inc/autoriser');
			if (autoriser('voirextra', $type, $flux['args']['id_objet'], '', array(
				'type' => $flux['args']['type'],
				'id_objet' => $flux['args']['id_objet'],
				'contexte' => $contexte)))
			{

				$contexte = cextras_creer_contexte($c, $contexte);
				$saisie_externe = false;
				
				// calculer le bon squelette et l'ajouter
				if($c->saisie_externe && find_in_path(
				($f = 'saisies-vues/'.$c->type).'.html')){
					$contexte['valeur'] = $contexte[$c->champ];
					// ajouter les listes d'éléments possibles
					if (isset($c->saisie_parametres['datas']) and $c->saisie_parametres['datas']) {
						$contexte['datas'] = $c->saisie_parametres['datas'];
					// sinon peut provenir du plugin d'interface, directement dans enum.
					} elseif ($c->enum) {
						$contexte['datas'] = cextras_enum_array($c->enum);
					}

					// lorsqu'on a 'datas', c'est qu'on est dans une liste de choix.
					// Champs Extra les stocke separes par des virgule.
					if ($contexte['datas']) {
						$contexte['valeur'] = explode(',', $contexte['valeur']);
					}
						
					$saisie_externe = true;
				}
				else if (!find_in_path(
				($f = 'extra-vues/'.$c->type).'.html')) {
					// si on ne sait pas, on se base sur le contenu
					// pour choisir ligne ou bloc
					$f = strstr($contexte[$c->champ], "\n")
						? 'extra-vues/bloc'
						: 'extra-vues/ligne';
				}
				$extra = recuperer_fond($f, $contexte);
				if($saisie_externe){
					$extra = '<div class="'.$c->champ.'"><strong>'._T($c->label).'</strong>'.$extra.'</div>';
				}
//----- richir vertige + adaptations dans "extra-vues/" et dans "extra-saisies/"
				if ($extra)
					$flux['data'] .= "\n".$extra;
			}
		}
	}
	return $flux;
}

// verification de la validite des champs extras
function cextras_formulaire_verifier($flux){
	// recuperer les champs crees par les plugins
	$form = $flux['args']['form'];
	// formulaire d'edition ?
	if (strncmp($form, 'editer_', 7) === 0) {
		$type = substr($form, 7);
		
		// des champs extras correspondent ?
		if ($extras = cextras_get_extras_match($type)) {

			// Il peut arriver qu'un prefixe soit appliqué sur les noms de champs de formulaire
			// La contrainte est que le formulaire inseré doit appeler le pipeline 'formulaire_verifier'
			// avec le bon type d'objet (en indiquant le prefixe) et concaténer ainsi les résultats
			if (isset($flux['args']['prefixe_champs_extras'])
			and $prefixe = $flux['args']['prefixe_champs_extras']) {
			} else {
				$prefixe = '';
			}
					
			include_spip('inc/autoriser');

			// si le plugin "verifier" est actif, on tentera dans
			// la verification de lancer la fonction de verification
			// demandee par le champ, si definie dans sa description
			// 'verifier' (et 'verifier_options')
			$verifier = charger_fonction('verifier', 'inc', true);
			
			//--- richir vertige
			include_spip('inc/date_gestion');
			$terr = array();

			foreach ($extras as $c) {
				// si on est autorise a modifier le champ
				// et que le champ est obligatoire
				// alors on renvoie une erreur.
				// Mais : ne pas renvoyer d'erreur si le champ est
				// obligatoire, mais qu'il n'est pas visible dans le formulaire
				// (si affiche uniquement pour la rubrique XX par exemple).
				// On teste seulement les champs dont la modification est autorisee
				$type = $c->_type . '_' . $c->champ;
				$id_objet = $flux['args']['args'][0]; // ? vraiment toujours ?

				//--- richir vertige : verifier les dates
				if (substr($c->champ, 0, 5) == 'date_') {
					$suf = substr($c->champ, 5);
					$chn = _request($c->champ);
					if (! $chn || $suf == 'maj') {
						$chn = date('d/m/Y');
						set_request($c->champ, $chn);
					}
					if (@verifier_corriger_date_saisie($suf, false, $terr))
						set_request($c->champ, substr($chn, 6, 4).'-'.substr($chn, 3, 2).'-'.substr($chn, 0, 2));
					else
						$flux['data'][$prefixe . $c->champ] = $terr[$c->champ];
				}
				//--- richir vertige : champ autre
				if (isset($_POST['otr_'.$c->champ]))
					if (($chn = _request('otr_'.$c->champ)) && _request($c->champ) == '')
						set_request($c->champ, $chn);

				//--- richir vertige : liste de paires (cle + valeur numerique), si diffusion alors au moins 1
				if ($c->champ == 'ndiffusion') {
					$t0 = _request('ndiffusion');
					$t1 = explode(',', _request('cle_ndiffusion'));
					if (count($t1) && is_array($t0)) {
						$t3 = _request('diffusion');
						if (! is_array($t3))
							$t3 = array();
						$t2 = array(); reset($t1);
						while (list($k) = each($t1))
							if (is_numeric($t0[$k]) && $t0[$k])
								{ $t2[] = $t1[$k]; $t2[] = (int) $t0[$k]; }
							else if (in_array($t1[$k], $t3))
								{ $t2[] = $t1[$k]; $t2[] = '1'; }
						set_request('ndiffusion', $t2);
					}
					unset($t0, $t1, $t2, $t3);
				}
				//--- richir vertige : email valide ?
				if ($c->champ == 'email2')
					if ($chn = _request('email2')) {
						include_spip('inc/filtres');
						if (! email_valide($chn))
							$flux['data'][$prefixe . $c->champ] = _T('form_email_non_valide');
					}

				// l'autorisation n'a pas de contexte a transmettre
				// comme dans l'autre appel (cextras_afficher_contenu_objet())
				// du coup, on risque de se retrouver parfois avec des
				// resultats differents... Il faudra surveiller.
				if (autoriser('modifierextra', $type, $id_objet, '', array(
					'type' => $c->_type,
					'id_objet' => $id_objet)))
				{	
					if ($c->obligatoire AND !_request($prefixe . $c->champ)) {
						$flux['data'][$prefixe . $c->champ] = _T('info_obligatoire');
					} elseif ($c->verifier AND $verifier) {
						if ($erreur = $verifier(_request($prefixe . $c->champ), $c->verifier, $c->verifier_options)) {
							$flux['data'][$prefixe . $c->champ] = $erreur;
						}
					}
				}
			}
		}
	}
	return $flux;
}


// prendre en compte les champs extras 2 dans les recherches
// pour les champs qui le demandent
function cextras_rechercher_liste_des_champs($tables){
	if ($champs = pipeline('declarer_champs_extras', array())) {
		$t = array();
		// trouver les tables/champs a rechercher
		foreach ($champs as $c) {
			if ($c->rechercher) {
				// priorite 2 par defaut, sinon sa valeur.
				// Plus le chiffre est grand, plus les points de recherche
				// attribues pour ce champ seront eleves
				if ($c->rechercher === true
				OR  $c->rechercher === 'oui'
				OR  $c->rechercher === 'on') {
					$priorite = 2;
				} else {
					$priorite = intval($c->rechercher);
				}
				if ($priorite) {
					$t[$c->_type][$c->champ] = $priorite;
				}
			}
		}
		// les ajouter
		if ($t) {
			$tables = array_merge_recursive($tables, $t);
		}
	}
	return $tables;
}
?>