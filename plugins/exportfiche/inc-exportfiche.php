<?php
function exportfiche_ajouterBoutons($boutons_admin) {
	// si on est admin, ajout du bouton dans la barre "configuration"
	if ($GLOBALS['connect_statut'] == "0minirezo" && autoriser('webmestre', '', '', intval($GLOBALS['connect_id_auteur'])))
		$boutons_admin['gestion']->sousmenu['exportfiche']= new Bouton("../plugins/exportfiche/csvexport-24.png", 'Exporter des fiches');
	return $boutons_admin;
}
?>
