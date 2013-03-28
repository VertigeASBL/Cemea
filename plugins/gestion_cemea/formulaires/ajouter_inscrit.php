<?php
function formulaires_ajouter_inscrit_charger_dist() {

	$contexte = array();
	return $contexte;
}

function formulaires_ajouter_inscrit_verifier_dist() {

	$id_auteur = _request('ajouter_inscrit');
	$id_article = _request('id_article');

	/*On vérifie que l'auteur n'est pas déjà inscrit a l'activité.*/
	$deja_inscrit = sql_getfetsel('id_article', 'spip_auteurs_articles', 'id_auteur='.sql_quote($id_auteur).' and id_article='.sql_quote($id_article).' and inscrit=\'Y\'');

	$erreurs = array();
	if (_request('ajouter_inscrit') == 'nan') {
		$erreurs['message_erreur'] = 'Vous devez choisir une personne.';
	}
	if (!empty($deja_inscrit)) {
		$erreurs['message_erreur'] = 'Cette personne est déjà inscrite.';
	}
	return $erreurs;
}

function formulaires_ajouter_inscrit_traiter_dist() {

	$id_auteur = _request('ajouter_inscrit');
	$id_article = _request('id_article');

	/*On ajoute l'entrée dans la base de donnée.*/
	sql_insertq('spip_auteurs_articles', 
		array('id_auteur'=>$id_auteur, 'id_article'=>$id_article, 'inscrit'=>'Y', 
			'statutsuivi'=>'T', 'date_suivi'=>date('Y-m-d'), 'heure_suivi'=>date('H:i:s'),
			));

	/*Mettre à jour la date de fin de diffusion.*/
	include_spip('fonctions_gestion_cemea');
	mettre_a_jour_diffusion($id_auteur);

	/*message*/
	return array(
		'message_ok' => 'Cette personne à été inscrite.',
		'redirect' => generer_url_ecrire('suivi', '&id_auteur='.$id_auteur.'&id_article='.$id_article)
		);
}
?>