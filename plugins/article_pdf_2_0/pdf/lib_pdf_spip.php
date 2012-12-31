<?php

/**
 * class PDF_SPIP extends PDF : 
 */
 
 
class PDF_SPIP extends PDF
{
//Private properties
var $copyright;               //current page number

// haut, gauche,  bas, droite
function SetAllMargins($TopMargin, $LeftMargin, $BottomMargin, $RightMargin)
{
	// gauche, haut, droite
	$this->SetMargins($LeftMargin,$TopMargin,$RightMargin);
	
	// bas
	$this->SetAutoPageBreak(auto, $BottomMargin*3/2);
}


function Header()
{
	global $titre ;
	
	$this->SetY($this->tMargin/2);
	$this->SetLineWidth(0.3);
	$this->Line($this->lMargin - 3, $this->tMargin, $this->w - $this->rMargin + 3, $this->tMargin);
	
	//Police helvetica gras 8
	$this->SetFont('helvetica','B',12);
	$this->SetTextColor(0,0,0);

	$this->Cell(0,$this->tMargin/2, $titre ,0,0,'C');
	
	// $this->tMargin = marge du haut, définie dans FPDF
	$this->Ln(9);
}


/* /// Pied de page du document) 
/* ///////////////////////////// */
function Footer()
{
	global $conf_nom_site , $conf_url_site  ;
	
	$this->SetY(-$this->bMargin/2);	
	$this->SetLineWidth(0.3);
	$this->Line($this->lMargin - 3, $this->GetY(), $this->w - $this->rMargin + 3, $this->GetY());
	
	
	//Police helvetica 8	
	$this->SetFont('helvetica','I',8);
	$this->SetTextColor(0,0,0);

	// Copyright
	$this->Cell(0,6,"Copyright © ".$conf_nom_site ,0,0,'L',0,$conf_url_site );
	
	//Numéro de page
	$this->SetX($this->w-$this->rMargin*2-5);
	$this ->Cell(0,6,'Page '.$this->PageNo().'/{nb}', 0, 1, 'C');
}

function GenerateTitlePage()
{
	global $site, $rubrique, $yahoo, $surtitre, $titre, $soustitre;
	global $logo_site, $logo_fichier, $logo_fichier_x, $logo_fichier_y;
	global $auteur, $descriptif;
	global $conf_url_site;
	global $DateParution,$DateMiseEnLigne;
	
	
	// En-tête
	if (file_exists($logo_site))
	{
		$this->Image($logo_site,$this->rMargin+3,$this->tMargin+6,52,30); //--- richir vertige
	}
	
	$this->SetFont('times','',12);
	$this->SetXY($this->rMargin+65,$this->tMargin+10);
	$this->MultiCell(0,5,"Extrait du " . $site);
	
	$this->SetXY($this->rMargin+65,$this->tMargin+18);
	$this->PutLink($conf_url_site,$conf_url_site);
	
	
	//Surtitre (type du document)
	$this->unhtmlentities($surtitre);
	$this->SetXY(20,92);
	$this->SetFont('courier','B',14);
	$this->MultiCell(170,6,$surtitre,0,'C',0);
	
	
	//Titre centré
	$this->SetXY(20,100);
	$this->SetFont('helvetica','B',32);
	$this->unhtmlentities($titre);
	$this->MultiCell(170,20,$titre,0,'C',0);
	
	
	// Rubriques
	$this->Ln(2);
	$this->SetFont('helvetica','',8);
	$this->MultiCell(0,5,$yahoo,0,'C',0);
	
	// Logo
	if ($logo_fichier) {
		$logo_fichier_x >>= 2;
		$logo_fichier_y >>= 2;
		if ($logo_fichier_x > 160) {
			$ratio = $logo_fichier_x / 160.0;
			$logo_fichier_y = (int) ($logo_fichier_y / $ratio);
			$logo_fichier_x = 160;
		}
		$x = $this->GetX();
		$y = $this->GetY();
		$this->Image($logo_fichier,15,$y,$logo_fichier_x,$logo_fichier_y,'','','0');
		$this->SetXY($x + $logo_fichier_x, $y + $logo_fichier_y);
		$this->Ln();
   	}

	//Dates
	$this->SetFont('times','',10);
	
	if ($DateMiseEnLigne) 
	{
		$this->SetXY(110,184);
		$DateMiseEnLigne = $this->unhtmlentities($DateMiseEnLigne);
		$this->MultiCell(0,6,"Date de mise en ligne : $DateMiseEnLigne",0,'L',0);
	}
	
	if ($DateParution) 
	{
		$this->SetXY(110,190);
		$DateParution = $this->unhtmlentities($DateParution);
		$this->MultiCell(0,6,"Date de parution : $DateParution",0,'L',0);
	}
	

	// Descriptif 	
	if ($descriptif)
	{
		
		$this->SetFont('helvetica','B',10) ;
		$this->SetXY($this->rMargin+5,220);
		$this->SetFont('helvetica', 'BU', 10);
		$this->Write(5, 'Description :');
		$this->Ln();
		$this->SetFont('times', '', 8);
		$this->WriteHTML($descriptif,5) ;
	}
	
	if ($this->copyright)
	{
		$this->SetXY(45,250);
		$this->SetFont('times', 'B', 10);
		$this->MultiCell(120,8,$this->copyright,'TB','C',0);
	}
}

function GenerateText()
{
 	global $texte, $chapo, $ps, $notes ;
		
	$this->SetFont('helvetica');
	if ($chapo)
	{
		// Chapeau
		$this->SetFont('times','B',13);
		$this->WriteHTML($chapo,5);
		$this->Ln(12);
	}
	
	//Texte - justifie
	$this->SetFont('helvetica','',10);
	$this->WriteHTML($texte,5);
	$this->Ln(12);
	if ($ps) 
	{
		//ps
		$this->SetFont('','I',8);
		$this->WriteHTML("Post-scriptum : ",4);
		$this->WriteHTML($ps,4);
		$this->Ln(8);
	}
	if ($notes) {
		//notes
		$this->SetFont('','',8);
		$this->WriteHTML($notes,3);
		$this->Ln();
	}
}

function t_GenerateText($k) { //--- richir vertige
 	global $t_titre, $t_logo_fichier, $t_logo_fichier_x, $t_logo_fichier_y, $t_texte, $t_descriptif, $t_chapo, $t_ps, $t_notes ;
		
	//Titre centré
	$this->SetFont('helvetica','B',20);
	$this->unhtmlentities($t_titre[$k]);
	$this->WriteHTML($t_titre[$k],5);
	$this->Ln(12);

	// Logo
	if ($t_logo_fichier[$k]) {
		$t_logo_fichier_x[$k] >>= 2;
		$t_logo_fichier_y[$k] >>= 2;
		if ($t_logo_fichier_x[$k] > 160) {
			$ratio = $t_logo_fichier_x[$k] / 160.0;
			$t_logo_fichier_y[$k] = (int) ($t_logo_fichier_y[$k] / $ratio);
			$t_logo_fichier_x[$k] = 160;
		}
		$x = $this->GetX();
		$y = $this->GetY();
		$this->Image($t_logo_fichier[$k],15,$y,$t_logo_fichier_x[$k],$t_logo_fichier_y[$k],'','','0');
		$this->SetXY($x + $t_logo_fichier_x[$k], $y + $t_logo_fichier_y[$k]);
		$this->Ln();
   	}

	// Descriptif 	
	if ($t_descriptif[$k]) {
		$this->SetFont('helvetica', 'BU', 10);
		$this->SetFont('times', '', 8);
		$this->WriteHTML($t_descriptif[$k],5) ;
		$this->Ln();
	}

	// Chapeau
	if ($t_chapo[$k])
	{
		$this->SetFont('helvetica');
		$this->SetFont('times','B',13);
		$this->WriteHTML($t_chapo[$k],5);
		$this->Ln(12);
	}
	
	//Texte - justifie
	if ($t_texte[$k]) {
		$this->SetFont('helvetica','',10);
		$this->WriteHTML($t_texte[$k],5);
		$this->Ln(12);
	}
	//ps
	if ($t_ps[$k]) 
	{
		$this->SetFont('','I',8);
		$this->WriteHTML("Post-scriptum : ",4);
		$this->WriteHTML($t_ps[$k],4);
		$this->Ln(8);
	}
	//notes
	if ($t_notes[$k]) {
		$this->SetFont('','',8);
		$this->WriteHTML($t_notes[$k],3);
		$this->Ln();
	}
}

function BuildDocument()
{
	global $t_titre;

	$this->AddPage();
	$this->GenerateTitlePage();

	$this->AddPage();
	$this->GenerateText();

	if (isset($t_titre) && count($t_titre)) {
		$this->Ln(12);
		reset($t_titre); //--- richir vertige
		while (list($k) = each($t_titre))
			$this->t_GenerateText($k);
	}
	
	// On repasse en police à la bonne taille pour le nombre de pages.
	$this->SetFont('helvetica','I',8);
	$this->AliasNbPages();
}

function SetCopyright($copyright)
{
	$this->copyright=$copyright;
}


//
}

?>
