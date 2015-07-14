window.addEvent('domready', function () {
    $('add_fields').addEvent('click',function (){
        $('fields_all').getSelected().each(function(el){
              el.inject($('fields'));
        });
    });
    $('remove_fields').addEvent('click',function (){
        $('fields').getSelected().each(function(el){
              el.inject($('fields_all'));
        });
    });
    $('adminForm').addEvent('submit',function (){
        options=$('fields').getElements('option');
        options.each(function(option) {option.selected=true;});
    })
});
