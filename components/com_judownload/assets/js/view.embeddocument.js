function getFieldDisplay() {
    var select = document.getElementById('field_display');
    var field_display = [];
	var options = select && select.options;
	var opt;
	for (var i=0, iLen=options.length; i<iLen; i++) {
		opt = options[i];
		if (opt.selected) {
			field_display.push(opt.value || opt.text);
		}
	}

    return field_display;
}

function getOptions() {
	var optionDiv = document.getElementById('options');
	els = optionDiv.getElementsByTagName('input');
	options = [];
	for (i = 0; i < els.length; i++) {
		if (els[i].type == 'checkbox') {
			if (els[i].checked == true) {
				options[els[i].name] = 1;
			} else {
				options[els[i].name] = 0;
			}
		}
	}

	return options;
}

function InsertDocument(ename, docId) {
    var docIds = [],
        insert_str = [];
    if (docId) {
        docIds.push(docId);
    } else {
        var els = document.getElementsByName('cid[]');
        for (i = 0; i < els.length; i++) {
            if (els[i].checked == true) {
                docIds.push(els[i].value);
            }
        }
    }

    if (docIds.length > 0) {
        insert_str.push('{judownload');
        insert_str.push('document = "' + docIds.join('|') + '"');
        field_display = getFieldDisplay();
        if (field_display) {
	        insert_str.push('field_display="' + field_display.join('|') + '"');
        }
	    options = getOptions();
	    if (options) {
		    for (option in options) {
			    if (options.hasOwnProperty(option)) {
				    insert_str.push(option + '="' + options[option] + '"');
			    }
		    }
	    }
        insert_str.push(' }');
        window.parent.jInsertEditorText(insert_str.join(' '), ename);
        window.parent.SqueezeBox.close();
    } else {
        alert(Joomla.JText._('COM_JUDOWNLOAD_PLEASE_SELECT_DOCUMENT', 'Please select document'));
    }

    return false;
}