jQuery(document).ready(function ($) {
	Joomla.submitbutton = function (task) {
		if (task == 'tools.rebuildRating') {
			var cats = $('select[name="catlist[]"]').val(),
				criteriagroups = $('select[name="criteriagroups[]"]').val(),
				limit = $(".inputbox").val(),
				start = 0;

			$("#process_info").css("display", "block");

			function process(start, limit) {
				$.ajax({
					type    : 'GET',
					data    : {start: start, limit: limit, cats: cats, criteriagroups: criteriagroups},
					url     : "index.php?option=com_judownload&task=" + task,
					dataType: "json",
					cache   : false,
					success : function (response) {
						$("#processed").html(response['processed']);
						$("#total_docs").html(response['total']);

						var percent = Math.floor((response['processed'] / response['total']) * 100);
						if (percent >= 100) {
							$(".progress").removeClass('active');
							$("#processed").text(response['total']);
							$("#bar").width(100 + '%');
							return false;
						}
						else {
							$("#bar").width(percent + '%');
							process(response['processed'], limit);
						}
					}
				});
			}

			$("#bar").width('0%');
			$(".progress").addClass('active');
			process(start, limit);
		} else {
			Joomla.submitform(task);
		}

		return false;
	};

	function submitbutton(task) {
		return Joomla.submitbutton(task);
	}
});