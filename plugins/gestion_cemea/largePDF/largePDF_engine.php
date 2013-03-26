<?php
session_start();

include_once('largePDF.php');

/* On appel la classe, car elle contient aussi le moteur :D */
$pdf = new largePDF();

/* On créer un seul PDF */
$statut_PDF = $pdf->create_pdf();

/* On récupère les informations de création */
$nb_pdf = $pdf->getNbQueue();
$nb_pdf_cree = $pdf->getNbFile();

if ($nb_pdf == $nb_pdf_cree) echo '<a href="?exec=generer_etiquette&merge=1">Télécharger le PDF</a>';
else echo $nb_pdf_cree.'/'.$nb_pdf;
?>