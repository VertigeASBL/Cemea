<?php
/*
	Générer les .htpassword
	-----------------------
		pour le gestionnaire de fichiers en modification - manager.php - .htpw_modif - phil, vertige, francois, jeanpaul
		pour le gestionnaire de fichiers en consultation - index.php - .htpw_intra - rédacteurs et administrateurs
		pour les documents dans docs/intranet/ - .htpw_intra - rédacteurs et administrateurs
		pour les documents dans docs/web/ - .htpw_web - visiteurs, rédacteurs et administrateurs

	http://www.cemea.be/vertige/docs/manager.php
	http://www.cemea.be/vertige/docs/index.php
	http://www.cemea.be/vertige/docs/intranet/un_fichier
	http://www.cemea.be/vertige/docs/web/un_fichier
*/

if (!defined('_ECRIRE_INC_VERSION')) return;

/*
//--- echo genie_intranet_modif_ht_dist('');
//--- return 'test intranet_modif_ht';
	if (! isset($_GET['prm']) || $_GET['prm'] != 'g9c3yn42q8')
		$retour .= 'Erreur: paramètre manquant';
// return 'getcwd : '.getcwd();
function mysql_fconnect() {
	$sql_server = 'mysql.domainepublic.net'; //--- 'localhost';
	$GLOBALS['sql_user'] = 'cemea'; //--- 'root';
	$sql_passw = 'dj1...'; //--- '';
	$sql_bdd = 'cemea';

	$dblk = mysql_connect($sql_server, $GLOBALS['sql_user'], $sql_passw);
	if (! $dblk)
		die('Erreur : Connexion impossible à la base de données');
	if (! mysql_select_db($sql_bdd, $dblk))
		die('Erreur : Sélection impossible de la base de données');
	return $dblk;
}
	$db_link = mysql_fconnect();
	mysql_close($db_link);
*/
function genie_intranet_modif_ht_dist($t) {
	$retour = '';

	//--- rédacteurs et administrateurs : htpw_intra
	$req = mysql_query('SELECT login,htpass FROM spip_auteurs WHERE login<>\'\' AND htpass<>\'\' AND statut IN (\'0minirezo\',\'1comite\') ORDER BY statut,id_auteur');
	if (! $req)
		return 'Erreur SQL : '.mysql_error();

	$chemin = 'docs/intrapw/';
	if (! $fich = fopen($chemin.'.tmp_htpw_intra', 'w'))
		return 'Erreur fopen';
	else {
		flock($fich, 2);

		$nbr = 0;
		while ($data = mysql_fetch_array($req)) {
			$chn = $data['login'].':'.$data['htpass'];
			if (! fwrite($fich, $chn."\n"))
				return 'Erreur fwrite';
			$nbr++;
		}
		flock($fich, 3);
		if (! fflush($fich))
			return 'Erreur fflush';
		fclose($fich);
		if (! copy($chemin.'.tmp_htpw_intra', $chemin.'.htpw_intra'))
			return 'Erreur copy';

		$retour .= ' - nombre redacs et admins : '.$nbr.' ';
	}
	//--- visiteurs : htpw_web
	$req = mysql_query('SELECT login,htpass FROM spip_auteurs WHERE login<>\'\' AND htpass<>\'\' AND statut IN (\'0minirezo\',\'1comite\',\'6forum\') ORDER BY statut,id_auteur');
	if (! $req)
		return 'Erreur SQL : '.mysql_error();

	if (! $fich = fopen($chemin.'.tmp_htpw_web', 'w'))
		return 'Erreur fopen';
	else {
		flock($fich, 2);

		$nbr = 0;
		while ($data = mysql_fetch_array($req)) {
			$chn = $data['login'].':'.$data['htpass'];
			if (! fwrite($fich, $chn."\n"))
				return 'Erreur fwrite';
			$nbr++;
		}
		flock($fich, 3);
		if (! fflush($fich))
			return 'Erreur fflush';
		fclose($fich);
		if (! copy($chemin.'.tmp_htpw_web', $chemin.'.htpw_web'))
			return 'Erreur copy';

		$retour .= ' - nombre avec visiteurs : '.$nbr.' ';
	}
	return $retour;
}
?>
