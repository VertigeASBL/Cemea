<?php
function formulaires_sql_charger_dist() {
    // Contexte du formulaire.
    $contexte = array(
        'sql_command' => _request('sql_command')
    );

    return $contexte;
}

/*
*   Fonction de vérification, cela fonction avec un tableau d'erreur.
*   Le tableau est formater de la sorte:
*   if (!_request('NomErreur')) {
*       $erreurs['message_erreur'] = '';
*       $erreurs['NomErreur'] = '';
*   }
*   Pensez à utiliser _T('info_obligatoire'); pour les éléments obligatoire.
*/
function formulaires_sql_verifier_dist() {
    $erreurs = array();
    $command_erreur = 'Seul la commande SELECT est autorisée';

    if (!_request('sql_command'))
        $erreurs['message_erreur'] = 'Entrez une commande SQL';

    // On interdit les commandes de modification, pas top.
    if (preg_match('#UPDATE|INSERT|DELETE|CREATE|DISTINCT|TRUNCATE|DROP|EXECUTE|RENAME|ALTER|UPGRADE#i', _request('sql_command')))
        $erreurs['message_erreur'] = $command_erreur;


    return $erreurs;
}

function formulaires_sql_traiter_dist() {
    //Traitement du formualaire.

    $sql = sql_query(_request('sql_command'));

    spip_log(_request('sql_command'), 'sql_cemea');

    // C'est partit pour un export CSV
    $exporter_csv = charger_fonction('exporter_csv', 'inc');

    $exporter_csv('export_sql', $sql);

    // Donnée de retour.
    return array(
            'editable' => true,
            'message_ok' => 'Commande exécuté avec succès.'
    );
}
?>