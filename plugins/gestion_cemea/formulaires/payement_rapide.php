<?php
function formulaires_payement_rapide_charger_dist($id_auteur, $id_article) {
	// Seul les admin peuvent utiliser ce formulaire
    if (session_get('statut') != '0minirezo')
        return false;
    $contexte = array(
		'' => '',
		);
	return $contexte;
}

function formulaires_payement_rapide_verifier_dist($id_auteur, $id_article) {
	$erreurs = array();
	if (!_request('payementRapide')) {
		$erreurs['message_erreur'] = 'Vous devez entrer un nombre.';
		$erreurs['NomErreur'] = 'Vous devez entrer un nombre.';
	}

	/* gestion des nombres a virgules */
	$payement = str_replace(',', '.', _request('payementRapide'));

	if (!is_numeric($payement)) {
		$erreurs['message_erreur'] = 'Vous devez entrer un nombre.';
		$erreurs['NomErreur'] = 'Vous devez entrer un nombre.';
	}
	return $erreurs;
}

function formulaires_payement_rapide_traiter_dist($id_auteur, $id_article) {

	$payement = str_replace(',', '.', _request('payementRapide'));

	$champs = array(
		'historique_payement' => 'CONCAT_WS(\';\', historique_payement, '.$payement.')'
		);
	sql_update('spip_auteurs_articles', $champs, 'id_article='.sql_quote($id_article).' AND id_auteur='.sql_quote($id_auteur));

    // message
	return array(
		'editable' => true,
		'message_ok' => 'Payement enregistré.',
		'redirect' => generer_url_ecrire('suivi', 'id_auteur='.$id_auteur.'&id_article='.$id_article)
		);
}
?>