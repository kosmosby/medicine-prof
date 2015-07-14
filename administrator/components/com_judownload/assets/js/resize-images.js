jQuery(document).ready(function ($) {
	Joomla.submitbutton = function (task) {
		if (task == 'tools.resizeImages') {
            $('form input[name="task"]').val(task);
			var formValue = $('form').serialize();
			var limitstart = 0;
			var limit = $("#limit-img").val();

			function resizeAjax(limitstart) {
				$.ajax({
					url    : "index.php?option=com_judownload&task=tools.resizeImages",
					data   : formValue + '&limitstart=' + limitstart,
					async  : true,
					success: function (res) {
						$(".bar").width(res + '%').html(res + '%');
						limitstart = parseInt(limitstart) + parseInt(limit);
						if (res < 100) {
							resizeAjax(limitstart);
						}
						else {
							$(".progress").removeClass('active');
							$(".bar").text(Joomla.JText._('COM_JUDOWNLOAD_FINISHED', 'Finished'));
						}
					}
				})
			}

			$(".progress").addClass('active');
			$(".bar").css("width", "0%").text("0%");
			resizeAjax(limitstart);
		}
		else {
			Joomla.submitform(task);
		}

		return false;
	};

	function submitbutton(task) {
		return Joomla.submitbutton(task);
	}
});