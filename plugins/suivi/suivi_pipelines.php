<?php
function suivi_affiche_milieu($flux) {
	$exec =  $flux['args']['exec'];

	if ($exec=='articles'){
		if (autoriser('modifier', 'article', $flux['args']['id_article'])) {
			$contexte = array();
			foreach($_GET as $key=>$val)
				$contexte[$key] = $val;

			$suivi = recuperer_fond('prive/suivi_article', $contexte);
			$flux['data'] .= $suivi;
		}
	}
	else if ($exec=='auteur_infos'){
		if (autoriser('modifier', 'auteur', $flux['args']['id_auteur'])) {
			$contexte = array();
			foreach($_GET as $key=>$val)
				$contexte[$key] = $val;
			$suivi = recuperer_fond('prive/suivi_auteur', $contexte);
			$flux['data'] .= $suivi;
		}
	}
	return $flux;
}
function suivi_objets_extensibles($objets){
	/* --- richir vertige : champs extra dans la table auteurs_articles
			voir plugins/champs_extras2/inc/cextras.php
			voir plugins/champs_extras2/inc/cextras_gerer.php
			voir plugins/champs_extras2/cextras_options.php	--- */
		return array_merge($objets, array('auteurs_articles'=>'auteurs_article'));
}
?>
