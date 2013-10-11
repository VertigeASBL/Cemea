<?php
function formulaires_mail_massif_charger_dist($id_article, $asbl) {
	// Seul les admin peuvent utiliser ce formulaire
    if (session_get('statut') != '0minirezo')
        return false;

    $contexte = array(
		'asbl' => $asbl,
		);

	return $contexte;
}

function formulaires_mail_massif_verifier_dist() {
	$erreurs = array();
	if (!_request('envoyer_mass_pdf')) {
		$erreurs['message_erreur'] = 'Vous devez choisir un docuement PDF.';
		$erreurs['NomErreur'] = '';
	}
	return $erreurs;
}

function formulaires_mail_massif_traiter_dist($id_article) {
	
	$data = _request('envoyer_mass_pdf');

	// message
	return array(
		'editable' => true,
		'message_ok' => 'Le PDF va être envoyé.',
		'redirect' => generer_url_ecrire('gestion_mass_pdf', 'modele='.$data.'&id_article='.$id_article)
		);
}
?>