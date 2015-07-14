
window.addEvent('domready', function() {
	['clickImage', 'clickFile'].each(function(event) {
		Files.app.grid.removeEvents(event);
		Files.app.grid.addEvent(event, function(e) {
			var target = document.id(e.target),
				node = target.getParent('.files-node-shadow') || target.getParent('.files-node'),
				row = node.retrieve('row');

			var url = Files.app.createRoute({
				option: 'com_docman', view: 'documents', format: 'json',
				storage_type: 'file', storage_path: row.path, routed: 0});
			new Request.JSON({
				url: url,
				onSuccess: function(response) {
                    var copy = Object.append({}, row);
                    copy.documents = response.entities;

                    copy.template = 'documents_list';
                    var render = kQuery(copy.render());

                    //Setting display to inline-block for the dynamic width/height to work, also adding css hooks
                    render.addClass('koowa modal-inspector');

                    render.appendTo(kQuery('body'));

                    render.find('a.document-link').click(function(e) {
                        e.preventDefault();

                        window.parent.open(Files.app.createRoute({
                            option: 'com_docman', view: 'document', format: 'html',
                            routed: 0, container: false,
                            id: kQuery(this).data('id')
                        }));
                    });

                    kQuery.magnificPopup.open({
                        items: {
                            src: render,
                            type: 'inline'
                        },
                        closeBtnInside: true
                    });
				}
			}).get();
		});
	});
	
	$$('a.toolbar').addEvent('click', function() {
		if (this.hasClass('unauthorized')) {
			return false;
		}
	});

	var enableButton = function(button) {
		document.id(button).getElement('a').removeClass('disabled');
	};
	var disableButton = function(button) {
		document.id(button).getElement('a').addClass('disabled');
	};
	
	var checkbox_dependents = $$('#toolbar-delete, #toolbar-create-documents, #toolbar-copy, #toolbar-move');
	checkbox_dependents.each(function(el) {
		disableButton(el);
	});
	
	Files.app.grid.addEvent('afterCheckNode', function() {
		var checked = Files.app.grid.nodes.filter(function(row) { return row.checked }),
			folders = Object.getLength(checked.filter(function(row) { return row.type == 'folder'})),
			files = Object.getLength(checked) - folders;

		if (files || folders) {
			enableButton('toolbar-delete');
            enableButton('toolbar-move');
            enableButton('toolbar-copy');

			if (files) {
				enableButton('toolbar-create-documents');
			}
		} else {
			checkbox_dependents.each(function(el) {
				disableButton(el);
			});
		}
		
	}.bind(this));

	Files.app.addEvent('afterNavigate', function() {
		checkbox_dependents.each(function(el) {
			el.getElement('a').addClass('disabled');
		});
	});
	
	Files.app.addEvent('uploadFile', function(row) {
		Files.app.grid.checkNode(row);
	});

	kQuery('#toolbar-create-documents').find('a').click(function(e) {
		e.preventDefault();

		var checked_files = Files.app.grid.nodes.filter(function(row) { return row.checked && row.type !== 'folder' }),
			paths = [];

		Object.each(checked_files, function(row) {
			paths.push(row.path);
		});

		if (!paths.length) {
			return;
		}
        /*
        Redirecting users to the document view is causing issues from documentation point of view.
        One button effectively has two functionalities with this.
        else if (paths.length === 1) {
            window.location.href = window.location.pathname +
                '?option=com_docman&view=document&storage_type=file&storage_path=' + encodeURIComponent(paths[0]);

            return;
        }*/
		
		var form = kQuery('<form />'),
			createField = function(name, value) {
				var field = kQuery('<input />');
	
		        field.attr('type', 'hidden');
		        field.attr('name', name);
		        field.attr('value', value);
		        
		        return field;
			};

	    form.attr('method', 'post');
	    form.attr('action', 'index.php?option=com_docman&view=files&layout=form');
	    form.append(createField('_method', 'GET'));

	    kQuery.each(paths, function(key, value) {
	        form.append(createField('paths[]', value));
	    });

	    kQuery(document.body).append(form);
	    form.submit();
	});

	var fileCountAdder = function() {
		if (Files.app.grid.layout !== 'details') {
			return;
		}

		var counts = {},
			requests = new Chain(),
			files = Files.app.grid.getFiles(),
			count = files.length,
			url = Files.app.createRoute({
				option: 'com_docman', view: 'documents', format: 'json',
                limit: 100,
                storage_type: 'file', routed: 0
			}),
		    request = new Request.JSON({
			    url: url,
                method: 'POST',
                data: {
                    _method: 'GET',
                    storage_path: []
                },
                onComplete: function() {
                    requests.callChain();
                },
                onSuccess: function(response) {
                    if (typeof response != 'object' || typeof response.entities != 'object') {
                        return;
                    }

                    Object.each(response.entities, function(row) {
                        if (!counts[row.storage_path]) {
                            counts[row.storage_path] = 0;
                        }
                        counts[row.storage_path]++;
                    });

                    Files.app.grid.nodes.each(function(row) {
                        var count = counts[row.path] || 0;
                        row.document_count = count;
                        var count_box = row.element.getElement('.file-count');
                        if (count_box) {
                            count_box.set('html', '<a href="#" class="navigate">'+count+'</a>');
                        }
                    });
                }
			});

			var i;
			for(i = 0; i < count; i += 20) {
				requests.chain(function() {
					request.options.data.storage_path = files.splice(0, Math.min(20, count));
					request.send();
					count -= 20;
				});
			}
			requests.callChain();
	};
	
	// If we already have files, run it
	if (Files.app.grid.getFiles()) {
		fileCountAdder();
	}
	
	Files.app.grid.addEvent('afterInsertRows', fileCountAdder);

    var attachCheckAllHandlers = function(){
        document.id('select-orphans').addEvent('click', function(e) {
            e.preventDefault();
            var check = false;
            Files.app.grid.nodes.each(function(row) {
                if (row.type !== 'folder' && !row.checked && row.document_count === 0) {
                    Files.app.grid.checkNode(row);
                    check = true;
                } else if(row.checked && (row.document_count !== 0 || row.type === 'folder')) {
                    Files.app.grid.checkNode(row);
                }
            });
            document.id('select-check-all').checked = check;
        });

        document.id('select-all').addEvent('click', function(e) {
            e.preventDefault();
            Files.app.grid.nodes.each(function(row) {
                if (!row.checked) {
                    Files.app.grid.checkNode(row);
                }
            });
            document.id('select-check-all').checked = true;
        });
        document.id('select-none').addEvent('click', function(e) {
            e.preventDefault();
            Files.app.grid.nodes.each(function(row) {
                if (row.checked) {
                    Files.app.grid.checkNode(row);
                }
            });
            document.id('select-check-all').checked = false;
        });
        document.id('select-check-all').addEvent('click', function(e){
            e.stopPropagation();
            var value = document.id('select-check-all').checked,
                grid = Files.app.grid,
                nodes = grid.nodes;

            Object.each(nodes, function(node) {
                if (value && !node.checked) {
                    grid.checkNode(node);
                } else if (!value && node.checked) {
                    grid.checkNode(node);
                }

            });
        });
    };
    if(Files.app.grid.layout == 'details') {
        attachCheckAllHandlers();
    }
    Files.app.grid.addEvent('afterRender', function() {
        if(Files.app.grid.layout == 'details')
        {
            attachCheckAllHandlers();
        }
    });

    var uploader = Files.app.uploader,
        create_documents = kQuery('<button />')
            .css({
                'margin-right': '10px',
                float: 'right',
                clear: 'none'
            })
            .text(Koowa.translate('Create documents'))
            .addClass('btn btn-primary disabled btn-create-documents') //btn-create-documents used to toggle show/hide
            .click(function(e) {
                e.preventDefault();

                if ($(this).hasClass('disabled')) {
                    return;
                }

                kQuery('#toolbar-create-documents').find('a').trigger('click');
            });

    kQuery('div.plupload_buttons').append(create_documents);

    // We only enable the button when the upload is complete
    uploader.bind('QueueChanged', function(uploader) {
        var has_checked_files = Files.app.grid.nodes.some(function(row) { return row.checked && row.type !== 'folder' });

        if (!has_checked_files) {
            create_documents.addClass('disabled');
        }
    });
    uploader.bind('UploadComplete', function(uploader) {
        var has_checked_files = Files.app.grid.nodes.some(function(row) { return row.checked && row.type !== 'folder' });

        if (uploader.total.uploaded && has_checked_files) {
            create_documents.removeClass('disabled');
        }
    });
});