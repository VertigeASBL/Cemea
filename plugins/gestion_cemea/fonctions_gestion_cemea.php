<?php
// Fonction d'envoie de mail via la lib swift mailer
function swift_envoyer_mail($destinataire, $sujet, $body, $fichier = '', $stream = false, $filename = 'fichier.pdf') {
    // Inclusion de la librairie swift mail
	include_once('lib_swift/swift_required.php');

    // ON ENVOIE RIEN EN VRAI SINON CA VA PAS LE FAIRE BORDEL !
	$destinataire = array('jpl@cemea.be' => 'Jean-Paul Liens');

    // Quelques tests, juste pour être sur
	if (!is_array($destinataire)) echo 'Erreur, le destinnataire dois être un tableau PHP array(adresse => Nom)';
	elseif (!is_string($sujet)) echo 'Erreur, le sujet dois être une chaine de caractère';
	elseif (!is_string($body)) echo 'Erreur, le corps du message dois être une chaine de caractère';
	else {
        /*Transporter par la fonction mail de PHP*/
        $transporter = Swift_MailTransport::newInstance();
		
		$mailer = Swift_Mailer::newInstance($transporter);

		$mail = Swift_Message::newInstance($sujet);
		$mail->SetFrom(array($GLOBALS['meta']['email_webmaster'] => 'Cemea'));
		$mail->setTo($destinataire);
		$mail->setBody($body, 'text/html'); 

        // On si on veux envoyer un fichier 
		if (!empty($fichier) and !$stream) $mail->attach(Swift_Attachment::fromPath($fichier));
		elseif (!empty($fichier) and $stream) {
			$mail->attach(Swift_Attachment::newInstance($fichier, $filename, 'application/pdf'));
		}
        // Send the message
        $result = $mailer->send($mail);
	}
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

/*
*	Cette fonction ajoute 3 année supplémentaire a un auteur.
*/
function mettre_a_jour_diffusion($id_auteur) {
	/*Il nous faut la date de naissance.*/
	$date_naissance = sql_getfetsel('date_naissance', 'spip_auteurs', 'id_auteur='.sql_quote($id_auteur));

	/*Si l'utilisateur à plus de 16 ans, on dois ajouter 3 ans à aujourd'hui pour mettre à jour la date de fin de diffusion.*/
	if (age($date_naissance) > 16) {
		sql_update('spip_auteurs', array('date_fin_diffusion' => 'DATE_ADD(CURDATE(), INTERVAL 3 YEAR)'), 'id_auteur='.sql_quote($id_auteur));
	}
}

?>