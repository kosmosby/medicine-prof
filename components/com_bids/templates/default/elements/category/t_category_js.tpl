{import_js_block}
{literal}
function showMore(nr,link)
{
    var el=document.getElementById('auction_hidden_subcats_'+nr);    
    if (el.style.display=='none')
    {
        el.style.display='block';
        link.innerHTML='--'+language["bid_fewer"];
        
    }else{
        el.style.display='none';
        link.innerHTML='++'+language["bid_more"];
    }
    
}
{/literal}
{/import_js_block}
