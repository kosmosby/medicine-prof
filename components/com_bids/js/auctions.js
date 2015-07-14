function ValidateBidPrice(bid_amount,mylastbid,maxbid,minincrease,proxy,initial_price)
{
   if (parseFloat(bid_amount.value)<=0 || isNaN(parseFloat(bid_amount.value))){
	   alert(language["bid_err_empty_bid"]);
	   return false;
   }
	if(parseFloat(maxbid.value)>0){
		accepted_price = (parseFloat(maxbid.value) + parseFloat(minincrease.value)).toFixed(2);
		if(accepted_price>parseFloat(bid_amount.value)){
			alert(language["bid_err_increase"]+' ' + accepted_price + ' ' + auction_currency);
			return false;
		}
	}
	if(parseFloat(mylastbid.value)>0){
		accepted_price = (parseFloat(mylastbid.value) + parseFloat(minincrease.value)).toFixed(2);
		if(accepted_price>parseFloat(bid_amount.value)){
			alert(language["bid_err_must_be_greater_mybid"]+' ' + accepted_price + ' ' + auction_currency);
			return false;
		}
	}
	if(parseFloat(initial_price.value)>0){
		accepted_price = parseFloat(initial_price.value).toFixed(2);
		if(accepted_price>parseFloat(bid_amount.value)){
			alert(language["bid_err_must_be_greater"]+' ' + accepted_price + ' ' + auction_currency);
			return false;
		}
	}
	return true;
}
function FormValidate(form)
{
    if (form.name=='auctionRateForm')
    {
        var rate = form.elements['rate'];

        if (isNaN(parseInt(rate.value))||parseInt(rate.value)<=0)
        {
            alert(language["bid_must_rate"]);
            return false;
        }
        return true;
    }
	var initial_price = form.elements['initial_price'];
	var bin = form.elements['bin_price'];
	var mylastbid =  form.elements['mylastbid'];
	var minincrease =  form.elements['min_increase'];
	var maxbid = form.elements['maxbid'];
	var proxy = form.elements['prxo'];
	var bid_amount= form.elements['amount'];

//the bid must be greater than mylastbid and greater then lastbid
	if(!ValidateBidPrice(bid_amount,mylastbid,maxbid,minincrease,proxy,initial_price)){
		return false;
	}
	if (parseFloat(bin.value)>0){
		if( parseFloat(bid_amount.value) >= parseFloat(bin.value)) {
			//greater then BIN - Warn
			var answer = confirm(language["bid_bid_greater_than_bin"]);
			if(answer == 0) return false;
		}
	}

	return true;
}
function MakeBinBid(form, bin_price, currency_name)
{

    nr_items=parseInt(document.getElementById("system_bin_quantity").value);
	if(has_quantity){
		var lang_confirm = language["bin_js_alert_q"];
		lang_confirm = lang_confirm.replace("NITEMS",nr_items);
		lang_confirm = lang_confirm.replace("VALUE", (bin_price*nr_items).toFixed(2) + ' ' + currency_name );
	}else{
	   	var lang_confirm = language["bin_js_alert"];
		lang_confirm = lang_confirm.replace("VALUE", bin_price.toFixed(2) + ' ' + currency_name );
	}

    if (!confirm(lang_confirm)) return false;
    form.quantity.value=nr_items;
    return true;
}
function SendMessage(link,message_id,bidder_id,username)
{
    if (!bidder_id) bidder_id=0;
    if (!message_id) message_id=0;
    if (link) link.style.display='none';
    document.getElementById('bidder_id').value=bidder_id;
    document.getElementById('idmsg').value=message_id;
    if (tabPane1.pages.length>1){
         tabPane1.setSelectedIndex(tabPane1.pages.length-1);
    }
    document.getElementById('msg').style.display='block';
    document.getElementById('message_to').innerHTML=username;
    document.getElementById('message').focus();
}
function ProxyClick(checkbox)
{
    if(checkbox.checked) {
            document.getElementById('bid').innerHTML=language["bid_maxpp"];
            document.getElementById('prxo').value=1;
    } else{
         document.getElementById('prxo').value=0;
         document.getElementById('bid').innerHTML=language["bid_bid_price"];
    }
}
// Since 1.6.6
//
function suggestPrice(el){
    frm=el.form;
 	var d = frm.bid_suggest.value;
	if(d)
        frm.submit();
	else
		alert(language["bid_err_empty_suggest"]);
}

function suggestThisPrice(){

 	var d = document.getElementById('form_bid_suggest').value;
	if(d)
		document.form_suggestPrice.submit();
	else
		alert(language["bid_err_empty_suggest"]);
}

function refreshSuggestPrice(){

	total = document.getElementById('bid_suggest').value * document.getElementById('system_quantity').value;

	document.getElementById("bid_suggest_total").innerHTML=total;

}
function refreshSuggestList_Price(total, quantity, someId){

	total = total * quantity;
	document.getElementById(someId).innerHTML=total;

}
function removeFilters()
{
   document.auctionForm.task.value='removeFilters';
   document.auctionForm.submit();

}

function launchpca(amount,fromC,toC) {
    XECurrencyWindow = window.open ('', 'XECurrencyWindow','toolbar=0,location=0,directories=0,status=0,menubar=0,scrollbars=0,resizable=1,height=200,width=620');
    XECurrencyWindow.focus();
    XECurrencyWindow.location.href = 'http://www.xe.com/pca/input.php?Amount='+amount+'&FromSelect='+fromC+'&ToSelect='+toC;
}
//by default, SqueezeBox closes on pressing X; this function overwrites that behavior
window.addEvent('domready', function(){
    if(typeof SqueezeBox != 'undefined') {
        SqueezeBox.onkeypress = function(e) {
            switch (e.key) {
                case 'esc':
                    this.close();
                    break;
            }
        };
        SqueezeBox.handlers.adoptSpecial = function(el) {
            return el.clone(true,true);
        };
        SqueezeBox.showContent = function() {
            if(this.options.onShow) {
                eval(this.options.onShow+'()');
            }
            if (this.content.get('opacity')) this.fireEvent('onShow', [this.win]);
            this.fx.content.start(1);
        };
    }
});

function refreshTotalPrice(aid,sp) {
    var newPrice = parseFloat($('bidderTotalPrice'+aid).value) + parseFloat(sp);
    document['paypalForm'+aid].amount.value = newPrice.toFixed(2);
    $('amount_total'+aid).innerHTML = newPrice.toFixed(2);
}

Element.extend({
    getProperty: function(property){
        var index = Element.Properties[property];
        if (index) return this[index];
        var flag = Element.PropertiesIFlag[property] || 0;

        // Commented old line
//      if (!window.ie || flag) return this.getAttribute(property, flag);

        // Two new lines: put MSIE version number in var msie and check if this is 8 or higher
        var msie = navigator.userAgent.toLowerCase().match(/msie\s+(\d)/);
        if (!window.ie || flag || msie && msie[1]>=8) return this.getAttribute(property, flag);

        var node = this.attributes[property];
        return (node) ? node.nodeValue : null;
    }
});

function showMessageBox(elementId,special)
{
	var newElem = $(elementId);
    var handler = special ? 'adoptSpecial' : 'clone';

    if(special) {
        SqueezeBox.addEvent('onOpen', function() {
            newElem.set('html','');
        });

        SqueezeBox.addEvent('onClose', function() {
            newElem.set('html',this.content.getChildren()[0].outerHTML);
        });
    }

	SqueezeBox.setContent( handler, newElem );
}
function showSendMessages(username,idmsg,bidderid)
{
    SqueezeBox.trash();
    SqueezeBox.close();

    var myForm = document.forms["messageForm"];
	var newElem = $('auction_message_to');
	newElem.innerHTML=username;

	if (!idmsg) idmsg=0;
	myForm.idmsg.value=idmsg;
	if (bidderid) myForm.bidder_id.value=bidderid;
	showMessageBox('auction_message_form_div',true);
}


function showMessageInline(elementId){
	var newElem = $(elementId);
	newElem.style.display="";
}

window.addEvent('domready', function () {
//Calendar fix for IE
if (typeof Calendar != 'undefined') {
    Calendar.prototype.showAtElement = function (el, opts) {
        var self = this;
        var p = Calendar.getAbsolutePos(el);
        if (!opts || typeof opts != "string") {
            this.showAt(p.x, p.y + el.offsetHeight);
            return true;
        }
        function fixPosition(box) {
            if (box.x < 0)box.x = 0;
            if (box.y < 0)box.y = 0;
            var cp = document.createElement("div");
            var s = cp.style;
            s.position = "absolute";
            s.right = s.bottom = s.width = s.height = "0px";
            document.body.appendChild(cp);
            var br = Calendar.getAbsolutePos(cp);
            document.body.removeChild(cp);
            if (Calendar.is_ie) {
                br.y += document.body.document.documentElement.scrollTop;
                br.x += document.body.document.documentElement.scrollLeft;
            } else {
                br.y += window.scrollY;
                br.x += window.scrollX;
            }
            var tmp = box.x + box.width - br.x;
            if (tmp > 0)box.x -= tmp;
            tmp = box.y + box.height - br.y;
            if (tmp > 0)box.y -= tmp;
        }

        ;
        this.element.style.display = "block";
        Calendar.continuation_for_the_fucking_khtml_browser = function () {
            var w = self.element.offsetWidth;
            var h = self.element.offsetHeight;
            self.element.style.display = "none";
            var valign = opts.substr(0, 1);
            var halign = "l";
            if (opts.length > 1) {
                halign = opts.substr(1, 1);
            }
            switch (valign) {
                case "T":
                    p.y -= h;
                    break;
                case "B":
                    p.y += el.offsetHeight;
                    break;
                case "C":
                    p.y += (el.offsetHeight - h) / 2;
                    break;
                case "t":
                    p.y += el.offsetHeight - h;
                    break;
                case "b":
                    break;
            }
            switch (halign) {
                case "L":
                    p.x -= w;
                    break;
                case "R":
                    p.x += el.offsetWidth;
                    break;
                case "C":
                    p.x += (el.offsetWidth - w) / 2;
                    break;
                case "l":
                    p.x += el.offsetWidth - w;
                    break;
                case "r":
                    break;
            }
            p.width = w;
            p.height = h + 40;
            self.monthsCombo.style.display = "none";
            fixPosition(p);
            self.showAt(p.x, p.y);
        };
        if (Calendar.is_khtml)setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()", 10); else Calendar.continuation_for_the_fucking_khtml_browser();
    }
}
});

function bidsRefreshCurrency(value) {

    $$('.bidsRefreshCurrency').each(
        function(el) {
            el.set('html',value);
        }
    )
}