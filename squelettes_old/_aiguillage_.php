<?php /*

	ID			aiguillage
-------------------------------------------------
	1			secteur : Accueil			redirection vers sommaire
	10			secteur : Education permanente		redirection vers mot 3611
	11			secteur : Service de Jeunesse		redirection vers mot 3612
	9			secteur : Hors menu
	15			rubrique : Lettres d’infos
	14			rubrique : News

	21			article : données personnelles
	3			article : inscription action > pour rubrique actions (id_mot 5) et meta-actions (id_mot 3627)
	14			article : moteur
	13			article : devenir adhérent
	12			article : pied de page
	11			article : page introuvable
	10			article : abonnement
	9			article : recherche
	8			article : plan du site
	6			article : connexion
	62			article : mais aussi (à droite)
	75			article : spip_pass
-------------------------------------------------

formulaires/ adherer_auteur.php, inscrire_auteur.php, spip_listes_inscription.html, gestion_abonnement.html :
liste de diffusion principale = premier id_liste de la langue

id_groupe	titre					tables_liees
---------	-----					------------
1			Statuts EP SJ
2			Diffusion
3			Statuts Cemea
4			Centres org
5			Localités
6			Dossiers
7			Lieux
8			Commanditaires
9			ASBLs
10			CAC
11			Secteurs
12			Affichage				rubriques
13			Type animation			articles
14			Type formation			articles
15			Profil					articles,rubriques
16			Association				rubriques

-------------------------------------------------------
				Data structure
Voir aussi plugins/champs_extras2/cextras_pipelines.php
Voir aussi squelettes/extra-saisies

-------------- personnes ------------------
id		(non id_auteur de spip)
idper		(année + numéro)
typepart		(codes)
archive_per
codescourtoisie		(codes)
nom		(=nom)
prenom
fonction
nom_court_institution
nom_long_institution
description_institution
date_naissance
adresse
codespostaux
localite
pays		(codes)		(non)
tel1
tel2
gsm1
gsm2
fax1
fax2
email1
email2
remarque		(=bio)
date_modif
date_creation
statut_form_cemea		(codes)
statut_anim_cemea		(codes)
statut_ep		(codes)
statut_sj		(codes)
personne_reference
centre_reference
source_reference	(non)
envoi_diffusion
date_debut_diffusion
date_fin_diffusion
adherent

diffusion =
programme,vacloisir,animateur,animateur_professionnel,coordinateur,formateur,prime_enfance,enseignement,extrascolaire,delegue_eleve,es_eo,handicap,trav_social,
sante,pedagogie,jeune_enfant,audio_visuel,dramatique_corporel,oral_ecrit,manuel,milieu,musique,jeu_physique,technique_scientifique,soi_groupe,gestion_collectif

-------------- actions ------------------
chapo : intros visibles si sq rubrique		(=chapo)
texte : présentation de l'action			(=texte)

id		(non id_article de spip)
archive_act
idact	(concat année ep/sj num  form séq ?)
dossier		(editable)
annee		(dans idact)
num_act		(dans idact)
sequence		(dans idact)
semestre
max_part
assurance
date_debut
dates_ra	(+s : du texte)
titre_ra
lieux		(non)
lieu
commanditaire		(editable)
id ra		(non)
nb_formateurs_ra
nb_part_ra
par_unite_ra
type_unite_ra
nb_unite_ra
insertion
cocof_rec
subord
dates_scir	(texte)
titre_scir
nb_part_scir
par_unite_scir
type_unite_scir
nb_unite_scir
date_c
date_m
date_p		(non)
type_act		(codes)
residentiel		(codes)
centre_organisateur		(codes)
asbl		(codes)
cac		(codes) non
code_fonctionnel		(codes)
secteurs				(codes)
cursus_formation		(codes)
diffusion		(editable cases)		(centres d'interet)
dossiers		(non déjà dossier)
--------------------------------

-------------- suivi ------------------
Dans "spip_auteurs_articles" :
statutsuivi
date_suivi
heure_suivi
-------------------------------------------------------



	Infos installation (chercher "richir" et/ou "vertige" mot complet)
-------------------------
+ voir  squelettes/plugins_modif/infos_plugins_modif.php
-------------------------

intranet :
docs/intrapw/.htpw_modif : mise à jour à la main

*/ ?>
