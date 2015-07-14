jQuery(document).ready(function ($) {
	function importImagesAjax(start) {
		$.ajax({
			type    : "GET",
			data    : {start: start},
			dataType: 'json',
			url     : "index.php?option=com_judownload&task=tools.batchImportImages",
			success : function (response) {

				var percent = Math.floor((response['processed'] / response['total']) * 100);

				$("#process_info").show();
				$("#processed").html(response['processed']);
				$("#total").html(response['total']);

				//display errors
				if (typeof response["errors"] != "undefined") {
					var errors = response["errors"];
					for (i = 0; i < errors.length; i++) {
						$("#import_image_errors").append('<li style="color:red;">' + errors[i] + '</li>');
					}
				}

				if (percent >= 100) {
					$("#processed").html(response['total']);
					$("#bar").width(100 + '%');

					return false;
				}
				else {
					$("#bar").width(percent + '%');

					importImagesAjax(response['processed']);
				}
			}
		});
	}

	importImagesAjax(0);
});
