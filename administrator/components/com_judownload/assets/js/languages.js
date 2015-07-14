//@todo translate ..
//should move this file to model/form
jQuery(document).ready(function ($) {
	$('#add-language').click(function () {
		var body = $('tbody');
		var html = '<tr class="add">'
			+ '<td><i class="icon-new"></i></td>'
			+ '<td><input type="text" class="keylanguage" name="keys[]" size="80" /></td>'
			+ '<td><input type="text" class="input-xlarge" name="" size="80" /></td>'
			+ '<td class="center"><div><a href="#" class="btn btn-mini btn-success save-language-new" ><i class="icon-save"></i> Save</a> <a href="#" class="btn btn-mini btn-danger remove-language-new" ><i class="icon-remove"></i> Remove</a></div></td>'
			+ '</tr>';
		body.append(html);

		return false;
	});

	$(document).on('keyup', '.keylanguage', function () {
		var val = $(this).val();
		var parent = $(this).parent();
		parent.next().children().attr("name", val);
	});

	$(document).on('click', '.remove-language', function () {
		var stt = confirm("Are you sure to delete this language string?");
		if (stt == true) {
			var site = $('#site').val();
			var file = $('#files').val();
			var lang = $('#language').val();

			var input = $(this).closest('td').children()[1];
			var valInputKey = $(input).attr('name');
			var objRemove = $(this).closest('tr');
			$.ajax({
				type : "POST",
				url  : "index.php?option=com_judownload&task=languages.removeAjax",
				data : "key=" + valInputKey + "&site=" + site + "&file=" + file + "&lang=" + lang,
				async: true
			}).done(function (data) {
				if (data == 'Remove successfully!') {
					objRemove.remove();
				} else {
					alert('Error, Please try again!')
				}
			});
		}
		return false;
	});

	$(document).on('click', '.save-language-new, .save-language', function () {
		var originalValue, inputValue, key;
		var site = $('#site').val();
		var file = $('#files').val();
		var lang = $('#language').val();
		var parent = $(this).closest('td');
		if ($(this).hasClass('save-language')) {
			var input = parent.children()[1];
			key = $(input).attr('name');
			inputValue = $(input).val();
			originalValue = (lang != 'en-GB') ? parent.prev()[0].innerText : '';
		} else {
			inputValue = parent.prev().children('input').val();
			key = parent.prev().prev().children('.keylanguage').val();
		}

		if (inputValue != '' && key != '') {
			$.ajax({
				type : "POST",
				url  : "index.php?option=com_judownload&task=languages.saveAjax",
				data : "key=" + key + "&value=" + inputValue + "&site=" + site + "&file=" + file + "&lang=" + lang,
				async: true
			}).done(function (data) {
				if (data == 'Save successfully!') {
					var html = '<td style="text-align: left;">#</td>';
					html += '<td style="text-align: left;">' + key + '</td>';
					if (lang == 'en-GB') {
						html += '<td style="text-align: left;">' + inputValue + '</td>';
					} else {
						html += '<td style="text-align: left;">' + originalValue + '</td>';
					}
					html += '<td><input type="hidden" name="keys[]" value="' + key + '" />';
					html += '<input type="text" name="' + key + '" class="input-xlarge"  size="65" value="' + inputValue + '" />';
					html += '<a href="#" class="btn btn-mini btn-success save-language" ><i class="icon-save"></i> Save</a>';
					html += '<a href="#" class="btn btn-mini btn-danger remove-language"><i class="icon-remove"></i> Remove</a>';
				    html += '</td>';

					parent.parent().removeClass('add,row0,row1').addClass('new');
					parent.parent().html(html);
				} else {
					alert('Error, Please try again!')
				}
			});
		}
		return false;
	});

	$(document).on('click', '.remove-language-new', function () {
		var tdParent = $(this).closest('td');
		var valInputKey = tdParent.prev().prev().children('input').val();
		var valInputValue = tdParent.prev().children('input').val();
		if (valInputValue != '' || valInputKey != '') {
			var stt = confirm("Are you sure to delete this language string?");
			if (stt == true) {
				$(this).closest('tr').remove();
			}
		} else {
			$(this).closest('tr').remove();
		}

		return false;
	});
});