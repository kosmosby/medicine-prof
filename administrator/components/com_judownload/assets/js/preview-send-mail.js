jQuery(document).ready(function ($) {
	if ($("#select_recipients").val() == "other") {
		$(".select_another_email").show();
	}

	$("#select_recipients").change(function () {
		if ($(this).val() == "other") {
			$(".select_another_email").show();
		} else {
			$(".select_another_email").hide();
		}
	});
});
