/*
---

name: CB.CategoryBrowser

description: A custom category browser. Designed and developed for Skepsis Consult.

requires:
 - base
 - jxlib/Jx.Object
 - jxlib/Jx.Dialog.Confirm
 - jxlib/Jx.Dialog
 - jxlib/Jx.Store
 - jxlib/Jx.Store.Strategy.Progressive
 - jxlib/Jx.Store.Strategy.Save
 - jxlib/Jx.Store.Strategy.Sort
 - jxlib/Jx.Store.Protocol.Ajax
 - jxlib/Jx.Plugin.Tree.Sorter
 - jxlib/Jx.Tree
 - CB.Locale.English.US
 - CB.Adaptor
 - CB.Record
 - CB.CategoryDialog

provides: [CB.CategoryBrowser]

images:
 - 

...
 */

CB.CategoryBrowser = new Class({
   
    Extends: Jx.Object,
   
    options: {
        urls: {
            insert: 'scripts/add.php',
            'delete': 'scripts/remove.php',
            update: 'scripts/update.php',
            read: 'scripts/refresh.php'
        },
        adaptor: {
            options: {
                monitorFolders: true,
                itemOptions: {
                    template: '<li class="jxTreeContainer jxTreeLeaf">' +
                        '<img class="jxTreeImage" src="'+Jx.aPixel.src+'" alt="" title="">' +
                        '<a class="jxTreeItem" href="javascript:void(0);">' +
                        //'<img class="jxTreeIcon" src="'+Jx.aPixel.src+'" alt="" title="">' +
                        '<span class="jxTreeLabel"></span>' +
                        '<span class="itemTools">' +
                        '<img class="toolEdit" src="' +Jx.aPixel.src+'" title="' + Jx.getText({set: 'CB', key: 'tree', value: 'tool.edit'}) + '">' +
                        '<img class="toolAdd" src="' +Jx.aPixel.src+'" title="' + Jx.getText({set: 'CB', key: 'tree', value: 'tool.add'}) + ' ">' +
                        '<img class="toolDelete" src="' +Jx.aPixel.src+'" title="' + Jx.getText({set: 'CB', key: 'tree', value: 'tool.delete'}) + '">' +
                        '</span></a></li>'
                },
                folderOptions: {
                    template: '<li class="jxTreeContainer jxTreeBranch">' +
                        '<img class="jxTreeImage" src="'+Jx.aPixel.src+'" alt="" title="">' +
                        '<a class="jxTreeItem" href="javascript:void(0);">' +
                        //'<img class="jxTreeIcon" src="'+Jx.aPixel.src+'" alt="" title="">' +
                        '<span class="jxTreeLabel"></span>' +
                        '<span class="itemTools">' +
                        '<img class="toolEdit" src="' +Jx.aPixel.src+'" title="' + Jx.getText({set: 'CB', key: 'tree', value: 'tool.edit'}) + '">' +
                        '<img class="toolAdd" src="' +Jx.aPixel.src+'" title="' + Jx.getText({set: 'CB', key: 'tree', value: 'tool.add'}) + '">' +
                        '<img class="toolDelete" src="' +Jx.aPixel.src+'" title="' + Jx.getText({set: 'CB', key: 'tree', value: 'tool.delete'}) + '">' +
                        '</span></a><ul class="jxTree jxListContainer"></ul></li>'
                },
                template: '{name}',
                left: 'lft',
                right: 'rht'
            }
        },
        container: null,
        id: 'CategoryBrowser'
    },
   
    initialize: function(options) {
        this.setOptions(options);
       
        //create the store, use the progressive strategy
        var parser = new Jx.Store.Parser.JSON();
        var progressive = new Jx.Store.Strategy.Progressive({
            dropRecords: false
        });
        
        this.saveStrategy = new Jx.Store.Strategy.Save({
            autoSave: true
        });
        
        this.sortStrategy = new Jx.Store.Strategy.Sort({
            sortOnStoreEvents: null,
            sortCols: [this.options.adaptor.options.left]
        });
        
        var protocol = new Jx.Store.Protocol.Ajax({
            parser: parser,
            urls: this.options.urls
        });
        //columns depend on adapter mode
        var columns = [
            {name: 'id',type: 'alphanumeric'},
            {name: 'name', type: 'alphanumeric'},
            {name: 'description', type: 'alphanumeric'},
            {name: 'lft', type: 'numeric'},
            {name: 'rht', type: 'numeric'}
        ];
        
        this.store = new Jx.Store({
            protocol: protocol,
            strategies: [progressive, this.saveStrategy, this.sortStrategy],
            record: CB.Record,
            columns: columns
        });
        
        //create the Adaptor
        this.options.adaptor.options.store = this.store;
        this.adaptor = new CB.Adaptor(this.options.adaptor.options);

        //create the sortable plugin
        var sorter = new Jx.Plugin.Tree.Sorter();
        //create the tree (pass the adaptor and plugin)
        this.tree = new Jx.Tree({
            parent: this.options.container,
            id: this.options.id,
            plugins: [this.adaptor, sorter]
        });
        
        //add element delegation for tools
        this.tree.container.addEvents({
            'click:relay(.toolEdit)': this.editCategory.bind(this),
            'click:relay(.toolDelete)': this.removeCategory.bind(this),
            'click:relay(.toolAdd)': this.addCategory.bind(this)
        });
        
        //add event for sorting
        this.tree.addEvent('jxTreeSortDone',this.onSortFinished.bind(this));
        
        //call the adaptor's firstLoad() to get the data and populate the tree
       this.adaptor.firstLoad();
       
    },
    

    editCategory: function (e, el) {
        //pop up a dialog w/prefilled name and description
        e.stop();
        this.tree.setHoldEvents(true);
        var item = $jx(e.target),
            pk = document.id(item).retrieve('pk').toInt(),
            record = this.store.getRecord(this.store.findByColumn('primaryKey',pk)),
            dlg = new CB.CategoryDialog({
                record: record,
                onClose: this.finishEdit.bind(this),
                destroyOnClose: true
            });
        
            
        dlg.show();
        return false;
            
    },
    
    finishEdit: function(dlg){
        this.saveStrategy.save();
        this.tree.setHoldEvents(false);
    },
    
    addCategory: function (e) {
        //pop up dialog asking for name and description.
        e.stop();
        this.tree.setHoldEvents(true);
        var item = $jx(e.target),
            pk = document.id(item).retrieve('pk').toInt(),
            dlg = new CB.CategoryDialog({
                onClose: this.finishAdd.bind(this),
                destroyOnClose: true
            });
        dlg.parentPK = pk;
        dlg.show();
        
    },
    
    finishAdd: function(dlg) {
        if (dlg.status !== "cancel") {
            var data = {
                name: dlg.name,
                description: dlg.description
            },
            fn = function(){
                this.saveStrategy.activate();
                this.store.removeEvent('storeChangesCompleted',fn);
                this.tree.setHoldEvents(false);
            }.bind(this);
            
            this.saveStrategy.deactivate();
            this.store.addEvent('storeChangesCompleted',fn);
            this.store.addRecord(data,'bottom',true);
            this.saveStrategy.save({data: {parent: dlg.parentPK}});
        }
    },
    
    
    
    removeCategory: function (e) {
        //pop up confirmation dialog
        e.stop();
        this.tree.setHoldEvents(true);
        var item = $jx(e.target),
            pk = document.id(item).retrieve('pk'),
            i = this.store.findByColumn('primaryKey',pk),
            dlg = new Jx.Dialog.Confirm({
                label: {set: 'CB', key: 'remove', value: 'dialog.label'},
                image: 'images/delete.png',
                question: this.getText({set: 'CB', key: 'remove', value: 'question.part1'}) + this.store.get('name',i) + this.getText({set: 'CB', key: 'remove', value: 'question.part2'}),
                onClose: this.doRemove.bind(this),
                destroyOnClose: true
            });
        dlg.item = item;
        dlg.index = i;
        dlg.show();
        //TODO: localize the message here.
    },
    
    doRemove: function(dlg, value){
        if (value) {
            //all we need to do is delete from the store and the store/adaptor
            //will do the rest.
            this.store.deleteRecord(dlg.index);
        }
        this.tree.setHoldEvents(false);
    },
    
    onSortFinished: function(item, previous){
        //previous is the new item this comes after
        //if previous is null then this is the first item in the list now
        var data = {
            moved: document.id(item).retrieve('pk').toInt(),
            move: true
        };
        if (previous === null || previous === undefined) {
            data.parent = document.id(item.owner).retrieve('pk').toInt();
            data.previous = -1;
        } else {
            data.previous = document.id(previous).retrieve('pk').toInt();
        }
        //all we need to do is send the primaryKeys of both to the server
        //have it make the move,
        //and then force the adaptor to do a refresh once it returns
        var request = new Request.JSON({
            data: data,
            url: this.options.urls.update,
            onSuccess: function(){
                this.adaptor.refresh();
            }.bind(this)
        });
        request.send();
        
    }
});