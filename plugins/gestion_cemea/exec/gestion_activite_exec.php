<?php 
if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/presentation');
include_spip('gestion_autorisation');


function exec_gestion_activite_exec() {
    // Début de la page d'admin
    $commencer_page = charger_fonction('commencer_page','inc');
    echo $commencer_page(_T('gestion:gestion_activite'));
    
    gros_titre(_T('gestion:gestion_activite'));

    // On commence le cadre principal
    echo debut_grand_cadre(true);

    echo icone_inline(_T('gestion:ecrire_nouvelle_action'), generer_url_ecrire("editer_activite_exec","id_rubrique=43&new=oui"), "article-24.gif","creer.gif", 'right');
    
    echo '<div class="nettoyeur"></div>';
    
    echo debut_cadre_relief();

    echo recuperer_fond('prive/gestion_activite/gestion_activite', 
                        array('id_article' => _request('id_article')), 
                        array('ajax' => true) );

    echo fin_cadre_relief(); 
    echo fin_grand_cadre(true);
    echo fin_page();
}
?>