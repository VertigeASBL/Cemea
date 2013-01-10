<?php
// // On modifie le header prive (Youhou !)
function gestion_header_prive($flux) {
 	include_spip("inc/filtres");

 	/* On a besoin d'une version de jQuery plus récente sur l'admin, on va donc remplacer jQuery de SPIP par le jQuery du plugin */

 	$flux = str_replace('../prive/javascript/jquery.js', (find_in_path('js/jquery.js')), $flux);

 	$flux .= '<!-- Auto-Complete Sytème -->';

	// On ajoute le script auto-complete
	$flux .= '<script type="text/javascript" src="'.(find_in_path('js/auto-complete/jquery.auto-complete.min.js')).'"></script>';
	$flux .= '<link rel="stylesheet" href="'.(find_in_path('js/auto-complete/jquery.auto-complete.css')).'" type="text/css" media="all" />';

    // L'auto submit du <select> des statuts
    $flux .= '<script type="text/javascript" src="'.(find_in_path('js/jquery.formStatut.js')).'"></script>';
  	
    //On ajoute le CSS général du plugin
	$flux .= '<link rel="stylesheet" href="'.(find_in_path('gestion_cemea.css')).'" type="text/css" media="all" />';

	// On renvoie le flux
	return $flux;
}

function gestion_affiche_gauche(&$flux){
    // Inclusion des fonctions pour faire des boites sans ce prendre la tête.
    include_spip('inc/presentation');
    
	/*
    	Boite du menu "Gestion des activités".
    */
    if ($flux['args']['exec'] == 'gestion_activite_exec') {

    	// Création de la boite et ajout du titre
		$flux['data'] .= debut_cadre_relief('',true,'', _T('gestion:option'));
    	
        // On ajoute les liens
    	$flux['data'] .= '';
    	
        // Fermeture du cadre.
    	$flux['data'] .= fin_cadre_relief(true);
    }

    /*
        Boite du menu "Gestion des inscriptions".
    */
	if ($flux['args']['exec'] == 'gestion_inscription_exec') {

		// Création de la boite et ajout du titre
		$flux['data'] .= debut_cadre_relief('',true,'', _T('gestion:option'));
    	// On ajoute les liens
        $flux['data'] .= '<ul>';

        // Récupération du statutsuivi actuel
        $statutsuivi = _request('statutsuivi');
        
        // Ou doit ton mettre la class ON ?
        if ($statutsuivi == 'T') $t = 'class="on"';
        elseif ($statutsuivi == 'X') $x = 'class="on"';
        elseif ($statutsuivi == 'I') $i = 'class="on"';
        elseif ($statutsuivi == 'A') $a = 'class="on"';
        elseif ($statutsuivi == 'C') $c = 'class="on"';
        else $on = 'class="on"';

        // Modification du flux
        $flux['data'] .= '<li><a href="'.generer_url_ecrire('gestion_inscription_exec').'" '.$on.'>Tout.</a></li>';
    	$flux['data'] .= '<li><a href="'.generer_url_ecrire('gestion_inscription_exec', 'statutsuivi=T').'" '.$t.'>Inscriptions à traiter.</a></li>';
        $flux['data'] .= '<li><a href="'.generer_url_ecrire('gestion_inscription_exec', 'statutsuivi=X').'" '.$x.'>Inscriptions réservé.</a></li>';
        $flux['data'] .= '<li><a href="'.generer_url_ecrire('gestion_inscription_exec', 'statutsuivi=I').'" '.$i.'>Inscriptions validé.</a></li>';
        $flux['data'] .= '<li><a href="'.generer_url_ecrire('gestion_inscription_exec', 'statutsuivi=A').'" '.$a.'>Inscriptions annulé.</a></li>';
        $flux['data'] .= '<li><a href="'.generer_url_ecrire('gestion_inscription_exec', 'statutsuivi=C').'" '.$c.'>Inscriptions Cemea.</a></li>';

        // Fermeture du UL
        $flux['data'] .= '</ul>';
    	// Fermeture du cadre.
    	$flux['data'] .= fin_cadre_relief(true);
	}
    // en renvoie le flux modifié.
    return $flux;
}
?>	