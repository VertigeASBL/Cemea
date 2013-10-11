<?php
function formulaires_creer_pdf_charger_dist($id_article, $id_auteur, $asbl) {
    // Seul les admin peuvent utiliser ce formulaire
    if (session_get('statut') != '0minirezo')
        return false;
    
        $contexte = array(
                'asbl' => $asbl
        );
        return $contexte;
}

function formulaires_creer_pdf_verifier_dist($id_article, $id_auteur, $asbl) {
        $erreurs = array();
        if (!_request('creer_pdf')) {
                $erreurs['message_erreur'] = 'Vous devez choisir un document PDF';
                $erreurs['NomErreur'] = '';
        }
        return $erreurs;
}

function formulaires_creer_pdf_traiter_dist($id_article, $id_auteur, $asbl) {
 		
 		$data = _request('creer_pdf');
		
		// message
        return array(
               	'editable' => true,
                'message_ok' => 'Le PDF à été créer',
                'redirect' => generer_url_ecrire('gestion_dompdf_exec', 'modele='.$data.'&id_auteur='.$id_auteur.'&id_article='.$id_article)
        );
}
?>