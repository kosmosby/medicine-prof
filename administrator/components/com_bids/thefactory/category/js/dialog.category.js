/*
---

name: CB.CategoryDialog

description: A Jx.Dialog subclass for editing and creating new categories.

requires:
 - base
 - jxlib/Jx.Dialog
 - jxlib/Jx.Form
 - jxlib/Jx.Field.Text
 - jxlib/Jx.Field.Textarea
 - jxlib/Jx.Toolbar
 - jxlib/Jx.Button

provides: [CB.CategoryDialog]

...
 */

CB.CategoryDialog = new Class({

    Extends: Jx.Dialog,
    Family: 'Browser.CategoryDialog',
    
    options: {
        width: 400,
        height: 250,
        close: false,
        collapse: false,
        data: null,
        id: 'categoryEditDialog',
        record: null,
        resize: true
    },
    
    edit: false,
    
    parameters: ['options'], //record to be null for new records
    
    render: function(){
        
        var record = this.options.record;
        
        //create the form. Pre-populate it if we have a record on hand
        this.form = new Jx.Form({
            name: 'categoryForm',
            formClass: 'jxFormBlock',
            id: 'catform'
        });
        
        //add fields for name and description
        this.fldName = new Jx.Field.Text({
            id: 'catName',
            name: 'catName',
            label: {set: 'CB', key: 'dialog', value: 'form.label.category'},
            required: true
        }).addTo(this.form);
        
        this.fldDesc = new Jx.Field.Textarea({
            id: 'catDescription',
            name: 'catDesc',
            label: {set: 'CB', key: 'dialog', value: 'form.label.description'},
            required: true
        }).addTo(this.form);
        
        if (record !== null && record !== undefined) {
            this.fldName.setValue(record.get('name'));
            this.fldDesc.setValue(record.get('description'));
            this.options.label = this.getText({set: 'CB', key: 'dialog', value: 'label.edit'}); 
            this.edit = true;
        } else {
            this.options.label = this.getText({set: 'CB', key: 'dialog', value: 'label.new'}); 
        }
        
        //TODO: add a form validator to make sure both fields are filled in.
        
        this.options.content = this.form;
        //buttons
        this.buttons = new Jx.Toolbar({position: 'bottom', scroll:false, align:'center'});
        this.buttons.add(
            new Jx.Button({
                label: {set: 'CB', key: 'dialog', value: 'button.save'},
                onClick: this.save.bind(this)
            }),
            new Jx.Button({
                label:{set: 'CB', key: 'dialog', value: 'button.cancel'},
                onClick: this.cancel.bind(this)
            })
        );
        
        this.options.toolbars = [this.buttons];
        this.parent();
    },
    
    save: function(){
        var record = this.options.record;
        this.status = 'success';
        
        //populate the data back to the record
        if (record !== null && record !== undefined) {
            record.set('name',this.fldName.getValue());
            record.set('description',this.fldDesc.getValue());
        } else {
            this.name = this.fldName.getValue();
            this.description = this.fldDesc.getValue();
        }
        
        this.close();
    },
    
    cancel: function(){
        this.status = 'cancel';
        this.close();
    }
    
});