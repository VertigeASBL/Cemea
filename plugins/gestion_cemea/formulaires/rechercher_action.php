<?php
function formulaires_rechercher_action_charger_dist() {
    $contexte = array();
    return $contexte;
}

function formulaires_rechercher_action_verifier_dist() {
    $erreurs = array();
    return $erreurs;
}

function formulaires_rechercher_action_traiter_dist() {

 	// On récupère les données
 	$activite = _request('activite');

	// Si il y a une activité qui est passé.
	if ($activite) {
		// On récupère l'ID
 		$id_activite = sql_getfetsel('id_article', 'spip_articles', 'idact='.sql_quote($activite));

 		// On modifie l'URL
 		$url = 'id_article='.$id_activite;
 	}

    // message
    return array(
        'editable' => true,
        'message_ok' => '',
        'redirect' => generer_url_ecrire('gestion_activite_exec', $url)
    );
}
?>