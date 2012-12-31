<?php
function formulaires_changer_statut_charger_dist($id_auteur, $id_article, $statut_payement) {
	$contexte = array(
		'statut_payement' => $statut_payement
		);
	return $contexte;
}

function formulaires_changer_statut_verifier_dist($id_auteur, $id_article) {
	$erreurs = array();
	if (!_request('statut_payement')) {
		$erreurs['message_erreur'] = 'Error !';
		$erreurs['NomErreur'] = 'Error !';
	}
	return $erreurs;
}

function formulaires_changer_statut_traiter_dist($id_auteur, $id_article) {

	$statut = _request('statut_payement');

	// Mise à jour de la base de donnée
	sql_update('spip_auteurs_articles', 
		array(
			'statut_payement' => sql_quote($statut), 
			'date_validation_payement' => 'NOW()'), 
		'id_auteur='.sql_quote($id_auteur).' AND id_article='.sql_quote($id_article));
	
	/* Changement automatique de statut. */
	if ($statut == 3 or $statut == 4) {
		// Mise à jour de la base de donnée 
		sql_updateq('spip_auteurs_articles', 
			array('statutsuivi' => 'I'), 
			'id_auteur='.$id_auteur.' AND id_article='.$id_article);
	}

    // message
	return array(
		'editable' => true,
		'message_ok' => '',
		'redirect' => generer_url_ecrire('suivi', 'id_auteur='.$id_auteur.'&id_article='.$id_article)
		);
}
?>