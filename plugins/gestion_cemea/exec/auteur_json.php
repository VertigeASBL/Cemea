<?php 
if (!defined("_ECRIRE_INC_VERSION")) return;

function exec_auteur_json() {
    header('Content-type: application/json');
    header('charset=utf-8');
    
    $recherche = sql_allfetsel('CONCAT_WS(\' \', nom, prenom) AS value', 'spip_auteurs', 'CONCAT_WS(\' \', nom, prenom) LIKE '.sql_quote('%'._request('search').'%'), '', '', '0,10');
    
    echo json_encode($recherche);
}
?>