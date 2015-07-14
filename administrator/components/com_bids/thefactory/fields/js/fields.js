window.addEvent('domready', function () {

    $$('input[name=db_name]').each(function(el){
            el.addEvent('change',function (){
                val=this.value;
                val=val.replace(/\s+/g,'_');
                val=val.replace(/[^a-zA-Z0-9_\-]+/g,'');
                val=val.toLowerCase(); 
                this.value=val;
            });
            el.addEvent('blur',function(){this.fireEvent('change')});
    });
    $$('select[name=page]').each(function(el){
            el.addEvent('change',function (){
                CustomFields.pageChanged(this.value);
            });
    });
    $$('input[name=categoryfilter]').each(function(el){
            el.addEvent('change',function (){
                CustomFields.toggleCategory(this);
            });
    });
    $$('input[name=compulsory]').each(function(el){
            el.addEvent('change',function (){
                if (this.value==1)
                    CustomFields.setFieldInfo(this.get('infoyes'));
                else
                    CustomFields.setFieldInfo(this.get('infono'));
            });
    });
    if ($('add_option_link'))
        $('add_option_link').addEvent('click',CustomFields.addOptionLink);
    if ($('save_options_link'))
        $('save_options_link').addEvent('click',CustomFields.saveNewOptionLink);
    if ($('field-reload-options'))
        $('field-reload-options').addEvent('click',CustomFields.reloadOptionLink);

    $$('.fields-delete-button').each(function(el){
        el.addEvent('click',CustomFields.delOptionLink);
    });
    $$('.fields-edit-button').each(function(el){
        el.addEvent('click',CustomFields.editOptionLink);
    });
    if ($('ftype'))
        $('ftype').addEvent('change',CustomFields.changeFieldType);
    var form=$$('[name=adminForm]');
    CustomFields.adminForm=form[0];
    CustomFields.extension=form[0].option.value;
});

var CustomFields={
    idoptions:0,
    extension:'',
    adminForm:null,
    pageChanged:function(page){
        if (typeof this.pages === "undefined" || typeof this.pages[page]=== "undefined"  ) return;
        if (this.pages[page].category)
            this.enableCategorySelection();
        else
            this.disableCategorySelection();
    },
    enableCategorySelection:function(){$('categories_overlay').setStyle('display','none'); },
    disableCategorySelection:function(){$('categories_overlay').setStyle('display','block');},
    toggleCategory:function(el){$$("select#parent").set('disabled',(el.value!=1));},
    selectAllCategories:function(){
        $$("select#parent").each(function(el){
				var n = el.options.length;
				for (i = 0; i < el.options.length; i++) 
					el.options[i].selected = true;
				
            });
    },
    selectNoneCategories:function(){
        $$("select#parent").each(function(el){
				var n = el.options.length;
				for (i = 0; i < el.options.length; i++) 
					el.options[i].selected = false;
				
            });        
    },
    setFieldInfo:function(text){$('field_info_compulsory').innerHTML=text;},
    addOptionLink:function(){
        var table = new HtmlTable('cfield_option_list_table');
        CustomFields.idoptions=CustomFields.idoptions+1;
        var myInput  = new Element('input', {name: 'field_option[]',type:'text',size:'20'});
        var myImgDel  = new Element('img', {src: 'components/'+CustomFields.extension+'/thefactory/fields/images/delete.png',
                border:'0',height:'15',
                'class':'fields-delete-button',
                'row-id':CustomFields.idoptions,
                'events': {
                    'click': CustomFields.delOptionRowLink
                    }
                });

        var row=table.push([
                myInput,
                '&nbsp',
                myImgDel],
                {id:'newopt-row-'+CustomFields.idoptions});
        return false;
    },
    delOptionRowLink:function(el){
        i=el.target.get('row-id');
        $('newopt-row-'+i).dispose();
        return false;
    },
    showError:function(msgText){
    },
    delOptionLink:function(el){
        i=el.target.get('row-id');
        el.target.set('src','components/'+CustomFields.extension+'/thefactory/fields/images/ajax_loading.gif');
        $('opt-row-'+i).set('opacity','.60');
        $('opt-row-'+i).setStyle('text-decoration','line-through');

        var url='index.php?option='+CustomFields.extension+'&task=fields.deleteoption&format=json';
        new Request({
            method: 'POST',
            url: url,
            data:{
                'fieldid':CustomFields.adminForm.id.value,
                'optionid':i
            },
            onSuccess: function(response) {
                res=JSON.parse(response);
                if(!res.result){
                    CustomFields.showError(res.errorText);
                    return;
                }
                $('opt-row-'+i).fade('out').get('tween').chain(function() {
                        this.element.dispose();
                });
            },
            onFailure:  function() {
                CustomFields.showError("Error!");
            }
        }).send();


        //$('opt-row-'+i).dispose();
        return false;
    },
    editOptionLink:function(el){
        i=el.target.get('row-id');
        el.target.set('src','components/'+CustomFields.extension+'/thefactory/fields/images/save.png');
        el.target.removeEvent('click',CustomFields.editOptionLink);
        el.target.addEvent('click',CustomFields.saveOptionLink);
        var span=$('cfield_opt_'+i);
        var edit=new Element('input',
            {
                'name':'editoption-'+i,
                'value':span.get('text'),
                'size':'20',
                'type':'text'
            }
        );
        span.empty();
        edit.inject(span);
        edit.focus();
        return false;
    },
    clearOptionsTable: function(){
        var table = new HtmlTable('cfield_option_list_table');
        table.body.empty();
    },
    addOptionsToTable: function(options){
        var table = new HtmlTable('cfield_option_list_table');
        var i=1;
        Object.each(options,
            function(opt){
                var myImgDel  = new Element('img', {src: 'components/'+CustomFields.extension+'/thefactory/fields/images/delete.png',
                        border:'0',height:'15',
                        'class':'fields-delete-button',
                        'row-id':opt.id,
                        'events': {
                            'click': CustomFields.delOptionLink
                            }
                        });
                var myImgEdit  = new Element('img', {src: 'components/'+CustomFields.extension+'/thefactory/fields/images/edit.png',
                        border:'0',height:'15',
                        'class':'fields-edit-button',
                        'row-id':opt.id,
                        'events': {
                            'click': CustomFields.editOptionLink
                            }
                        });
                table.push([
                        '<span id="cfield_opt_'+opt.id+'">'+opt.option_name+'</span>',
                        {
                            content:myImgEdit,
                            properties: {
                                align: 'center'
                            }
                        },
                        {
                            content:myImgDel,
                            properties: {
                                align: 'center'
                            }
                        }
                        ],
                        {
                            id:'opt-row-'+opt.id,
                            class:'row'+i
                        });
                i=1-i;
            }
        )

    },
    reloadOptionLink:function(){
        if(!CustomFields.adminForm.id.value)
        {
            //field not saved yet
            alert(js_lang_fields['field_must_be_saved']);
        }
        var url='index.php?option='+CustomFields.extension+'&task=fields.getfieldoptions&format=json';

        new Request({
            method: 'POST',
            url: url,
            data:{
                fieldid:CustomFields.adminForm.id.value
            },
            onSuccess: function(response) {
                options=JSON.parse(response);
                CustomFields.addOptionsToTable(options);
                if (this.imgload) this.imgload.dispose();
                $('field-reload-options').style.display='block';
            },
            onRequest: function() {
                CustomFields.clearOptionsTable();
                var myImgLoading  = new Element('img', {src: 'components/'+CustomFields.extension+'/thefactory/fields/images/ajax_loading.gif',
                        border:'0',height:'16',
                        'class':'ajax-loading'
                        });
                myImgLoading.inject($('field-reload-options'),'before');
                this.imgload=myImgLoading;
                $('field-reload-options').style.display='none';
            },
            onFailure:  function() {
                if (this.imgload) this.imgload.dispose();
                $('field-reload-options').style.display='block';
            }
        }).send();

        return false;
    },
    saveOptionLink: function(el){
            i=el.target.get('row-id');
            if(!CustomFields.adminForm.id.value)
            {
                //field not saved yet
                alert(js_lang_fields['field_must_be_saved']);
            }
            
            el.target.set('src','components/'+CustomFields.extension+'/thefactory/fields/images/ajax_loading.gif');
            el.target.removeEvent('click',CustomFields.saveOptionLink);
            var url='index.php?option='+CustomFields.extension+'&task=fields.saveoption&format=json';
            var val=CustomFields.adminForm['editoption-'+i].value;
			new Request({
				method: 'POST',
				url: url,
                data:{
                    fieldid:CustomFields.adminForm.id.value,
                    'id':i,
                    'optionvalue':val
                },
                onSuccess: function(response) {
                    el.target.set('src','components/'+CustomFields.extension+'/thefactory/fields/images/edit.png');
                    el.target.addEvent('click',CustomFields.editOptionLink);
                    var span=$('cfield_opt_'+i);
                    span.set('text',val);
                },
				onFailure:  function() {
                    res=JSON.parse(response);
                    CustomFields.showError(res.errorText);
                }
			}).send();

            return false;
    },
    saveNewOptionLink: function(el){
        if(!CustomFields.adminForm.id.value)
        {
            //field not saved yet
            alert(js_lang_fields['field_must_be_saved']);
        }
        vars=Array();
        $$(CustomFields.adminForm.elements).each(
            function (el){
                if(el.name=='field_option[]')
                    vars.push(el.value);
            }
        )
        var url='index.php?option='+CustomFields.extension+'&task=fields.saveoptions&format=json';

        new Request({
            method: 'POST',
            url: url,
            data:{
                fieldid:CustomFields.adminForm.id.value,
                'field_option':vars
            },
            onSuccess: function(response) {
                CustomFields.reloadOptionLink();
                if (this.imgload) this.imgload.dispose();
                $('field-reload-options').style.display='block';
            },
            onRequest: function() {
                var myImgLoading  = new Element('img', {src: 'components/'+CustomFields.extension+'/thefactory/fields/images/ajax_loading.gif',
                        border:'0',height:'16',
                        'class':'ajax-loading'
                        });
                myImgLoading.inject($('field-reload-options'),'before');
                this.imgload=myImgLoading;
                $('field-reload-options').style.display='none';
            },
            onFailure:  function() {
                if (this.imgload) this.imgload.dispose();
                $('field-reload-options').style.display='block';
            }
        }).send();

        return false;
    },
    changeFieldType:function(el){
        ftype=el.target.value;
        var url='index.php?option='+CustomFields.extension+'&task=fields.getfieldtypeparams&format=json';
        new Request({
            method: 'POST',
            url: url,
            data:{
                fieldid:CustomFields.adminForm.id.value,
                'ftype':ftype
            },
            onRequest: function()
            {
                $('cfield_parameters').empty();
                $('cfield_option_list').empty();
                var myImgLoading  = new Element('img', {src: 'components/'+CustomFields.extension+'/thefactory/fields/images/ajax_loading.gif',
                        border:'0',height:'100',
                        'class':'ajax-loading'
                        });
                myImgLoading.inject($('cfield_parameters'));
            },
            onSuccess: function(response) {
                res=JSON.parse(response);
                $('cfield_parameters').set('html',res['params']);
                if(res['hasoptions']){
                    $('cfield_option_list').set('html',res['options']);
                    $('add_option_link').addEvent('click',CustomFields.addOptionLink);
                    $('save_options_link').addEvent('click',CustomFields.saveNewOptionLink);
                    $('field-reload-options').addEvent('click',CustomFields.reloadOptionLink);

                    $$('.fields-delete-button').each(function(el){
                        el.addEvent('click',CustomFields.delOptionLink);
                    });
                    $$('.fields-edit-button').each(function(el){
                        el.addEvent('click',CustomFields.editOptionLink);
                    });
                }
            },
            onFailure:  function() {
                res=JSON.parse(response);
                CustomFields.showError(res.errorText);
            }
        }).send();

    }


}
