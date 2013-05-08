/*
*    Fichier javascript qui gère les champs RA et SCIR dans les formulaire des actions.
*    Lorsque l'on remplit les champs RA il complète automatiquement les champs SCIR si ces derniers sont vide.
*
*    Didier - Vertige ASBL
*    http://www.vertige.org/
*    http://p.henix.be/
*/


jQuery(document).ready(function($) {
    
    // Tableau des champs sans le suffixe
    var champs = [
                    "#dates_",
                    "#titre_",
                    "#nb_formateurs_",
                    "#nb_part_",
                    "#par_unite_",
                    "#type_unite_",
                    "#nb_unite_"
                    ];
    
    // On boucle sur tout les champs
    $.each(champs, function (index, value) {
        // On live le blur et on pointe sur le champs + le sufixe RA
        $(value+"ra").live("blur", function () {
            // On récupère la valeur du champ.
            var value_ra = $(this).val();
            
            // Si la valeur n'est pas vide.
            if (value_ra) {
                // On test la valeur correspondante dans les champs SCIR.
                var value_scir = $(value+"scir").val();

                // Si la valeur SCIR est vide on prend celle en RA.
                if (!value_scir) $(value+"scir").val(value_ra);
            }
        });
    });
});