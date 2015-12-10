jQuery( document ).ready(function() {

    var initSelectableTree = function() {
        return jQuery('#treeview-selectable').treeview({
            data: defaultData,
            multiSelect: jQuery('#chk-select-multi').is(':checked'),
            onNodeSelected: function(event, node) {
                jQuery('#selectable-output').html('');
                jQuery('#selectable-output').prepend('<div class="streamMediaLeft activityContainerLogo media-left"><img src="'+node.image+'" align="left" class="cbImgPict cbThumbPict img-thumbnail"></div><div class="streamMediaBody activityContainerTitle media-body">' +  node.text + '</div>'  );
            },
            onNodeUnselected: function (event, node) {
                jQuery('#selectable-output').html('');
                //jQuery('#selectable-output').prepend('<p>' + node.text + ' was unselected</p>');
            }
        });
    };
    var jQueryselectableTree = initSelectableTree();

    var findSelectableNodes = function() {
        return jQueryselectableTree.treeview('search', [ jQuery('#input-select-node').val(), { ignoreCase: false, exactMatch: false } ]);
    };
    var selectableNodes = findSelectableNodes();

    jQuery('#chk-select-multi:checkbox').on('change', function () {
        console.log('multi-select change');
        jQueryselectableTree = initSelectableTree();
        selectableNodes = findSelectableNodes();
    });

    // Select/unselect/toggle nodes
    jQuery('#input-select-node').on('keyup', function (e) {
        selectableNodes = findSelectableNodes();
        jQuery('.select-node').prop('disabled', !(selectableNodes.length >= 1));
    });

    jQuery('#btn-select-node.select-node').on('click', function (e) {
        jQueryselectableTree.treeview('selectNode', [ selectableNodes, { silent: jQuery('#chk-select-silent').is(':checked') }]);
    });

    jQuery('#btn-unselect-node.select-node').on('click', function (e) {
        jQueryselectableTree.treeview('unselectNode', [ selectableNodes, { silent: jQuery('#chk-select-silent').is(':checked') }]);
    });

    jQuery('#btn-toggle-selected.select-node').on('click', function (e) {
        jQueryselectableTree.treeview('toggleNodeSelected', [ selectableNodes, { silent: jQuery('#chk-select-silent').is(':checked') }]);
    });


});


