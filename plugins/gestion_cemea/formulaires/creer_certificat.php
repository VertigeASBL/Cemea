<?php
function formulaires_creer_certificat_charger_dist($id_article, $asbl) {
    
    // Seul les admin peuvent utiliser ce formulaire
    if (session_get('status') != '0minirezo')
        return false;

    $contexte = array(
            'asbl' => $asbl
    );
    return $contexte;
}

function formulaires_creer_certificat_verifier_dist() {
    $erreurs = array();
    if (!_request('id_certif')) {
            $erreurs['message_erreur'] = 'Vous devez choisir un certificat';
            $erreurs['NomErreur'] = '';
    }
    return $erreurs;
}

function formulaires_creer_certificat_traiter_dist($id_article) {
 		
		$data = _request('id_certif');

    // message
    return array(
            'editable' => true,
            'message_ok' => '',
            'redirect' => generer_url_ecrire('gestion_dompdf_exec', 'id_certif='.$data.'&id_article='.$id_article)
        );
}
?>