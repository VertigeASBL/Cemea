<?php

// Calcule la date déchéance de payement
function echeance($date, $plus = false) {
	if ($plus) return date('Y-m-d H:m:s',strtotime($date)+(3600*24*15));
	else return date('Y-m-d H:m:s',strtotime($date)-(3600*24*15));
}

// Calcule la date déchéance de début d'activité
function echeance_activite($date, $plus = false) {
	if ($plus) return date('Y-m-d H:m:s',strtotime($date)+(3600*24*31));
	else return date('Y-m-d H:m:s',strtotime($date)-(3600*24*31));
}

// Calcule l'age en fonction de la date de naissance
function age($date) {
	return (int) ((time() - strtotime($date)) / 3600 / 24 / 365);
}

// Retourne le montant totale payé par la personne. 
function calculer_payement ($str) {
	$str = explode(';', $str);

	return array_sum($str);
}

// Cette fonction calcule l'interval entre un date et maintenant
// La class php DateTime est présente dans PHP 5.2.
// La methode DateTime::diff demande PHP 5.3 !
function calculer_jour ($date1) {
	/* Création des deux objet dateTime */
	$d1 = new DateTime($date1);
	$d2 = new DateTime('now');

	/* Comparaison */
	$diff = $d1->diff($d2); 

	/* On retourne le nombre de jours total => http://be1.php.net/manual/fr/dateinterval.format.php */
	return $diff->format('%a');
}

// Cette fonction "Calcules" les balises placé dans le champs texte pour les PDF
function balise_pdf($texte, $id_activite, $id_personne) {
	// On inclut les fonctions pour faire joli
	include_spip('public/interfaces');
	// Et les filtres
	include_spip('inc/filtres');

	// Récupération des informations de l'activité
	$activite = sql_fetsel('*', 'spip_articles', 'id_article='.$id_activite);
	// Récupération des informations de l'inscription
	$inscription = sql_fetsel('*', 'spip_auteurs_articles', 'id_auteur='.$id_personne);
	// Récupération des informations de l'auteur
	$auteur = sql_fetsel('*', 'spip_auteurs', 'id_auteur='.$id_personne);

	// Récupération du lieux ou ce déroule l'activité
	$adresse = sql_getfetsel('texte', 'spip_articles', 'titre=\''.$activite['lieu_deroulement'].'\'');

    // On récupère les pieds de pages
    $pied_sj = '<div class="pied_page">'.propre(sql_getfetsel('texte', 'spip_articles', 'id_article='.sql_quote(235))).'</div>';
    $pied_ep = '<div class="pied_page">'.propre(sql_getfetsel('texte', 'spip_articles', 'id_article='.sql_quote(209))).'</div>';
	
	$balise_pdf = array(
		'#DATE_ANNULATION',
		'#DATE_DEBUT',
		'#REFERENCE',
		'#LIEU_DEROULEMENT',
		'#NOM_FORMATION',
		'#NOM',
		'#PRENOM',
		'#DATE_NAISSANCE',
		'#LIEU_NAISSANCE',
		'#TELEPHONE_ORGANISATION',
		'#TEXTE_PRESENTATION',
		'#HEURE_FORMATION',
		'#DATE_FIN',
		'#ADRESSE',
		'#PAGE',
        '#PIED_EP',
        '#PIED_SJ'
		);

	$conversion = array(
		affdate(echeance($inscription['date_suivi'], true)),
		affdate($activite['date_debut']),
		$activite['idact'],
		propre($adresse),
		supprimer_numero($activite['titre']),
		$auteur['nom'],
		$auteur['prenom'],
		affdate($auteur['date_naissance']),
		$auteur['lieunaissance'],
		$activite['telephone_orga'],
		$activite['text_presentation'],
		$activite['heure_formation'],
		$activite['dates_ra'],
		$auteur['adresse'].'<br />'.$auteur['codepostal'].' '.$auteur['localite'],
		'<div style="page-break-after: always;"></div>',
        $pied_ep,
        $pied_sj
		);

	// On remplace les balises
	$texte = str_replace($balise_pdf, $conversion, $texte);

	// Si l'auteur est une institution, on fixe le prix institution
	if ($auteur['typepart'] == 'I') {
		$texte = str_replace('#PRIX', $activite['prix_organisme'], $texte);
	}
	// Sinon, on vérifie le statut étudiant/demandeur d'emplois et on fixe le prix
	else {
		// Si c'est un étidiant, on fixe le prix étudiant
		if ($inscription['etudiant'] === 'oui') {
			$texte = str_replace('#PRIX', $activite['prix_etudiant'], $texte);
		}
		// Sinon, on fixe le prix normal
		else {
			$texte = str_replace('#PRIX', $activite['prix'], $texte);
		}	
	}

	$texte = str_replace('#CODECOURTOISIE', code_courtoisie($auteur['codecourtoisie']), $texte);

	return $texte;
}


/*
*   Fonction qui renvoie le code courtoisie en fonction du sexe de la personne.
*/
function code_courtoisie($sexe) {
    if ($sexe == 'Masculin') return 'M.';
    elseif ($sexe == 'Féminin') return 'Mme';
    else return '';
}

/*
*   str_replace version SPIP
*/
function spip_replace($subject, $search, $replace) {
	return str_replace($search, $replace, $subject);
}

/*
*   html_entity_decode
*/
function decode_entities($str) {
	return html_entity_decode($str);
}

/*Converti la chaine du champ Ndiffusion en tableau utilisable...*/
function Ndiffusion2Array($liste) {
	$tab = explode(',', $liste);
	$chn = array();
	for ($k = 0, $g = 1; isset($tab[$k]); $k = ++$g, $g++)
		if ($tab[$g]) {
			$chn[$tab[$k]] = $tab[$g];
		}
	return $chn;
}

/*
*   Fonction qui transforme une chaine "payement" ou extrait de compte en ul>li
*/
function explode_payement($str, $sigle = '') {
    // On explode la chaine pour avoir un tableau
    $element = explode(';', $str);
    // Début de la chaine ul
    $output = '<ol>';
    // On ajoute les éléments
    foreach ($element as $key => $value) {
        // On évite de créer des li vide
        if (!empty($value)) $output .= '<li>'.$value.$sigle.'</li>';
    }
    // On ferme le ul
    $output .= '</ol>';

    return $output;
}
?>