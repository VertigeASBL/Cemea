<?php
/**
 * Nota: plugin.xml en cache.
		si modif plugin.xml, il faut parfois réactiver le plugin (config/plugin: désactiver/activer)
 * @version Original From SPIP-Listes-V :: Id: spiplistes_listes_forcer_abonnement.php paladin@quesaco.org
 * @package spiplistes
 */
 // $LastChangedRevision: 50614 $
 // $LastChangedBy: root $
 // $LastChangedDate: 2011-08-21 00:00:15 +0200 (Sun, 21 Aug 2011) $

if(!defined('_ECRIRE_INC_VERSION')) return;

include_spip('inc/spiplistes_api_globales');

function spiplistes_ajouterBoutons($boutons_admin) {

	if($GLOBALS['connect_statut'] == "0minirezo") {
		$menu = "naviguer";
		$icone = "courriers_listes-24.gif";
		if (isset($boutons_admin['bando_edition'])){
			$menu = "bando_edition";
			$icone = "spip-listes-16.png";
		}
	// affiche le bouton dans "Edition"
		$boutons_admin[$menu]->sousmenu['spiplistes'] = new Bouton(
			_DIR_PLUGIN_SPIPLISTES_IMG_PACK.$icone  // icone
			, 'spiplistes:listes_de_diffusion_'	// titre
			, _SPIPLISTES_EXEC_COURRIERS_LISTE
		);
	}
	return ($boutons_admin);
}
