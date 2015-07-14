jQuery(document).ready(function () {
    jQuery(".catsmedizd input[type='checkbox']").change(function(){
        
        var catid = jQuery(this).parent().attr("id");
        if(jQuery(this).is(':checked')){
            jQuery('#'+catid+'id').css( "display", "block" );
        }else{
            jQuery('#'+catid+'id').css( "display", "none" );
        }
    })
});

function MedCatUnselect(){
    jQuery("#medcats_div input[type='checkbox']").attr('checked', false);
    jQuery('.subcatsmedizd').css( "display", "none" );
}
function MedCatSearch(base){
    var vals = '';
    jQuery("#medcats_div input[type='checkbox']:checked").each(function(){
        vals = vals + jQuery(this).val() + ',';
    });
    if(!vals){
        vals = '0';
    }
    
        jQuery('#jqGrid').jqGrid('clearGridData');
        jQuery('#jqGrid').jqGrid('setGridParam', {url: base + 'index.php?option=com_medicineproducts&task=getItemsJson&tmpl=component&no_html=1&cats='+vals});
        jQuery('#jqGrid').trigger('reloadGrid');

    
    
    

}