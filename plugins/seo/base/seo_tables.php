<?php

// Sécurité
if (!defined("_ECRIRE_INC_VERSION")) return;

function seo_declarer_tables_interfaces($interface){
	// 'spip_' dans l'index de $tables_principales
	$interface['table_des_tables']['seo'] = 'seo';
	
	return $interface;
}

function seo_declarer_tables_principales($tables_principales){
	//-- Table SEO -----------------------------------------------------
	$seo = array(
		'id_object'   => "int(11) NOT NULL",
        'type_object' => "varchar(10) NOT NULL",
        'meta_name'    => "varchar(20) NOT NULL",
        'meta_content'   => "text NOT NULL",
	);
	$seo_cles = array(
		"PRIMARY KEY" => "id_object, type_object, meta_name"
	);
	$tables_principales['spip_seo'] = array(
		'field' => &$seo,
		'key' => &$seo_cles
	);
	
	return $tables_principales;
}
