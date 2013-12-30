<?php
/*
*    Fichier fichier va chercher les documents PDF du dossier
*   IMG/gestion pour les envoyer au navigateur.
*   Ce dossier est protégé le reste du temps par htaccess.
*
*   Ce fichier attend un paramètre GET "fichier"
*
*    Didier - Vertige ASBL
*    http://www.vertige.org/
*    http://p.henix.be/
*/


if (!defined("_ECRIRE_INC_VERSION")) return;

function exec_get_file() {

    // On récupère le fichier dans gestion.
    $file = _DIR_IMG.'gestion/'._request('fichier');

    // Si le fichier existe, l'envoie
    if (file_exists($file) and _request('fichier')) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        ob_clean();
        flush();
        readfile($file);
        exit;
    }
    // Sinon, il faut renvoyer une erreur.
    else {
        include_spip('inc/minipres');
        echo minipres("Ce fichier n'existe pas.");
    }
}