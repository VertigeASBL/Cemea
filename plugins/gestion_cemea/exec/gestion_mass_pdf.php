<?php
// set_time_limit(0);

if (!defined("_ECRIRE_INC_VERSION")) return;

function exec_gestion_mass_pdf() {

	include_spip('inc/presentation');
	include_spip('gestion_autorisation');

	include_spip('dompdf/dompdf_config.inc');
	include_spip('PDFMerger/PDFMerger');
	include_spip('fonctions_gestion_cemea');

	/* On commence une page Admin SPIP */
	$commencer_page = charger_fonction('commencer_page','inc');
	echo $commencer_page(_T('gestion:tableau_de_bord'));

	/* On vérifie que c'est bien un administrateur qui est connecté */
	if (session_get('statut') != '0minirezo') {
		include_spip('inc/minipres');
		echo minipres('Vous n\'avez pas les autorisations');
	}
	else {
		// On commence le cadre principal
		echo debut_grand_cadre(true);

		// Récupération des variables
		// Le squelette ou l'article qui sera utilisé pour créer le pdf.
		$modele = _request('modele');

		// Si on a un id_article on le récupère
		$id_article = _request('id_article');

		// On récupère les informations de l'action
		$action = sql_fetsel('titre, idact as reference', 'spip_articles', 'id_article='.sql_quote($id_article));

		// le fichier temporaire
		$filename = $action['reference'].'_'.$action['titre'];

		/* On va boucler sur tout les inscrits de l'article */
		$inscrits = sql_allfetsel('a.id_auteur, email, nom, prenom', 'spip_auteurs_articles AS a INNER JOIN spip_auteurs AS b ON a.id_auteur = b.id_auteur', 'a.id_article='.sql_quote($id_article).' AND a.inscrit =\'Y\' AND b.send_email ='.sql_quote('oui'));

		echo '<ul>';
		/* On boucle sur tout les inscrits, pour créer et envoyer les PDF */
		foreach ($inscrits as $key => $value) {

			$html = recuperer_fond(
				'squelettes/generique', 
				array(
					'id_pdf' => _request('modele'),
					'id_activite' => $id_article,
					'id_personne' => $value['id_auteur']
					), 
				array('ajax' => false)
				);

			// Initialisation de la lib et création du PDF
			$dompdf = new DOMPDF();

			// On charge le HTML
			$dompdf->load_html($html);

			// Render !
			$dompdf->render();

			// Récupération du pdf sous forme de flux
			$file = $dompdf->output();

			// On construit le tableau pour envoyer le mail.
			$send = array($value['email'] => $value['nom'].' '.$value['prenom']);
			$sujet = 'A déterminer';
			$body = 'Corps du texte.';
			
			// On envoie le tout à la personne
			/* swift_envoyer_mail($send, $sujet, $body, $file, true, $filename.'.pdf'); */

			/* Messag de confirmation */
			echo '<li>Un email à été envoyé à '.$value['nom'].' '.$value['prenom'].' ('.$value['email'].')</li>';
		}
		echo '</ul>';
		echo fin_grand_cadre(true);
	}

	echo fin_page();
}
?>