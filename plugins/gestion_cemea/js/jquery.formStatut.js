jQuery(document).ready(function($) {
	// Si un statut change
	$(".autosubmit select").live("change", function () {
		// On demande confirmation du changement de statut.
		if (confirm("Etes-vous sur ?")) {
			// L'utilisateur confirme le changement, on change le statut en soumettant le formulaire
			$(this).parents(".autosubmit").submit();
		}
		else {
			// Si l'utilisateur annule le changement, on reset le formulaire pour que le statut ne change pas
			$(this).parents(".autosubmit")[0].reset();
		}
	});

	/*
		Invoquer ou révoquer le formulaire de payement rapide
	*/
	$(".openQuickPay").live("click", function () {
		$(this).next(".formulaire_payement_rapide").show();
		return false;
	});
	$(".close_form").live("click", function () {
		$(this).parent().hide();
		return false;
	});
});