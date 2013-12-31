<?php

// Les champs de la table auteur à modifier.
function get_champ_auteur() {
    return array('nom',
                'prenom',
                'idper',
                'responsable',
                'responsable_lien',
                'etude_etablissement',
                'profession',
                'pratique',
                'formation',
                'membre_assoc',
                'demandeur_emploi'
                );
}

// les champs de la table auteur_articles
function get_champ_inscription() {
    return array(
        'statutsuivi',
        'date_suivi',
        'heure_suivi',
        'alimentation',
        'remarques_inscription',
        'historique_payement',
        'extrait_de_compte',
        'statut_payement',
        'prix_special',
        'facture',
        'adresse_facturation',
        'tableau_exception',
        'sante_comportement',
        'ecole',
        'recus_fiche_medical',
        'brevet_animateur',
        'places_voitures',
        'historique_payement',
        'extrait_de_compte',
        'statut_payement',
        'prix_special',
        'facture',
        'adresse_facturation',
        'tableau_exception'
        );
}


function formulaires_editer_fiche_charger_dist($id_auteur, $id_article) {
    // Contexte du formulaire.
    $contexte = array(
        'id_article' => $id_article,
        'id_auteur' => $id_auteur
        );

    // On va injecter dans le contexte du formulaire les données de la base de donnée.
    $champs_auteur = get_champ_auteur();

    $auteur = sql_allfetsel(
                            $champs_auteur,
                            'spip_auteurs',
                            'id_auteur='.$id_auteur);

    // On va injecter les données de l'inscription
    $champ_inscription = get_champ_inscription();

    $inscription = sql_allfetsel($champ_inscription,
                    'spip_auteurs_articles',
                    'id_auteur='.$id_auteur.'
                        AND id_article='.$id_article);

    // Fusion !
    $contexte = array_merge($contexte, $auteur[0], $inscription[0]);

    // On va combiner la date_suivi et heure_suivi pour qu'elle rentre dans le bon champ.
    $contexte['date_suivit_comb'] = $contexte['date_suivi'].' '.$contexte['heure_suivi'];

    return $contexte;
}

/*
*   Fonction de vérification, cela fonction avec un tableau d'erreur.
*   Le tableau est formater de la sorte:
*   if (!_request('NomErreur')) {
*       $erreurs['message_erreur'] = '';
*       $erreurs['NomErreur'] = '';
*   }
*   Pensez à utiliser _T('info_obligatoire'); pour les éléments obligatoire.
*/
function formulaires_editer_fiche_verifier_dist($id_auteur, $id_article) {
    $erreurs = array();

    if (!_request('nom')) {
       $erreurs['message_erreur'] = 'Erreur dans la saisie';
       $erreurs['nom'] = _T('info_obligatoire');;
    }

    if (!_request('prenom')) {
       $erreurs['message_erreur'] = 'Erreur dans la saisie';
       $erreurs['prenom'] = _T('info_obligatoire');;
    }

    return $erreurs;
}

function formulaires_editer_fiche_traiter_dist($id_auteur, $id_article) {
    // On a pas l'API Objet de SPIP3, du coup on va tout faire à coup d'update.

    // les champs
    $champs_auteur = get_champ_auteur();
    $champ_inscription = get_champ_inscription();

    // On va construire la requête SQL de la table auteur
    $update_auteur = array();
    foreach ($champs_auteur as $champ) {
        $update_auteur[$champ] = _request($champ);
    }

    // On update la table auteur.
    sql_updateq(
        'spip_auteurs',
        $update_auteur,
        'id_auteur='.$id_auteur);

    // On va construire la liste des champs de la table spip_auteurs_articles
    $update_inscription = array();
    foreach ($champ_inscription as $inscription) {
        // On doit faire une exception pour date_suivit_comb
        if ($inscription == 'date_suivi') {
            $date = _request('date_suivit_comb');
            // Il nous faut une date SQL
            include_spip('inc/date');
            $date = explode('/', $date['date']);
            $date = format_mysql_date($date[2], $date[1], $date[0]);

            $update_inscription[$inscription] = $date;
        }
        // Exception pour l'heure_suivi
        elseif ($inscription == 'heure_suivi') {
            $date = _request('date_suivit_comb');
            $update_inscription[$inscription] = $date['heure'];
        }
        // tout les autres cas, on enregistre.
        else {
            $update_inscription[$inscription] = _request($inscription);
        }
    }

    // On update la table des inscriptions
    sql_updateq(
        'spip_auteurs_articles',
        $update_inscription,
        'id_auteur='.$id_auteur.'
        AND id_article='.$id_article);

    // Donnée de retour.
    return array(
            'editable' => true,
            'redirect' => generer_url_ecrire('fiche_inscription', 'id_auteur='.$id_auteur.'&id_article='.$id_article)
    );
}