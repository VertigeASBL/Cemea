<?php 
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('fonctions_gestion_cemea');

function exec_gestion_payement_exec() {
	
	// Début de la page d'admin
	$commencer_page = charger_fonction('commencer_page','inc');
	echo $commencer_page(_T('gestion:gestion_payement'));
	
	echo '
	<style type="text/css">
		#conteneur, .table_page, div.messages {
			width: 960px;
		}
	</style>';
	
	
	// On affiche un titre.
	gros_titre(_T('gestion:gestion_payement'));
	
	// On commence le cadre principal
	echo debut_grand_cadre(true);
	
	// Possibilité de faire des updates dans la base de donnée.
	include_spip('prive/gestion_update_db');

	if (_request('id_participant') or _request('id_activite')) {
		echo icone_inline(_T('gestion:retour'), generer_url_ecrire('gestion_payement_exec'), find_in_path('img/payement-24.png'),'rien.gif', 'left');
		echo '<div class="nettoyeur"></div>';
	}
	
	// Formulaire de recherche dans le tableau des payements.
	echo debut_cadre_relief('', false, '', _T('gestion:echeance'), '', '');
	echo recuperer_fond('prive/gestion_payement/retrouver_payement_form', array(), array('ajax' => false) );

	// Tableau des échéances de payement.
	echo recuperer_fond('prive/gestion_payement/gestion_payement', array('id_auteur' => _request('id_participant'), 'id_article' => _request('id_activite')), array('ajax' => false) );
	echo fin_cadre_relief();

	echo fin_grand_cadre(true);

	
	echo fin_page();
}
?>