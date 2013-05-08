jQuery(document).ready(function($) {
    
    $("#titre_ra").blur(function () {
        // On récupère la valeur du champ.
        var value_ra = $(this).val();
        
        // Si la valeur n'est pas vide.
        if (value_ra) {
            // On test la valeur correspondante dans les champs SCIR.
            var value_scir = $("#titre_scir").val();

            // Si la valeur SCIR est vide on prend celle en RA.
            if (!value_scir) $("#titre_scir").val(value_ra);
        }

    })
});