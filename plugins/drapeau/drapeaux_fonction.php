<?php
function drapeau_location ($nom_du_pays) {
	return find_in_path('drapeaux/'.ucfirst(trim(str_replace(' ', '_', $nom_du_pays))).'.png'); 
}

function drapeau_to_img ($nom_du_pays) {	
	return filtre_balise_img_dist(drapeau_location($nom_du_pays));
}

// http://doc.spip.org/@insert_article
function insert_drapeau() {
	include_spip('base/abstract_sql');
	include_spip('inc/utils');

	$champs = array(
		'drapeau_pays' => '',
	);

	// Envoyer aux plugins
	$champs = pipeline('pre_insertion',
		array(
			'args' => array(
				'table' => 'spip_drapeaus',
			),
			'data' => $champs
		)
	);

	$id_drapeau = sql_insertq("spip_drapeaus", $champs);
	
	pipeline('post_insertion',
		array(
			'args' => array(
				'table' => 'spip_drapeaus',
				'id_objet' => $id_article
			),
			'data' => $champs
		)
	);

	return $id_drapeau;
}

function drapeau_set($id_drapeau, $set=null) {
	include_spip('inc/modifier');
	return modifier_contenu('drapeau', $id_drapeau, array(), $set);
}


//$test = insert_drapeau();
//drapeau_set($test, array('drapeau_pays' => 'Blabla'));

?>