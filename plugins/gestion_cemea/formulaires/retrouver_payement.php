<?php
function formulaires_retrouver_payement_charger_dist() {
    $contexte = array(
    );
    return $contexte;
}

function formulaires_retrouver_payement_verifier_dist() {
    
    $erreurs = array();
    return $erreurs;
}

function formulaires_retrouver_payement_traiter_dist() {

 	// On récupère les données
 	$participant = _request('participant');
 	$activite = _request('activite');

 	// Si il y a un id_auteur
 	if ($participant) {
 		// On le récupère
 		$id_participant = sql_getfetsel(
 									'id_auteur', 
 									'spip_auteurs', 
 									'CONCAT_WS(\' \', nom, prenom) LIKE '.sql_quote('%'.$participant.'%').'
									OR CONCAT_WS(\' \', prenom, nom) LIKE '.sql_quote('%'.$participant.'%'));
		// On met à jour l'URL
		$url = 'id_participant='.$id_participant;
	}

	// Si il y a une activité qui est passé.
	if ($activite) {
		// On récupère l'ID
 		$id_activite = sql_getfetsel('id_article', 'spip_articles', 'idact='.sql_quote($activite));

 		// On regarde si l'url à déjà été modifiée;
 		if ($url) $url .= '&';
 		$url .= 'id_activite='.$id_activite;
 	}

    // message
    return array(
        'editable' => true,
        'message_ok' => '',
        'redirect' => generer_url_ecrire('gestion_payement_exec', $url)
    );
}
?>