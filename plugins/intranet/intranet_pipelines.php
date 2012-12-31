<?php
function intranet_ajouterBoutons($boutons_admin) {
	// si on est admin, ajout du bouton dans la barre "configuration"
	$menu = 'naviguer';
	if (isset($boutons_admin['configuration']))
		$menu = 'configuration';
	if ($GLOBALS['connect_statut'] == '0minirezo' && autoriser('webmestre', '', '', intval($GLOBALS['connect_id_auteur'])))
		$boutons_admin[$menu]->sousmenu['intranet_modif'] = new Bouton('../plugins/intranet/intranet-24.gif', 'Gestionnaire de fichiers');
	else if ($GLOBALS['connect_statut'] == '1comite')
		$boutons_admin[$menu]->sousmenu['intranet_intra'] = new Bouton('../plugins/intranet/intranet-24.gif', 'Gestionnaire de fichiers');
	return $boutons_admin;
}
function intranet_taches_generales_cron($taches_generales){
	$taches_generales['intranet_modif_ht'] = 86400;
	return $taches_generales;
}
?>
