<?php
/* répertoire d'installation de la classe */
define('LARGEPDF_ROOT', str_replace(DIRECTORY_SEPARATOR, '/', realpath(dirname(__FILE__))));

/* définition du répertoire temporaire */
define('PDF_TMP_DIR', LARGEPDF_ROOT.'/tmp/');

/* On a besoin de l'URL du moteur PDF, histoire de pouvoir faire de l'Ajax correctement */
define('PDF_URL_ENGINE', 'http://localhost/Vertige/cemea/plugins/gestion_cemea/largePDF/largePDF_engine.php');

/* URL du rapport JSON */
define('PDF_URL_ENGINE_JSON', 'http://localhost/Vertige/cemea/plugins/gestion_cemea/largePDF/largePDF_engine.js');

/* On a besoin de l'URL du dossier temporaire pour pouvoir rediriger sur les fichiers cré */

define('PDF_TMP_DIR_URL', 'http://localhost/Vertige/cemea/plugins/gestion_cemea/largePDF/tmp/');

?>