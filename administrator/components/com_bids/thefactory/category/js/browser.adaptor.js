/*
---

name: CB.Adaptor

description: An adaptor class designed specifically for the requirements of the category browser

requires:
 - CB.Record
 - jxlib/Jx.Adaptor.Tree.MPTT
 - jxlib/Jx.Store
 - jxlib/Jx.Tree.Folder
 - jxlib/Jx.Tree.Item

provides: [CB.Adaptor]

...
*/ 
CB.Adaptor = new Class({

    Extends: Jx.Adaptor.Tree.Mptt,
    Family: 'CB.Adaptor',
    
    items: null,
    
    init: function(){
        this.parent();
        this.items = {};
        this.bound.refresh = this.refresh.bind(this);
        this.bound.update = this.update.bind(this);
        
        //grab a reference to the save strategy
        this.save = this.store.getStrategy('save');

        //connect to the changes completed event which is fired after any change 
        //is sent back to the website and something is returned.
        this.store.addEvent('storeChangesCompleted',this.bound.update);
        
    },
    
    update: function(obj){
        //this is where we will react to update/delete/add events and adjust the
        //tree accordingly.
        
        //obj contains a list of successful changes as well as those that failed.
        //as arrays of response objects
        console.log(obj);
        
        //for each object in successful changes, get the requestParams array and
        //get the record and the operation
        var record, op;
        obj.successful.each(function(resp){
            record = resp.requestParams[0];
            op = resp.requestParams[2];
            //now branch based on op
            if (op == 'delete') {
                this.processDelete(record);
            } else if (op == 'insert') {
                this.processAdd(record);
            } else if (op == 'update') {
                this.processUpdate();
            }
        },this);
        
    },
    
    processDelete: function(record) {
        //remove the item from the tree... no refresh of data needed
        var pk = record.get('primaryKey'),
            item = this.items[pk].treeItem;
        item.owner.remove(item);
        delete this.items[pk];
        this.refresh();
    },
    
    processUpdate: function() {
        this.refresh();
    },
    
    processAdd: function(record){
        this.refresh();
    },
        
    
    /**
     * APIMethod: refresh
     * Takes a node and removes the children from the store and tree, then
     * requeries the website for updated data
     */
    refresh: function () {
        //easiest thing to do is to simply create a new store, fill it from 
        //the website and then compare it to the current store updating/add any 
        //new records. TODO: how do we determine something has been deleted from the
        //server?
        
        store = new Jx.Store({
            protocol: this.store.protocol,
            strategies: [this.store.getStrategy('progressive')],
            record: CB.Record,
            columns: this.store.getColumns()
        });
        
        store.addEvent('storeDataLoaded',this.doRefresh.bind(this));
        store.load({node:-2});
    },
    
    doRefresh: function(store){
        this.store.getStrategy('save').deactivate();
        store.each(function(record,i){
            var index = this.store.findByColumn('primaryKey',record.get('primaryKey')),
                rec;
            
            if (index !== null) {
                rec = this.store.getRecord(index);
            
                //only do something if it's not equal and actually drawn on the screen
                if (rec.get('drawn') && !this.store.equals(rec,record)) {
                    var pk = rec.get('primaryKey');
                    obj = this.items[pk];
                    
                    
                    //so, the only thing that could be different that we care about at
                    //this point is if the label changed or if it's folder/item status
                    //changed
                    
                    //check the label
                    var label = store.fillTemplate(record,this.options.template,this.columnsNeeded);
                    if (label !== obj.treeItem.options.label) {
                        obj.treeItem.setLabel(label);
                    }
                    
                    rec.processData(record.asObject());
                    obj.record = rec;
                    var item;
                    //check the folder status
                    //if the right !== left + 1 then this is a folder
                    if (record.get(this.options.right) === record.get(this.options.left) + 1 &&
                        obj.treeItem instanceof Jx.Tree.Folder) {
                        //change to Jx.TreeItem
                        item = this.createItem(false,rec,index);
                    } else if (record.get(this.options.right) !== record.get(this.options.left) + 1 &&
                        !(obj.treeItem instanceof Jx.Tree.Folder)) {
                        //make it a folder  
                        item = this.createItem(true,rec,index);
                    } //otherwise no change needed
                    
                    if (item !== undefined && item !== null) {
                        document.id(item).store('pk', record.get('primaryKey')).store('jxAdaptor', this);
                        obj.treeItem.owner.replace(obj.treeItem, item);
                        obj.treeItem.destroy();
                        obj.treeItem = item;
                    }
                    
                }
            } else {
                //new record
                this.store.addRecord(record.asObject(), 'bottom');
            }
        },this);
        
        //check for deleted records by looping the original store and checking
        //for existing PK in the one we just got down.
        var remove = [];
        this.store.each(function(record){
            var idx = store.findByColumn('primaryKey',record.get('primaryKey'));
            
            if (idx === null || idx === undefined) {
                //obviously the record was removed on the server... remove it here
                var pk = record.get('primaryKey'),
                    item = this.items[pk].treeItem;
                item.owner.remove(item);
                delete this.items[pk];
                remove.push(pk);
            }   
        },this);
        
        if (remove.length > 0) {
            remove.each(function(pk){
                this.store.removeRecord(this.store.findByColumn('primaryKey',pk));       
            },this);
        }
        
        this.store.getStrategy('save').activate();
        this.fill();
    },
    
    /**
     * APIMethod: fill
     * This function will start at this.currentRecord and add the remaining
     * items to the tree.
     */
    fill: function () {
        var i,
            template,
            item,
            p,
            folder,
            sibIdx,
            options = this.options;

        if (this.busy == 'tree') {
            this.tree.setBusy(false);
            this.busy = 'none';
        } else if (this.busy == 'folder') {
            this.busyFolder.setBusy(false);
            this.busy = 'none';
        }
        this.tree.freeze();
        //sort records by this.options.left
        this.store.getStrategy('sort').sort();
        this.store.each(function(record,i){
            if (!record.get('drawn')) {
                item = this.createItem(this.hasChildren(i),record, i);
                document.id(item).store('pk', record.get('primaryKey')).store('jxAdaptor', this);
                record.set('drawn',true);
                this.items[record.get('primaryKey')] = {
                    treeItem: item,
                    record: record
                };
                //check for a parent
                if (this.hasParent(i)) {
                    //add as child of parent
                    p = this.getParentIndex(i);
                    folder = this.folders[p];
                    //find previous sibling, returns primaryKey
                    sibIdx = this.getPreviousSibling(i,p);
                    if (sibIdx) {
                        folder.add(item,this.items[sibIdx].treeItem);
                    } else {
                        folder.add(item,'top');
                    }
                } else {
                    //otherwise add to the tree itself
                    this.tree.add(item);
                }
            }
        },this);
        this.tree.thaw();
    },
    
    getPreviousSibling: function(index,parent) {
        var lft = this.store.get(this.options.left,index),
            rht = lft - 1,
            ret;
        
        this.store.each(function(record){
            if (rht == record.get(this.options.right)) {
                //this is our previous sibling
                ret = record.get('primaryKey');
            }
        },this);
        
        return ret;

    },
    
    createItem: function(folder,record,i){
        var item,
            options = this.options,
            template = this.store.fillTemplate(record,options.template,this.columnsNeeded);
        if (folder) {
            //add as folder
            item = new Jx.Tree.Folder(Object.merge({},options.folderOptions, {
                label: template
            }));

            if (options.monitorFolders) {
              item.addEvent('disclosed', this.checkFolder);
            }

            this.folders[i] = item;
        } else {
            //add as item
            item = new Jx.Tree.Item(Object.merge({},options.itemOptions, {
                label: template
            }));
        }
        return item;
    }
});