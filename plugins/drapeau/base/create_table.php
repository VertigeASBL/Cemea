<?php 
function drapeau_declarer_tables_principales($tables_principales) {
	$structure = array(
			'id_drapeau' => 'bigint(21) NOT NULL',
			'drapeau_pays' => 'tinytext NOT NULL'
	);

	$key = array('PRIMARY KEY' => 'id_drapeau');

	$tables_principales['spip_drapeaus'] = array('field' => &$structure, 'key' => &$key);

	return $tables_principales;
}

function drapeau_declarer_tables_interfaces ($interfaces) {
	$interfaces['table_des_tables']['drapeaus'] = 'drapeaus';
	$interfaces['table_des_traitements']['DRAPEAU_PAYS']['drapeaus'] = 'drapeau_to_img(%s)';

	return $interfaces;
}
?>