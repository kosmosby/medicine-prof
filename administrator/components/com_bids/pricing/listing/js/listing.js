window.addEvent('domready', function () {
    $$('input[name=category_pricing_enabled]').each(function(el){
        el.addEvent('change',function (){
            var chk=this.checked;
            $$('input.category_pricing').each(function(el2){
                el2.disabled=!chk;
            });
        });
        el.addEvent('blur',function(){this.fireEvent('change')});
    });
});