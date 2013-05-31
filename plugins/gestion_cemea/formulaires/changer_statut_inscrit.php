<?php
function formulaires_changer_statut_inscrit_charger_dist($statutsuivi, $id_auteur, $id_article) {
    $contexte = array(
            'statutsuivi' => $statutsuivi,
    );
    
    return $contexte;
}

function formulaires_changer_statut_inscrit_verifier_dist($statutsuivi, $id_auteur, $id_article) {
    $erreurs = array();
    
    return $erreurs;
}

function formulaires_changer_statut_inscrit_traiter_dist($statutsuivi, $id_auteur, $id_article) {

    // Mise à jour de la base de donnée 
    sql_updateq('spip_auteurs_articles', 
        array('statutsuivi' => _request('statutsuivi')), 
        'id_auteur='.$id_auteur.' AND id_article='.$id_article);

    // Si le statut est une confirmation de l'inscription.
    if (_request('statutsuivi') == 'X' or _request('statutsuivi') == 'I')
        // On ajoute la date a laquel le payement à été validé
        sql_update('spip_auteurs_articles', 
            array('date_validation' => 'NOW()'), 
            'id_auteur='.sql_quote($id_auteur).' AND id_article='.sql_quote($id_article));


    /* message */
    return array(
            'editable' => true,
            'message_ok' => _T('gestion:changement_statut')
    );
}
?>