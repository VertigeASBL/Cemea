<?php
function listeselect_ajouterBoutons($boutons_admin) {
	// si on est admin, ajout du bouton dans la barre "configuration"
	if ($GLOBALS['connect_statut'] == '0minirezo')
		$boutons_admin['gestion']->sousmenu['listeselect']= new Bouton('../plugins/listeselect/reply-to-all-24.gif', 'Selection de destinataires');
	return $boutons_admin;
}
?>
