<?php //--- richir
function targetblank($url) {
	$url = str_replace(array('%3A','%2F','%3F','%3D','%26','%23','%0A'), array(':','/','?','=','&amp;','#','+'), urlencode($url));
	if (strpos($url, 'http://') === 0)
		$url .= '" target="_blank';
	return $url;
}
function filtrenewsletter($chn) {
/*	$chn = str_replace('/ecrire/?exec=articles&amp;id_article=', '/spip.php?page=article&amp;id_article=', $chn);
	$chn = str_replace('/ecrire/?exec=naviguer&amp;id_rubrique=', '/spip.php?page=rubrique&amp;id_rubrique=', $chn); */
	$chn = str_replace('style=\'float:left;', 'style=\'float:left; margin:2px 20px 5px 0;', $chn);
	$chn = str_replace('style=\'float:right;', 'style=\'float:right; margin:2px 0 5px 20px;', $chn);
	$chn = str_replace('<h3 class="spip">', '<h3 class="spip" style="text-align:left; font-size:1.4em; font-weight:bold; color:#73B1D4; margin:15px 15px 5px 15px; padding:0;">', $chn);
	return $chn;
}
function nlversbr($chn) {
	$chn = str_replace('</p>', '', str_replace('<p>', '', $chn));
	$chn = str_replace("\n", '<br />', str_replace("\r\n", '<br />', $chn));
	return $chn;
}
function monnl2br($chn) { //--- mes_options.php : pipeline post_propre
	$chn = str_replace("\n", '<br />', str_replace("</p>\n", '</p>', str_replace("\n<p>", '<p>', $chn)));
	$chn = str_replace('<br />', "<br />\n", str_replace('</p>', "</p>\n", str_replace('<p>', "\n<p>", $chn)));
	return $chn;
}
function obtenirpaire($tab, $cle) {
	$t2 = array_keys($tab, $cle);
	reset($t2);
	while (list(, $k) = each($t2))
		if (! ($k & 1)) {
			$k |= 1;
			if (isset($tab[$k]))
				return $tab[$k];
		}
	return '0';
}
function afficherpaires($liste) { /* paires = (clé + valeur numérique) - voir plugins/champs_extras2/cextras_pipelines.php */
	$tab = explode(',', $liste);
	$chn = '';
	for ($k = 0, $g = 1; isset($tab[$k]); $k = ++$g, $g++)
		if ($tab[$g]) {
			if ($chn) $chn .= ', ';
			$chn .= $tab[$k].' '.$tab[$g];
		}
	return $chn;
}
function balise_SET_BALISE($p) {
	$_var = interprete_argument_balise(1,$p);
	$_val = interprete_argument_balise(2,$p);
	$_niv = interprete_argument_balise(3,$p) == '\'ENV\'' ? '0' : '$SP';
	if ($_var AND $_val)
		$p->code = 'vide($Pile['.$_niv.']['.$_var.'] = '.$_val.')';
	else
		$p->code = "''";
	$p->interdire_scripts = false;
	return $p;
}
/*
squelettes/mot.html	+ [(#ID_RUBRIQUE|setcontexte{id_rubrique})]
function setcontexte($val, $var) {
	$GLOBALS['contexte'][$var] = $val;
}
//echo '<hr /><pre>'; echo htmlspecialchars($chn); echo '</pre>';

function affichertab($chn, $sep) {
	return implode($sep, explode(',', $chn));
}
function estdansliste($chn, $cherch) {
	return in_array($cherch, explode(',', str_replace('&#039;', '\'', str_replace('&#39;', '\'', $chn)))) ? ' ' : '';
}
function estdanstab($tab, $cherch) {
	return is_array($tab) ? (in_array($cherch, $tab) ? ' ' : '') : '';
}
function tabtostr($tab) {
	return is_array($tab) ? implode(',', $tab) : '';
}

function inverserdate($chn) {
	if ($chn)
		$chn = substr($chn, 8, 2).'/'.substr($chn, 5, 2).'/'.substr($chn, 0, 4);
	return $chn;
}
function pageprecedente($void) {
	$chn = isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : '';
	$k = strpos($chn, '?');
	if ($k !== false)
		$chn = substr($chn, 0, $k);
	return $chn;
}
function getphpcookie($var) {
	return isset($_COOKIE[$var]) ? (int) $_COOKIE[$var] : 0;
}
function tabsetvaleur($tab, $cle, $val) {
	$tab[$cle] = $val;
	return $tab;
}
function nlverslien($chn) {
	$tab = explode("\n", $chn);
	reset($tab); $chn = '';
	while (list(, $k) = each($tab))
		if ($k = htmlspecialchars(strtolower(trim($k)))) {
			if (strpos($k, 'http://') !== 0)
				$g = 'http://'.$k;
			$chn .= '<a href="'.$g.'">'.$k.'</a><br />';
		}
	return $chn;
}
function urlarticle($url, $chapo) {
	if ($chapo && $chapo{0} == '=') {
		$chapo = str_replace(array('%3A','%2F','%3F','%3D','%26','%23'), array(':','/','?','=','&amp;','#'), rawurlencode(substr($chapo, 1)));
		if (strpos($chapo, 'http://') === 0)
			$chapo .= '" target="_blank';
		return $chapo;
	}
	else
		return $url;
}
//---  [(#VAL{init}|setvarglobal{'nom'})] [(#VAL{0}|getvarglobal{'nom'})]
function setvarglobal($val, $nom) { $GLOBALS[$nom] = $val; }
function getvarglobal($void, $nom) { return isset($GLOBALS[$nom]) ? $GLOBALS[$nom] : ''; }
*/
?>
