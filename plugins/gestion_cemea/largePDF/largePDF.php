<?php

/**
* Class de création de PDF destinée à générer de fichier de garde taille
* Basé sur DOMPDF pour créer les PDF et PDFMerger pour les fusions de PDF
*/

class largePDF 
{	
	var $filename = 'tmppdf';

	function __construct() {
		/* Inclusion des potes à TOTO */
		include_once('dompdf/dompdf_config.inc.php');
		include_once('PDFMerger/PDFMerger.php');

		/* On inclu la config */
		include_once('config.php');
	}

	/* 
	* Cette fonction ajoute un élément à la liste des fichiers PDF déjà créer 
	*/
	public function addqueue($html) {
		$_SESSION['largePDF_queue'][] = array('html' => $html, 'statut' => 0);
		return true;
	}

	/* 
	* Cete fonction ajoute un fichier à la liste des fichier à fusionner 
	*/
	public function addfile($filename) {
		$_SESSION['largePDF_fileList'][] = $filename;
		return true;
	}

	/* 
	* Cette fonction récupère la queue et traite le premier élément PDF qui n'a pas encore été traité.
	*/
	public function create_pdf() {
		
		// On trouve me premier élément qui n'a pas encore été traité.
		foreach ($_SESSION['largePDF_queue'] as $key => $value) {
			if ($value['statut'] == 0) {
				// On sauvegarde le html 
				$htmlForPDF = $value['html'];
				// On sauvegarde la clée pour pouvoir mettre à jour le statut du PDF
				$htmlKey = $key;
				break;
			}
		}

		// Si cet élément est trouvé en en créer un PDF
		if (!empty($htmlForPDF)) {
			// on lance un DOMPDF pour créer le PDF dans le TMP
			$dompdf = new DOMPDF();

			// On charge le HTML
			$dompdf->load_html($htmlForPDF);
			
			// Render !
			$dompdf->render();

			// Récupération du pdf sous forme de flux
			$file = $dompdf->output();

			// on récupère le chemin du fichier
			$filePath = PDF_TMP_DIR.$this->filename.$this->getNbFile().'.pdf';

			// On sauvegarde le PDF dans le dossier temportaire
			file_put_contents($filePath, $file);
			
			// On met à jours le statut du PDF
			$_SESSION['largePDF_queue'][$key]['statut'] = 1;

			// On ajoute le fichier à la liste des fichiers.
			$this->addfile($filePath);
		}
	}

	/*
	* Cette fonction créer une queue a partir d'un tableau de page html
	*/
	public function addAllQueue($html) {
		if (!is_array($html)) $this->addqueue($html);
		else  {
			foreach ($html as $value) {
				$this->addqueue($value);
			}
		}
	}

	/*
	* Fonction qui affiche le javascript qui sert de créateur de PDF
	*/
	public function display_js() {
		// On récupète le nombre de PDF dans la queue
		$nb_pdf = $this->getNbQueue();

		$javascript = '
			<script>
			jQuery(document).ready(function ($) {
				var i = 1;
				var a = '.$nb_pdf.';
				
				setInterval(function () {
					if (a > i) {
						$.ajax({
							url: "'.PDF_URL_ENGINE.'",
							dataType: "html",

							success: function (html) {
								$("#largePDF_rapport").html(html);
								i++;
							}
						});
					}
				}, 100);
			});
			</script>
		';

		return $javascript;
	}

	/*
	* Function qui renvoie la taille de la queue
	*/
	public function getNbQueue() {
		return count($_SESSION['largePDF_queue']);
	}

	/*
	* function qui renvoie le nombre de fichier créer
	*/
	public function getNbFile() {
		return count($_SESSION['largePDF_fileList']);
	}

	/*
	* Function qui fusionne les PDF
	*/
	public function mergePDF($filename = 'fichier.pdf') {
		// On ouvre PDFMerger parce que ça aide.
		$PDFMerger = new PDFMerger();

		// On boucle sur les fichiers pour les ajouter à la fusion
		foreach ($_SESSION['largePDF_fileList'] as $value) {
			$PDFMerger->addPDF($value);
		}
		$PDFMerger->merge('browser', 'fichier.pdf');
	}

	/*
	* function qui va rediriger sur le fichier une fois créer
	* On le fait avec du javascript parce que l'on sais que cela marche généralement mieux qu'un header, ça évite les problèmes d'envoie.
	*/
	public function sendFile($filename) {
		$javascript = '<script>document.location = '.PDF_TMP_DIR_URL.'/'.$filename.';</script>';

		return $javascript;
	}

	/*
	* Function qui sert à purger les éléments quand on a fini de créer les PDF
	*/
	public function purge() {
		/* Purge de la session */
		unset($_SESSION['largePDF_queue']);
		unset($_SESSION['largePDF_fileList']);

		/* On supprime les fichiers du répertoire tmp */
		foreach (glob(PDF_TMP_DIR.'*.pdf') as $value) {
			unlink($value);
		}
	}
}
?>