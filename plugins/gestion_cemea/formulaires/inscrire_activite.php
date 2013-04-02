<?php
function formulaires_inscrire_activite_charger_dist() {
	$contexte = array(
		'' => '',
		);
	return $contexte;
}

function formulaires_inscrire_activite_verifier_dist() {
	$id_auteur = _request('id_auteur');
	$id_article = _request('inscrire_action');

	$erreurs = array();

	/*On vérifie que l'auteur n'est pas déjà inscrit a l'activité.*/
	$deja_inscrit = sql_getfetsel('id_article', 'spip_auteurs_articles', 'id_auteur='.sql_quote($id_auteur).' and id_article='.sql_quote($id_article).' and inscrit=\'Y\'');


	if (!empty($deja_inscrit)) {
		$erreurs['message_erreur'] = 'Cette personne est déjà inscrite';
		$erreurs['deja_inscrit'] = 'deja_inscrit';
	}
	return $erreurs;
}

function formulaires_inscrire_activite_traiter_dist() {

	$id_auteur = _request('id_auteur');
	$id_article = _request('inscrire_action');

	/*On ajoute l'entrée dans la base de donnée.*/
	sql_insertq('spip_auteurs_articles', 
		array('id_auteur'=>$id_auteur, 'id_article'=>$id_article, 'inscrit'=>'Y', 
			'statutsuivi'=>'T', 'date_suivi'=>date('Y-m-d'), 'heure_suivi'=>date('H:i:s'),
			));

	/*Mettre à jour la date de fin de diffusion.*/
	include_spip('fonctions_gestion_cemea');
	mettre_a_jour_diffusion($id_auteur);

    // On envoie un message au audministrateur pour signaler la nouvelle inscription
    // On a besoin de swiftMailer
    include_spip('fonctions_gestion_cemea');

    $destinataire = array(
        'Cemea' => $GLOBALS['meta']['email_webmaster'],
        'Inscription Cemea' => 'inscriptions@cemea.be'
        );

    $sujet = $GLOBALS['meta']['nom_site'].' : nouvelle inscription';

    $body = 'Une nouvelle inscription à été faite par un audministrateur, pour la visionner: <a href="'.generer_url_ecrire('suivi', 'id_auteur='.$id_auteur.'&id_article='.$id_article).'">'.generer_url_ecrire('suivi', 'id_auteur='.$id_auteur.'&id_article='.$id_article).'</a>';

    swift_envoyer_mail($destinataire, $sujet, $body);
    
	/*message*/
	return array(
		'editable' => true,
		'message_ok' => 'Cette personne à été inscrite',
        'redirect' => generer_url_ecrire('suivi', '&id_auteur='.$id_auteur.'&id_article='.$id_article)
		);
}
?>