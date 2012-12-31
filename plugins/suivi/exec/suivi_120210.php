<?php
/* --- richir vertige --- */
// execute automatiquement par le plugin au chargement de la page ?exec=suivi
function exec_suivi() {
	$id_auteur = (int) _request('id_auteur');
	$id_article = (int) _request('id_article');
	$nouv_auteur = (int) _request('nouv_auteur');
	$contexte = array();
	$idper = '';
	$nom = '';
	$prenom = '';
	$statutauteur = '6forum';
	$inscrit = '';
	$statutsuivi = '';
	$date_suivi = '';
	$heure_suivi = '';

	//----------- lire DB ---------- AND id_secteur=2
	$req = sql_select('id_article,idact,titre', 'spip_articles', "id_article=$id_article");
	if ($data = sql_fetch($req)) {
		$idact = $data['idact'];
		$titre = $data['titre'];
	}
	else
		$id_article = 0;

	$req = sql_select('idper,nom,prenom,statut,inscrit,statutsuivi,date_suivi,heure_suivi', "spip_auteurs AS A LEFT JOIN spip_auteurs_articles AS S ON S.id_auteur=$id_auteur AND S.id_article=$id_article AND S.inscrit<>''", "A.id_auteur=$id_auteur");
	if ($data = sql_fetch($req)) {
		$idper = $data['idper'];
		$nom = $data['nom'];
		$prenom = $data['prenom'];
		$statutauteur = $data['statut'];
		if ($data['inscrit']) {
			$inscrit = 'Y';
			$statutsuivi = $data['statutsuivi'];
			$date_suivi = $data['date_suivi'];
			$heure_suivi = $data['heure_suivi'];
		}
	}
	else
		$id_auteur = 0;

	//-------- form soumis -----------
	if (_request('okconfirm') && $id_article && ($id_auteur || $nouv_auteur))
		if ($GLOBALS['connect_statut']!='0minirezo' || ! autoriser('modifier', 'article', $id_article))
			$contexte['message_erreur'] = 'Autorisation refusée';
		else {
			$statutsuivi = _request('statutsuivi');
			$date_suivi = _request('date_suivi');
			$heure_suivi = _request('heure_suivi');

			include_spip('inc/date_gestion');
			$contexte['erreurs'] = array();
			if (@verifier_corriger_date_saisie('suivi', false, $contexte['erreurs']))
				$date_suivi = substr($date_suivi, 6, 4).'-'.substr($date_suivi, 3, 2).'-'.substr($date_suivi, 0, 2);
			else
				$contexte['message_erreur'] = 'Erreur';

			if (! $contexte['message_erreur'])
				if ($nouv_auteur) {
					$req = sql_select('A.id_auteur,id_article',"spip_auteurs AS A LEFT JOIN spip_auteurs_articles AS S ON S.id_auteur=$nouv_auteur AND S.id_article=$id_article", "A.id_auteur=$nouv_auteur");
					if ($data = sql_fetch($req)) {
						$id_auteur = $data['id_auteur'];
						if (! $data['id_article'])
							sql_insertq('spip_auteurs_articles', array('id_auteur'=>$id_auteur, 'id_article'=>$id_article, 'inscrit'=>'Y'));
					}
					else {
						$contexte['message_erreur'] = 'Erreur';
						$contexte['erreurs']['nouv_auteur'] = 'auteur ID inconnu';
						$id_auteur = 0;
						$inscrit = '';
					}
				}
			if ($id_auteur && ! $contexte['message_erreur']) {
				sql_updateq('spip_auteurs_articles', array('inscrit'=>'Y', 'statutsuivi'=>$statutsuivi, 'date_suivi'=>$date_suivi, 'heure_suivi'=>$heure_suivi), "id_auteur=$id_auteur AND id_article=$id_article");
				$contexte['message_ok'] = 'Ok, l\'inscription est mise à jour';
				$inscrit = 'Y';
				include_spip('inc/headers');
				redirige_par_entete(parametre_url('?exec=articles', 'id_article', $id_article, '&'));
				exit();
			}
		}

	//-------- desinscrire -----------
	if (_request('noinscr') && $id_article && $id_auteur)
		if ($GLOBALS['connect_statut']!='0minirezo' || ! autoriser('modifier', 'article', $id_article))
			$contexte['message_erreur'] = 'Autorisation refusée';
		else {
			if ($statutauteur == '6forum')
				sql_delete('spip_auteurs_articles', "id_auteur=$id_auteur AND id_article=$id_article");
			else
				sql_updateq('spip_auteurs_articles', array('inscrit'=>''), "id_auteur=$id_auteur AND id_article=$id_article");
			$inscrit = '';
			$contexte['message_ok'] = 'Ok, la désinscription est faite';
			include_spip('inc/headers');
			redirige_par_entete(parametre_url('?exec=articles', 'id_article', $id_article, '&'));
			exit();
		}

	//--------- page + formulaire ---------
	$commencer_page = charger_fonction('commencer_page', 'inc');
	echo $commencer_page('Suivi des inscriptions', '', '');

	echo '<br />',gros_titre('Suivi des inscriptions');

	echo debut_gauche('', true);
	echo debut_boite_info(true);
	echo 'Suivi des inscriptions<br /><br />Explications',"\n";
	echo fin_boite_info(true);

	echo debut_droite('', true);
	echo debut_cadre_relief('', true, '', '');

	$contexte['id_article'] = $id_article;
	$contexte['id_auteur'] = $id_auteur;
	$contexte['idact'] = $idact;
	$contexte['titre'] = $titre;
	$contexte['idper'] = $idper;
	$contexte['nom'] = $nom;
	$contexte['prenom'] = $prenom;
	$contexte['inscrit'] = $inscrit;
	$contexte['statutsuivi'] = $statutsuivi;
	$contexte['date_suivi'] = $date_suivi;
	$contexte['heure_suivi'] = $heure_suivi;
	$contexte['editable'] = ' ';

	$milieu = recuperer_fond("prive/form_suivi", $contexte);
	echo pipeline('editer_contenu_objet',array('args'=>array('type'=>'auteurs_article','contexte'=>$contexte),'data'=>$milieu));

	echo fin_cadre_relief(true);
	echo fin_gauche();
	echo fin_page();
}
?>
