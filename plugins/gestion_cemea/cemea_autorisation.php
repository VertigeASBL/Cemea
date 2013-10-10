<?php
/*
*    Gestion des autorisations du plugin
*    Admin: Full accès.
*    Rédacteur: Lecture seul.
*
*    Didier - Vertige ASBL
*    http://www.vertige.org/
*    http://p.henix.be/
*/


// Autoriser le bouton de gestion pour les Rédacteur et les admin.
function autoriser_gestion_dist($faire, $type, $id, $qui, $opt) {
    if ($qui['statut'] == '1comite' or $qui['statut'] == '0minirezo')
        return true;
    else 
        return false;;
}

// Tableau de bord.
function autoriser_tableau_de_bord_dist($faire, $type, $id, $qui, $opt) {
    return autoriser_gestion_dist($faire, $type, $id, $qui, $opt);
}

// Les autres boutons, voir le fichiers plugin.xml

function autoriser_gestion_payement_dist($faire, $type, $id, $qui, $opt) {
    return autoriser_gestion_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_gestion_inscriptions_dist($faire, $type, $id, $qui, $opt) {
    return autoriser_gestion_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_gestion_inscrit_dist($faire, $type, $id, $qui, $opt) {
    return autoriser_gestion_dist($faire, $type, $id, $qui, $opt);
}

function autoriser_gestion_action_dist($faire, $type, $id, $qui, $opt) {
    return autoriser_gestion_dist($faire, $type, $id, $qui, $opt);
}

?>