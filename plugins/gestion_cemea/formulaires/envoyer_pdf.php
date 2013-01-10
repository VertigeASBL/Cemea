<?php
function formulaires_envoyer_pdf_charger_dist() {
        $contexte = array(
                '' => '',
        );
        return $contexte;
}

function formulaires_envoyer_pdf_verifier_dist() {
        $erreurs = array();
        if (!_request('envoyer_pdf')) {
                $erreurs['message_erreur'] = 'Vous devez choisir un document PDF.';
                $erreurs['NomErreur'] = '';
        }
        return $erreurs;
}

function formulaires_envoyer_pdf_traiter_dist($id_article, $id_auteur) {
 		
 		$data = _request('envoyer_pdf');
		
 		$redirect = urlencode(_request('redirect'));

		// message
        return array(
               	'editable' => true,
                'message_ok' => 'Le PDF à été créer',
                'redirect' => generer_url_ecrire('gestion_dompdf_exec', 'modele='.$data.'&id_auteur='.$id_auteur.'&id_article='.$id_article.'&envoyer_par_mail=1&redirect='.$redirect)
        );
}
?>