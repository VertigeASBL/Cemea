<?php
// Si un nouveau statut de payement est envoyé
if (isset($_POST['statut_payement'])) {
	change_statut_payement($_POST['statut_payement'], $_POST['id_auteur'], $_POST['id_article'], $_POST['email']);
}

// Si on veux jeter un auteur à la poubelle
if (isset($_GET['poubelle'])) {
	delete_inscrit($_GET['poubelle']);
}

if (isset($_GET['pdf_envoye'])) {
	include_spip('inc/presentation');

	// On affiche une boite pour confirmer le changement à l'utilisateur.
    echo debut_boite_info();
    
    // On affiche le message de confirmation.
    echo _T('gestion:envoyer_pdf_confirmer');

    // On ferme la boite.
    echo fin_boite_info();
}
?>