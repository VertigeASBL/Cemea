<?php 
if (!defined("_ECRIRE_INC_VERSION")) return;

function exec_auteur_json() {
	// Début de la page d'admin
	if (session_get('statut') != '0minirezo') {
		include_spip('inc/minipres');
		echo minipres('Vous n\'avez pas les autorisations.');
	}
	else {
        header('Content-type: application/json');
        header('charset=utf-8');
        
        $recherche = sql_allfetsel('CONCAT_WS(\' \', nom, prenom) AS value', 'spip_auteurs', 'CONCAT_WS(\' \', nom, prenom) LIKE '.sql_quote('%'._request('search').'%'), '', '', '0,10');
        
        echo json_encode($recherche);
	}	
}
?>