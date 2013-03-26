<?php
/**
* Exporte un jeu de donnée en format Excel
*/
class data2Excel
{
	// On déclare quelques fonctions utile
	private $filename = 'myExcelFile';
	private $dataExcel = '';
	private $directory = '';

	function __construct($data, $fileNameByUser, $directoryByUser)
	{
		// On ajoute les options de l'utilisateur
		$this->filename = $fileNameByUser;
		$this->directory = $directoryByUser;

		// On ajoute le format au filename
		$this->filename .= '.xls';

		// On convertit les donnée. Basé sur le code ici => http://www.the-art-of-web.com/php/dataexport/
		if (!is_array($data)) throw new Exception('Vous devez passer un tableau multidimentionnel de données');
		else {
			$flag = false;
			foreach($data as $row) {
				if(!$flag) {
					// display field/column names as first row
					$this->dataExcel .=  implode("\t", array_keys($row)) . "\r\n";
					$flag = true;
				}
				if (!array_walk($row, array(&$this, 'cleanData'))) throw new Exception('Problème pour nettoyer le tableau');
				
				$this->dataExcel .= implode("\t", array_values($row)) . "\r\n";
			}

			// On crée le Fichier xls
			$this->createFile($this->dataExcel);
		}
	}

	/**
	* Fonction qui nettoye une chaine de caractère pour en faire une cellule excel
	*/
	public function cleanData($str)
	{
		$str = preg_replace("/\t/", "\\t", $str);
    	$str = preg_replace("/\r?\n/", "\\n", $str);
    	
    	return $str;
	}

	/**
	* Fonction qui crée le fichier dans le dossier choisi.
	*/
	public function createFile($data)
	{
		$t = fopen($this->directory.$this->filename, 'w+');
		fwrite($t, $data);
		fclose($t);
	}

	/**
	* Fonction qui envoie le fichier à l'utilisateu
	*
	* $typeOfSend:
	* 				1 => Renvoie l'URL du fichier
	*				2 => Renvoie le fichier
	*/
	public function sendFile()
	{
		return $GLOBALS['meta']['adresse_site'].'/IMG/xls/'.$this->filename;
	}
}
?>