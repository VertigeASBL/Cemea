<?php
// set_time_limit(0);

if (!defined("_ECRIRE_INC_VERSION")) return;

function exec_gestion_dompdf_exec() {

	include_spip('inc/presentation');
	include_spip('inc/documents');
	include_spip('gestion_autorisation');

	include_spip('dompdf/dompdf_config.inc');

	include_spip('PDFMerger/PDFMerger');
	include_spip('fonctions_gestion_cemea');

	/* fonction destinnée à évité de ce retrouvé avec n'importe quoi comme nom de fichier après la purification. */
	function filtre_filename($str) {
		$search = array('é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ï', 'î', 'ù', 'ç', ' ', '‘');
		$replace = array('e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'u', 'c', '_', '');

		return str_replace($search, $replace, $str);
	}

	// Récupération des variables

	// Le squelette ou l'article qui sera utilisé pour créer le pdf.
	$modele = _request('modele');

	/*On récupère les documents PDF du modèle*/
	$documents = sql_allfetsel("D.id_document", "spip_documents AS D LEFT JOIN spip_documents_liens AS T ON T.id_document=D.id_document", "T.id_objet=" . intval($modele) . " AND T.objet=" . sql_quote('article') . " AND extension = ".sql_quote('pdf'));

	/* On va créer un tableau qui contiendra les URL des documents. */
	$pdf_static = array();
	foreach ($documents as $key => $value) {
		$pdf_static[] = generer_url_document_dist($value['id_document']);
	}

	// En cas de certificat !
	$id_certif = _request('id_certif');

	// Si on passe un id_auteur pour avoir les informations de la personne.
	$id_auteur = _request('id_auteur');
	// Si on a un id_article on le récupère
	$id_article = _request('id_article');
	// Si une redirection est demandé
	$redirect = _request('redirect');

	// On envoie pas mail le PDF ?
	$envoyer_par_mail = _request('envoyer_par_mail');

	// Initialisation de la lib et création du PDF
	$dompdf = new DOMPDF();

	// On récupère les informations de l'auteur
	$personne = sql_fetsel('nom, prenom, email', 'spip_auteurs', 'id_auteur='.sql_quote($id_auteur));
	// On récupère les informations de l'action
	$action = sql_fetsel('titre, idact as reference', 'spip_articles', 'id_article='.sql_quote($id_article));

	// On créer le nom du fichier pour vérifier son éventuel existance.
	$filename = $personne['nom'].'_'.$personne['prenom'].'_'.$action['reference'];
	if ($modele == 'liste_participant') {
		$filename = $modele.'_'.$action['reference'];
	}
	elseif (!empty($id_certif)) {
		// Dans le cas d'un certificat, on va rechercher le titre du certificat.
		$certificat = sql_fetsel('titre', 'spip_articles', 'id_article='.sql_quote($id_certif));
		
		$filename = supprimer_numero($action['titre']).'_'.$certificat['titre'];
	}
	else {
		// Dans le cas d'un modèle générique, on va chercher le titre du document
		$document = sql_fetsel('titre', 'spip_articles', 'id_article='.sql_quote($modele));

		$filename .= '_'.supprimer_numero($document['titre']);
	}
	
	// On purifie la variable, par sécurité.
	$filename = filter_var(filtre_filename($filename), FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

	// On vérifie l'éventuel existence du fichier sur le serveur
	// Si le fichier existe déjà on appel l'interface de confimation.
	if (file_exists(sous_repertoire(_DIR_IMG, "gestion").$filename.'.pdf') and !_request('confirmer') and !_request('renvoyer')) {
		include_spip('inc/presentation');
		// Début de la page d'admin
		$commencer_page = charger_fonction('commencer_page','inc');
		echo $commencer_page('Ce fichier existe déjà.');
		
		// On affiche un titre.
		gros_titre(_T('gestion:recreer_fichier'));

		// On demande confirmation 
		echo '<a href="'.self().'&confirmer=1" title="Cofirmer ?">'._T('gestion:confirmer').'</a>';
		echo '<br />';
		echo '<br />';
		echo '<br />';
		// On propose de télécharger le fichier existant
		echo '<a href="'.sous_repertoire(_DIR_IMG, "gestion").$filename.'.pdf'.'" title="télécharger">'._T('gestion:telecharger_existant').'</a>';
		if ($envoyer_par_mail != 0) echo '<br /><a href="'.self().'&renvoyer=1" title="télécharger">'._T('gestion:renvoyer_existant').'</a>';
		
		echo fin_page();
	}
	// Si le fichier n'existe pas ou qu'on a demander de le recréer.
	elseif(!file_exists(sous_repertoire(_DIR_IMG, "gestion").$filename.'.pdf') or _request('confirmer')) {
		
		// On créer la liste des participant a une action
		if ($modele === 'liste_participant') {
			$html = '
			<html>
			<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <style>
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 50px;
                padding-left: 150px;
                padding-right: 150px;
            }
            .pied_page {
                position: absolute;
                bottom: 30;
            }
            </style>
			</head>
			<body>';
			$html .= recuperer_fond('prive/exec/participants_exec', array('pdf' => 1, 'id_article' => $id_article, 'pagination' => 9999999), array('ajax' => false));
			$html .= '</body></html>';
		}
        // Dans le cas d'un listing de payement
        elseif ($modele === 'listing_payement') {
            $html = '
            <html>
            <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <style>
            html, body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 50px;
                padding-left: 150px;
                padding-right: 150px;
            }
            .pied_page {
                position: absolute;
                bottom: 30;
            }
            </style>
            </head>
            <body>';
            $html .= recuperer_fond('prive/exec/liste_payement', array('pdf' => 1, 'id_article' => $id_article, 'pagination' => 9999999), array('ajax' => false));
            $html .= '</body></html>';   
        }
		// Si c'est un certificat
		elseif (!empty($id_certif)) {
			$dompdf->set_paper('A4', 'landscape');
			$html = recuperer_fond(
				'squelettes/certificat', 
				array(
					'id_certif' => $id_certif,
					'id_activite' => $id_article
					), 
				array('ajax' => false)
				);
		}
		// Sinon on récupère un squelette modèle
		else {
			$html = recuperer_fond(
				'squelettes/generique', 
				array(
					'id_pdf' => _request('modele'),
					'id_activite' => $id_article,
					'id_personne' => $id_auteur
					), 
				array('ajax' => false)
				);
		}

        if ($modele != 'etiquette') {
			// On charge le HTML
			$dompdf->load_html($html);
			// Render !
			$dompdf->render();

			// Récupération du pdf sous forme de flux
			$file = $dompdf->output();
			
			// Quoi qu'il arrive en sauvegarde le PDF dans le dossier IMG/gestion
			file_put_contents(sous_repertoire(_DIR_IMG, 'gestion').$filename.'.pdf', $file);

			// On fusion les PDF ensemble
			$merge = new PDFMerger;
			 // Page 1, le PDF qu'on viens de créer avec domPDF 
			$merge->addPDF(sous_repertoire(_DIR_IMG, 'gestion').$filename.'.pdf');
			// On ajoute les PDF lié 
			foreach ($pdf_static as $key => $value) {
				$merge->addPDF($value);
			}
			/*Fuuuuusion !*/
			$file = $merge->merge('string');
			
			/*On sauvegarde le nouveau fichier*/
			file_put_contents(sous_repertoire(_DIR_IMG, 'gestion').$filename.'.pdf', $file);

			/*Si il n'y a pas de mail on télécharge le PDF.*/
			if ($envoyer_par_mail == 0) $merge->merge('browser');
			/*Sinon, on envoie le PDF à la personne via swift*/
			else {
				// On construit le tableau pour envoyer le mail.
				$send = array($personne['email'] => $personne['nom'].' '.$personne['prenom']);
				$sujet = 'A déterminer';
				$body = 'Corps du texte.';
				// On envoie le tout à la personne
				swift_envoyer_mail($send, $sujet, $body, $file, true);
			}
		}
		if ($redirect) header('location: '.urldecode($redirect).'&pdf_envoye=1');
	}
	//Si on a demander de renvoyer le fichier existant.
	elseif (_request('renvoyer')) {
		// On construit le tableau pour envoyer le mail.
		$send = array($personne['email'] => $personne['nom'].' '.$personne['prenom']);
		$sujet = 'A déterminer';
		$body = 'Corps du texte.';
		// On envoie le tout à la personne
		swift_envoyer_mail($send, $sujet, $body, sous_repertoire(_DIR_IMG, "gestion").$filename.'.pdf', false);

		if ($redirect) header('location: '.urldecode($redirect).'&pdf_envoye=1');
	}
}
?>