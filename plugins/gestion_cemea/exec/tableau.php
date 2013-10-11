<?php 
if (!defined("_ECRIRE_INC_VERSION")) return;

include_spip('inc/presentation');
include_spip('fonctions_gestion_cemea');

function exec_tableau() {
	// Début de la page d'admin
	$commencer_page = charger_fonction('commencer_page','inc');
	echo $commencer_page(_T('gestion:tableau_de_bord'));
	
	echo '
	<style type="text/css">
		#conteneur, .table_page, div.messages {
			width: 960px;
		}
	</style>';
    
    // On affiche un titre.
	gros_titre(_T('gestion:tableau_de_bord'));
	
	// On commence le cadre principal
	echo debut_grand_cadre(true);
	
	// Possibilité de faire des updates dans la base de donnée.
	include_spip('prive/gestion_update_db');

	// Possibilité d'ajouter une nouvelle activité.
	echo icone_inline(_T('gestion:ecrire_nouvelle_action'), generer_url_ecrire("editer_activite_exec","id_rubrique=43&new=oui"), "article-24.gif","creer.gif", 'right');

	// Ligne suivante du tableau de bord
	echo '<div class="nettoyeur"></div>';

	// Tableau des échéances de payement.
	echo debut_cadre_relief('', false, '', '<a href="'.generer_url_ecrire('gestion_payement_exec').'" title="'._T('gestion:echeance').'">'._T('gestion:echeance').'</a>', '', '');
	echo recuperer_fond('prive/tableau_de_bord/echeance', array(), array('ajax' => true) );
	echo fin_cadre_relief();

	// Ligne suivante du tableau de bord.
	echo '<div class="nettoyeur"></div>';

	// Tableau des échéances des action
	echo debut_cadre_relief('', false, '', '<a href="'.generer_url_ecrire('gestion_activite_exec').'" title="'._T('gestion:activite_proche').'">'._T('gestion:activite_proche').'</a>', '', '');
	echo recuperer_fond('prive/tableau_de_bord/start_action', array(), array('ajax' => true) );
	echo fin_cadre_relief();

	// Ligne suivante du tableau de bord.
	echo '<div class="nettoyeur"></div>';
	
	// Tableau des dernières inscritpions annulée.
	echo debut_cadre_relief('', false, '', _T('gestion:dernier_annule'), '', 'float');
	echo recuperer_fond('prive/tableau_de_bord/annulation', array(), array('ajax' => true) );
	echo fin_cadre_relief();
	
	// Tableau des derniers payement validé.
	echo debut_cadre_relief('', false, '', '<a href="'.generer_url_ecrire('gestion_inscription_exec').'" title="'._T('gestion:dernier_payement').'">'._T('gestion:dernier_payement').'</a>', '', 'float margin-float');
	echo recuperer_fond('prive/tableau_de_bord/dernier_payement', array(), array('ajax' => true) );
	echo fin_cadre_relief();

	// Ligne suivante du tableau de bord.
	echo '<div class="nettoyeur"></div>';

	// Tableau des remboursements.
	echo debut_cadre_relief('', false, '', _T('gestion:remboursement'), '', '');
	echo recuperer_fond('prive/tableau_de_bord/remboursement', array(), array('ajax' => true) );
	echo fin_cadre_relief();

	echo fin_grand_cadre(true);
	
	echo fin_page();
}
?>