<?php 
/**
 * @package spiplistes
 */
 // $LastChangedRevision: 47229 $
 // $LastChangedBy: root $
 // $LastChangedDate: 2011-04-30 11:00:08 +0200 (Sat, 30 Apr 2011) $

if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/spiplistes_api_globales');
include_spip('inc/spiplistes_api_prive');
include_spip('inc/spiplistes_api_journal');

/*
/* Ajax, renvoie le contenu du log
/**/
function action_spiplistes_journal () {
	
	global $connect_toutes_rubriques, $connect_login, $connect_statut, $spip_lang_rtl;
	
	// spiplistes_log("controle appel action");
	
	if (!$connect_statut) {
		$auth = charger_fonction('auth', 'inc');
		$auth = $auth();
	}

	$securiser_action = charger_fonction('securiser_action', 'inc');
	$args = $securiser_action();

	$autoriser_lire = (autoriser('webmestre','','',$connect_id_auteur));
	
	if($autoriser_lire) {

		$result = spiplistes_journal_lire(_SPIPLISTES_PREFIX);
	
		echo($result);
		
		exit(0);
	}
}

?>