<?php
// Fonction d'envoie de mail via la lib swift mailer
function swift_envoyer_mail($destinataire, $sujet, $body, $fichier = '', $stream = false) {
    // Inclusion de la librairie swift mail
	include_once('lib_swift/swift_required.php');

    // ON ENVOIE RIEN EN VRAI SINON CA VA PAS LE FAIRE BORDEL !
	$destinataire = array('didier@vertige.org' => 'Debondt Didier');

    // Quelques tests, juste pour être sur
	if (!is_array($destinataire)) echo 'Erreur, le destinnataire dois être un tableau PHP array(adresse => Nom)';
	elseif (!is_string($sujet)) echo 'Erreur, le sujet dois être une chaine de caractère';
	elseif (!is_string($body)) echo 'Erreur, le corps du message dois être une chaine de caractère';
	else {
		
        // Transporter via smtp perso
		$transporter = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl');
        // $transporter->setUsername('phenix0factory@gmail.com');
        // $transporter->setPassword('hmp827W1');
        // Transporter par la fonction mail de PHP
        // $transporter = Swift_MailTransport::newInstance();
		
		$mailer = Swift_Mailer::newInstance($transporter);

		$mail = Swift_Message::newInstance($sujet);
		$mail->SetFrom(array($GLOBALS['meta']['email_webmaster'] => 'Cemea'));
		$mail->setTo($destinataire);
		$mail->setBody($body); 

        // On si on veux envoyer un fichier 
		if (!empty($fichier) and !$stream) $mail->attach(Swift_Attachment::fromPath($fichier));
		elseif (!empty($fichier) and $stream) {
			$mail->attach(Swift_Attachment::newInstance($fichier, 'fichier.pdf', 'application/pdf'));
		}
        // Send the message
        // $result = $mailer->send($mail);
	}
}

// Fonction qui met à jour le statut d'une inscription
function change_statut_inscrit($statut, $id_auteur, $id_article) {
    // Mise à jour de la base de donnée 
	sql_updateq('spip_auteurs_articles', 
		array('statutsuivi' => $statut), 
		'id_auteur='.$id_auteur.' AND id_article='.$id_article);
	
    // On affiche une boite pour confirmer le changement à l'utilisateur.
	echo debut_boite_info();

    // On affiche le message de confirmation.
	echo _T('gestion:changement_statut');

    // Si le statut est une confirmation de l'inscription.
	if ($statut == 'X') {
        // On ajoute la date a laquel le payement à été validé
		sql_update('spip_auteurs_articles', 
			array('date_validation' => 'NOW()'), 
			'id_auteur='.sql_quote($id_auteur).' AND id_article='.sql_quote($id_article));

        // On récupère le mail de l'auteur en question.
		$email = sql_fetsel('email, nom, prenom', 'spip_auteurs', 'id_auteur='.sql_quote($id_auteur));

		$destinataire = array($email['email'] => $email['nom'].' '.$email['prenom']);

        // On récupère le titre de l'activité
		$titre_activite = sql_getfetsel('titre', 'spip_articles', 'id_article='.$id_article);

        // On récupère les informations du message dans la base de donnée des articles
		$data_mail = sql_fetsel('chapo, texte', 'spip_articles', 'id_article=205');

		include_spip('inc/flitres');
        // On place le titre de la confirmation
		$data_mail['texte'] = str_replace('#ACTIVITE', supprimer_numero($titre_activite), $data_mail['texte']);

        // On envoie un mail à la personne pour la prévenir du changement via swiftmail plutôt que envoyer_mail
		swift_envoyer_mail($destinataire, $data_mail['chapo'], $data_mail['texte']);

        // On envoie un mail de confirmation de l'inscription.
		echo '<br />'._T('gestion:confirmation_mail').$email['email'];
	}
    // On ferme la boite.
	echo fin_boite_info();
}

// Fonction qui change le statut d'un payement
function change_statut_payement($statut, $id_auteur, $id_article) {
 //    // Mise à jour de la base de donnée
	// sql_update('spip_auteurs_articles', 
	// 	array(
	// 		'statut_payement' => sql_quote($statut), 
	// 		'date_validation_payement' => 'NOW()'), 
	// 	'id_auteur='.sql_quote($id_auteur).' AND id_article='.sql_quote($id_article));
	
	// if ($statut == 3 or $statut == 4) change_statut_inscrit('I', $id_auteur, $id_article);

 //    // On affiche une boite pour confirmer le changement à l'utilisateur.
	// echo debut_boite_info();
 //    // On affiche le message de confirmation.
	// echo _T('gestion:statut_payement_alert');
 //    // On ferme la boite.
	// echo fin_boite_info();
}

function delete_inscrit($id_auteur) {
	sql_updateq('spip_auteurs', 
		array('statut' => '5poubelle'), 
		'id_auteur='.$id_auteur);

    // On affiche une boite pour confirmer le changement à l'utilisateur.
	echo debut_boite_info();

    // On affiche le message de confirmation.
	echo _T('gestion:auteur_poubelle');

    // On ferme la boite.
	echo fin_boite_info();
}

// Cette fonction inscrit une personne a une action.
function inscrire_action($id_auteur, $id_article) {
	
	$output = '';

    // On vérifie que l'auteur n'est pas déjà inscrit a l'activité.
	$deja_inscrit = sql_getfetsel('id_article', 'spip_auteurs_articles', 'id_auteur='.sql_quote($id_auteur).' and id_article='.sql_quote($id_article).' and inscrit=\'Y\'');

    // Si il n'est pas déjà inscrit
	if (empty($deja_inscrit)) {
        // On ajoute l'entrée dans la base de donnée.
		sql_insertq('spip_auteurs_articles', 
			array('id_auteur'=>$id_auteur, 'id_article'=>$id_article, 'inscrit'=>'Y', 
				'statutsuivi'=>'T', 'date_suivi'=>date('Y-m-d'), 'heure_suivi'=>date('H:i:s'),
				));

        // On affiche une boite pour confirmer le changement à l'utilisateur.
		$output .= debut_boite_info(true);

        // On affiche le message de confirmation.
		$output .=  _T('gestion:confirmer_inscription');

        // On ferme la boite.
		$output .=  fin_boite_info(true);
	}
    // Si il est déjà inscrit, on affiche un message d'erreur
	else {
        // On affiche une boite pour confirmer le changement à l'utilisateur.
		$output .= debut_boite_alerte();

        // On affiche le message de confirmation.
		$output .= _T('gestion:deja_inscrit');

        // On ferme la boite.
		$output .= fin_boite_alerte();
	}

	return $output;
}
?>