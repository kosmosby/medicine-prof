{if $auction->quantity>0 && $auction->BIN_price>0 && $bidCfg->bid_opt_global_enable_bin}
{import_js_block}
    {literal}
    window.addEvent('domready', function() {
    {/literal}
        var spinCtrl = new SpinControl();
    	spinCtrl.SetMaxValue({$auction->quantity});
    	spinCtrl.SetMinValue(1);
    	spinCtrl.AttachValueChangedListener(spinCtrlPrintOut);
    	var spinnerBox = document.getElementById("spinnerBox");
        {literal}
        if(spinnerBox){
        	spinnerBox.appendChild(spinCtrl.GetContainer());
        	spinCtrl.StartListening();
        }
        {/literal}
        
        var spinBINCtrl = new SpinControl();
    	spinBINCtrl.SetMaxValue({$auction->quantity});
    	spinBINCtrl.SetMinValue(1);
    	spinBINCtrl.AttachValueChangedListener(spinCtrlPrintOut2);
    	var spinnerBINBox = document.getElementById("spinnerbinBox");
        {literal}
        if(spinnerBINBox){
        	spinnerBINBox.appendChild(spinBINCtrl.GetContainer());
        	spinBINCtrl.StartListening();
        }
	});
			

	function spinCtrlPrintOut(sender, newVal){  
	       document.getElementById("system_quantity").value = newVal; 
           refreshSuggestPrice(); 
    }
	function spinCtrlPrintOut2(sender, newVal){  
    {/literal}
	   document.getElementById("system_bin_quantity").value = newVal;
       var dec = parseInt({$bidCfg->bid_opt_number_decimals});
       var multiplePrice = Math.round({$auction->BIN_price}*newVal*Math.pow(10,dec))/Math.pow(10,dec);
        if(multiplePrice==parseInt(multiplePrice)) {literal}{{/literal}
            multiplePrice = multiplePrice + '.00';
{literal}}{/literal}
	   document.getElementById("quantity_info").innerHTML = " &nbsp;"
        + '<input type="button" id="bin_button" class="auction_button_BINq" value="{'COM_BIDS_BUY_IT_NOW'|translate} '
        + multiplePrice + ' {$auction->currency}" name="bin_button" onclick="preCheckBin();" />';
    {literal}
	}
    {/literal}
{/import_js_block}
{/if}

{import_js_block}
function preCheckBin()
{literal}{{/literal}
    if(MakeBinBid(document.auctionForm_bin,{$auction->BIN_price},'{$auction->currency}'))
        document.auctionForm_bin.submit();
{literal}}{/literal}
{/import_js_block}
