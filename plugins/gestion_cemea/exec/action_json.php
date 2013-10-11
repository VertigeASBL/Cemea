<?php 
if (!defined("_ECRIRE_INC_VERSION")) return;

function exec_action_json() {
    
    header('Content-type: application/json');
    header('charset=utf-8');
    
    $sql_action = sql_allfetsel(
        'idact, titre, date_debut', 
        'spip_articles AS a 
            INNER JOIN spip_mots_rubriques AS m ON a.id_rubrique = m.id_rubrique', 
        'CONCAT_WS(\' \', idact, titre) LIKE '.sql_quote('%'._request('search').'%').' AND id_mot IN (5,3627)', '', '', '0,10');

    $recherche = array();
    foreach ($sql_action as $key => $value) {
        $recherche[] = array(
            'value' => $value['idact'],
            'label' => $value['idact'].' '.(addslashes(supprimer_numero($value['titre']))).' '.affdate($value['date_debut'])
            );
    }

    echo json_encode($recherche);

}
?>