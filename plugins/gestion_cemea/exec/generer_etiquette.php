<?php 
session_start();

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('largePDF/largePDF');
include_spip('inc/presentation');
include_spip('fonctions_gestion_cemea');

function exec_generer_etiquette() {
	$pdf = new largePDF();
	
	if ($_GET['merge'] == 1) {
		$pdf->mergePDF();
	}
	else {
		$pdf->purge();
		// Début de la page d'admin
		$commencer_page = charger_fonction('commencer_page','inc');
		echo $commencer_page('Création des étiquettes');
		
		// On affiche un titre.	
		gros_titre('Création des étiquettes.');

		// On commence le cadre principal
		echo debut_grand_cadre(true);

		// Les fonctions à phil
		$lilang = 'fr';
		include_spip('plugins/listeselect/exec/listewhere');
		$sql = fonclistewhere($lilang);

		// On récupère les auteurs pour générer le PDF
		$query = sql_select('*', 'spip_auteurs', $sql, null, 'date_maj');

		// Ce tableau contiendra les pages html qui serviront à créer les multiples PDF
		$html = array();

		// Le début de chaque page html
		$debut_html = '
		<html>
		<head>
			<style>
				/* 
				http://meyerweb.com/eric/tools/css/reset/ 
				v2.0 | 20110126
				License: none (public domain)
				*/

				html, body, div, span, applet, object, iframe,
				h1, h2, h3, h4, h5, h6, p, blockquote, pre,
				a, abbr, acronym, address, big, cite, code,
				del, dfn, em, img, ins, kbd, q, s, samp,
				small, strike, strong, sub, sup, tt, var,
				b, u, i, center,
				dl, dt, dd, ol, ul, li,
				fieldset, form, label, legend,
				table, caption, tbody, tfoot, thead, tr, th, td,
				article, aside, canvas, details, embed, 
				figure, figcaption, footer, header, hgroup, 
				menu, nav, output, ruby, section, summary,
				time, mark, audio, video {
					margin: 0;
					padding: 0;
					border: 0;
					font-size: 100%;
					font: inherit;
					vertical-align: baseline;
				}
				/* HTML5 display-role reset for older browsers */
				article, aside, details, figcaption, figure, 
				footer, header, hgroup, menu, nav, section {
					display: block;
				}
				body {
					line-height: 1;
				}
				ol, ul {
					list-style: none;
				}
				blockquote, q {
					quotes: none;
				}
				blockquote:before, blockquote:after,
				q:before, q:after {
					content: \'\';
					content: none;
				}
				table {
					border-collapse: collapse;
					border-spacing: 0;
					width: 100%;
				}
				
				td {
					text-align: center;
					padding: 20px;
					vertical-align: middle;
					width: 258px; 
					height: 108px;
				}

			</style>

			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		</head>
		<body>';

		// La fin de chaque page html
		$fin_html .= '</body></html>';

		// Le compteur d'étiquettes
		$i = 0;
		// Le compteur de PDF
		$c = 0;
		// On boucle sur le SQL
		while ($res = sql_fetch($query)) {
			// Toute les 14 étiquette con créé une nouvelle page
			if ($i%14 == 0) $html[$c] = $debut_html.'<table style="width: 100%; text-align: center;"><tbody>';
			// Toute les 2 étiquettes on créer une nouvelle ligne
			if ($i%2 == 0) $html[$c] .= '<tr>';
			// On met à jour le compteur d'étiquette
			$i++;
			
			// On ajoute l'étiquette
			$html[$c] .= '
				<td>
					'.$res['nom'].' '.$res['prenom'].'
					<br />'.$res['adresse'].'
					<br /> '.$res['codepostal'].' '.$res['localite'].'
				</td>
			';
			
			// On ferme la ligne
			if ($i%2 == 0) $html[$c] .= '</tr>';
			
			if ($i%14 == 0) {
				// on ferme la page html
				$html[$c] .= '</tbody></table>'.$fin_html; 
				
				// On met à jour le nombre de page html créer
				$c++;
			}
		}

		$pdf->addAllQueue($html);

		echo $pdf->display_js();

		echo '<div id="largePDF_rapport"></div>';
		
		echo fin_grand_cadre(true);
			
		echo fin_page();
	}
}
?>