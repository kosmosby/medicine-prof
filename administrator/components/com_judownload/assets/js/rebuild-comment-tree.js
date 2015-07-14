jQuery(document).ready(function ($) {
	Joomla.submitbutton = function (task) {
		if (task == 'tools.rebuildCommentTree') {
			var limitstart = 0;
			var limit = 10;
            var lft = 1;

			function resizeAjax(limitstart, limit, lft) {
				$.ajax({
					url    : "index.php?option=com_judownload&task=tools.rebuildCommentTree",
					data   : {'limitstart' : limitstart, 'limit' : limit, 'lft' : lft},
					async  : true,
                    dataType : 'json',
					success: function (res) {
						$(".bar").width(res['percent'] + '%').html(res['percent'] + '%');
						limitstart = parseInt(limitstart) + parseInt(limit);
                        lft = res['lft'];
						if (res['percent'] < 100) {
							resizeAjax(limitstart, limit, lft);
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
			resizeAjax(limitstart, limit, lft);
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