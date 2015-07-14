function changeAuctionType() {

    var frm = document.auctionForm;

    var bin_price = $("BIN_price");
    var bin_price_row = $("BIN_price_row");
    var bin_price_row2 = $("BIN_price_row2");
    var auction_type = $("auction_type");
    var bin_option = $("bin_OPTION");

    var bin_only_extra = $("bin_only_extra"); //quantity
    var bid_price_suggest = $("bid_price_suggest");
    var bid_price_suggest_min = $("bid_price_suggest_min");
    var price_suggest_min = $("price_suggest_min");

    var reserve_price_row = $("reserve_price_row");
    var min_increase_select_row = $("min_increase_select_row");
    var initial_price_row = $("initial_price_row");
    var automatic_check = $("automatic_check");

    if(automatic_check) {
        automatic_check.addEvent('click', function(e) {
            bin_price_row2.style.display = automatic_check.get('checked') ? 'none' : '';
        });
    }

    var reserve_maxprice_row = $("reserve_maxprice_row");
    var reserve_nrbidder_row = $("reserve_nrbidder_row");
    var reserve_price_row2 = $("reserve_price_row2");

    if (auction_type && auction_type.value)
    {
        if (auction_type.value == 3)
        { //BIN ONLY AUCTION
            if (bin_option)
            {
                bin_option.value = 1;
                bin_option.disabled = true;
            }

            if (bin_price && bin_price_row)
            {
                bin_price_row.style.display = "";
                bin_price.disabled = false;
                bin_price_row2.style.display = "";
            }

            if (bin_only_extra)
            {
                bin_only_extra.style.display = "";
            }

            if (bid_price_suggest)
            {
                bid_price_suggest.style.display = "";
            }

            if (price_suggest_min)
            {
                bid_price_suggest_min.style.display = "";
            }

            if (min_increase_select_row)
            {
                min_increase_select_row.style.display = "none";
            }

            if (reserve_price_row)
            {
                reserve_price_row.style.display = "none";
            }

            $('initial_priceID').removeClass("required");
            $('initial_priceID').removeClass("validate-price");
            
            if (initial_price_row)
            {
                initial_price_row.style.display = "none";
            }

            if (automatic_check)
            {
                automatic_check.disabled = true;
                bin_price_row2.style.display = '';
            }

            if (reserve_maxprice_row)
            {
                reserve_maxprice_row.style.display = "none";
            }

            if (reserve_nrbidder_row)
            {
                reserve_nrbidder_row.style.display = "none";
            }
            if (reserve_price_row2)
            {
                reserve_price_row2.style.display = "none";
            }
        }
        else
        {
            $('initial_priceID').addClass("required");
            $('initial_priceID').addClass("validate-price");
            if (bin_option)
            {
                bin_option.disabled = false;
            }
            if (bin_price && bin_price_row)
            {
                if ( bin_option && bin_option.value == 1 )
                {
                    bin_price_row.style.display = "";
                    if(!automatic_check || !automatic_check.get('checked')|| 1==bin_option.value) {
                        bin_price_row2.style.display = "";
                    }
                }
                else
                {
                    bin_price_row.style.display = "none";
                    if(!automatic_check || !automatic_check.get('checked') || 0==bin_option.value) {
                        bin_price_row2.style.display = "none";
                    }
                }
                bin_price.disabled = false;
            }

            if (bin_only_extra)
            {
                bin_only_extra.style.display = "none";
            }

            if (bid_price_suggest)
            {
                bid_price_suggest.style.display = "none";
            }

            if (bid_price_suggest_min)
            {
                bid_price_suggest_min.style.display = "none";
            }

            if (min_increase_select_row)
            {
                min_increase_select_row.style.display = "";
            }

            if (reserve_price_row)
            {
                reserve_price_row.style.display = "";
            }

            if (initial_price_row)
            {
                initial_price_row.style.display = "";
            }

            if (automatic_check)
            {
                automatic_check.disabled = false;
            }

            if (reserve_maxprice_row)
            {
                reserve_maxprice_row.style.display = "";
            }

            if (reserve_nrbidder_row)
            {
                reserve_nrbidder_row.style.display = "";
            }

            if (reserve_price_row2)
            {
                reserve_price_row2.style.display = "";
            }
        }
    } else {
        if(bin_option) bin_option.disabled = true;
    }

    if (typeof(frm)!='undefined' && frm.price_suggest)
    {
        if (getCheckedValue(frm.price_suggest) == 1)
        {
            price_suggest_min.disabled = false;
        }
        else
        {
            price_suggest_min.disabled = true;
        }

    }

}

//run this once just in case user default settings are in use
window.addEvent('domready', function() {
    changeAuctionType();
});



function getCheckedValue(radioObj)
{
    if (!radioObj) return "";
    var radioLength = radioObj.length;
    if (radioLength == undefined) if (radioObj.checked) return radioObj.value;
        else return "";
    for (var i = 0; i < radioLength; i++)
    {
        if (radioObj[i].checked)
        {
            return radioObj[i].value;
        }
    }
    return "";
}

JFormValidator.prototype.validate = function(el)
{
    // If the field is required make sure it has a value
    if ($(el).hasClass('required')) {
        //file inputs need to be treated alone, because .getValue() always returns "false" for them
        if ($(el).get('tag')=='input' && $(el).get('type')=='file' ){
            if(!Boolean($(el).get('value'))) {
                this.handleResponse(false, el);
                return false;
            }
        } else if (!($(el).get('value'))) {
            this.handleResponse(false, el);
            return false;
        }
    }

    // Only validate the field if the validate class is set
    var handler = (el.className && el.className.search(/validate-([a-zA-Z0-9\_\-]+)/) != -1) ? el.className.match(/validate-([a-zA-Z0-9\_\-]+)/)[1] : "";
    if (handler == '') {
        this.handleResponse(true, el);
        return true;
    }

    // Check the additional validation types
    if ((handler) && (handler != 'none') && (this.handlers[handler]) && $(el).get('value')) {
        // Execute the validation handler and return result
        if (this.handlers[handler].exec($(el).get('value')) != true) {
            this.handleResponse(false, el);
            return false;
        }
    }

    // Return validation state
    this.handleResponse(true, el);
    return true;
};

function validateForm(frm) {
    if (!document.formvalidator.isValid(frm)) 
    {
        var msg = language["bid_fill_in_required"];
        alert(msg);
        return false; 
    }

    //verif bin empty

    sel=$('bin_OPTION');
    if (sel && sel.value != 0)
    { //auction with bin
        sel=$('BIN_price');
        bin_val=parseFloat(sel.value);
        if (isNaN(bin_val) ||(bin_val<=0))
        {
            alert(language["bid_auction_bin_zero"]);            
            return false;
        }
    }

    return true; 

}

window.addEvent('domready', function() {
    Element.extend({
        getValue: function(){
            //original getValue did not work for file and radio
            switch(this.get('tag')){
                case 'select':
                    var values = [];
                    $each(this.options, function(option){
                        if (option.selected) values.push($pick(option.value, option.text));
                    });
                    return (this.multiple) ? values : values[0];
                case 'input':
                    if (this.type=='radio') return getCheckedValue(this);
                    if (!(this.checked && ['checkbox', 'radio'].contains(this.type)) && !['hidden', 'text', 'password','file'].contains(this.type)) break;
                case 'textarea':
                    return this.value;
            }
            return false;
        }
    });

    document.formvalidator.setHandler('payment', function(value) {
        if (value == 0)
        {
            alert(language["bid_err_payment_valid"]);
            return false;
        }    
        return true;
    });

    document.formvalidator.setHandler('bin', function(value) {   
        sel=$('bin_OPTION');
        if (sel.value == 0)
        { //not bin
            return true;
        }

        bin_val=parseFloat(value);
        if (isNaN(bin_val) ||(bin_val<=0))
        {
            alert(language["bid_auction_bin_zero"]);            
            return false;
        }

        price=$('initial_price');
        if (price && bin_val < parseFloat(price.value))
        {
            alert(language["bid_err_bin_must_be_greater"]);
            return false;
        }

        return true;

    });

    document.formvalidator.setHandler('price', function(value) {
        price_val=parseFloat(value);
        if (isNaN(price_val) ||(price_val<=0))
        {
            alert(language["bid_err_price"]);
            return false;
        }    
        return true;
    });

   

    document.formvalidator.setHandler('start-date', function(value) {
        var start_date = $('start_date');

        var currentTime = new Date();
        var month = currentTime.getMonth() + 1;
        var day = currentTime.getDate();
        var year = currentTime.getFullYear();
   
        var nowDate = year + '-' + month + '-' + day;

        var joomlaformat = bid_date_format;
        if (joomlaformat=='D, F d Y')
            joomlaformat='EE, MMM d y';
        joomlaformat = joomlaformat.replace('m', 'M');
        joomlaformat = joomlaformat.replace('Y', 'y');
        
        //'yyyy-MM-dd'
        if (!isDate(start_date.value, joomlaformat))
        {
            alert(language["bid_err_start_date_valid"]);
            return false;
        }

        if ( compareDates(start_date.value, joomlaformat, nowDate, 'yyyy-M-d') < 0 )
        //if ( compareDates(start_date.value, joomlaformat, nowDate, 'yyyy-M-d') < 0 || dateGt(start_date.value, joomlaformat, nowDate, 'yyyy-M-d') < 0 )
        {
            alert(language["bid_err_start_date_valid"]);
            return false;
        }
        
        return true;
    });

    document.formvalidator.setHandler('end-date', function(value) {
        var start_date = $('start_date');
        var end_date = $('end_date');

        var joomlaformat = bid_date_format;
        if (joomlaformat=='D, F d Y')
            joomlaformat='EE, MMM d y';
        joomlaformat = joomlaformat.replace('m', 'M');
        joomlaformat = joomlaformat.replace('Y', 'y');

        if (!isDate(end_date.value, joomlaformat))
        {
            alert(language["bid_err_end_date_valid"]);
            return false;
        }

        if (compareDates(end_date.value, joomlaformat, start_date.value, joomlaformat) <= 0)
        {
            alert(language["bid_err_end_date_valid"]);
            return false;
        }

        var d1 = new Date(getDateFromFormat(start_date.value, joomlaformat));
        d1.setMonth(d1.getMonth() + bid_max_availability);
        var d2 = getDateFromFormat(end_date.value, joomlaformat);

        if (bid_max_availability > 0 && d1.getTime() < d2)
        {
            alert(language["bid_err_max_valability"]);
            return false;
        }
        return true;

    });

})


function clearSelectedValues()
{

    var Sl = Array();
    for (var i = 0; i < CurrentIndex; i++)
    {
        Sl[i] = $("zzone_" + i).value;
        //!!! TO FIX
        if (SHIPOptions.in_array(Sl[i]))
        {
            DelValue(Sl[i]);
        }
    }

    return Sl;

}

Array.prototype.in_array = function (p_val)
{
    for (var i = 0, l = this.length; i < l; i++)
    {
        if (this[i] == p_val)
        {
            return true;
        }
    }
    return false;
}

function DelValue(p_val)
{

    var t = Array();
    var ids = Array();

    var l = SHIPOptions.length;
    var p = 0;
    for (var inti = 0; inti < l; inti++)
    {
        if (SHIPOptions[inti] != p_val)
        {
            t[p] = SHIPOptions[inti];
            ids[p] = SHIPIDS[inti];
            p++;
        }
        else
        {
            SHIPNO -= 1;
        }
    }
    SHIPOptions = t;
    SHIPIDS = ids;
}

function SHIPAddzone()
{

    var SHIPSelectedVals = Array();
    var SHIPSelectedVals = 0;
    var SHIPPrices = Array();
    var zIndex = CurrentIndex;

    CurrentIndex += 1;
    if (CurrentIndex > SHIPNO) return;

    var container = $('shippingZonesContainer');

    myselect = document.createElement("select");
    myselect.setAttribute('name', 'shipZones[]');
    myselect.setAttribute('id', 'shipZones[]');
    myselect.className = 'inputbox';

    for (var i = 0; i < SHIPNO; i++)
    {
        theOption = document.createElement("OPTION");
        theOption.setAttribute('value', SHIPIDS[i]);
        theOption.innerHTML = SHIPOptions[i];
        myselect.appendChild(theOption);
    }
    container.appendChild(myselect);
    myprice = document.createElement("input");
    myprice.setAttribute('name', 'shipPrices[]');
    myprice.setAttribute('type', 'text');
    myprice.setAttribute('value', '');
    myprice.className = 'inputbox validate-numeric';

    container.appendChild(myprice);
    mybr = document.createElement("br");
    container.appendChild(mybr);

}

function auctionStartCurrentLocalTimer(cssClass,year,month,day,hour,minute,lang)
{
    jQueryBids(document).ready(
        function() {
            var d = new Date(year,month-1,day,hour,minute); //month in JS is 0-11
            jQueryBids('.'+cssClass).clock( {
                'timestamp': d.getTime(),
                "langSet":lang
            } );
        }
    );
}

//Calendar fix for IE
if(typeof Calendar != 'undefined') {
    Calendar.prototype.showAtElement=function(el,opts){var self=this;var p=Calendar.getAbsolutePos(el);if(!opts||typeof opts!="string"){this.showAt(p.x,p.y+el.offsetHeight);return true;}function fixPosition(box){if(box.x<0)box.x=0;if(box.y<0)box.y=0;var cp=document.createElement("div");var s=cp.style;s.position="absolute";s.right=s.bottom=s.width=s.height="0px";document.body.appendChild(cp);var br=Calendar.getAbsolutePos(cp);document.body.removeChild(cp);if(Calendar.is_ie){br.y += document.body.document.documentElement.scrollTop;br.x += document.body.document.documentElement.scrollLeft;}else{br.y+=window.scrollY;br.x+=window.scrollX;}var tmp=box.x+box.width-br.x;if(tmp>0)box.x-=tmp;tmp=box.y+box.height-br.y;if(tmp>0)box.y-=tmp;};this.element.style.display="block";Calendar.continuation_for_the_fucking_khtml_browser=function(){var w=self.element.offsetWidth;var h=self.element.offsetHeight;self.element.style.display="none";var valign=opts.substr(0,1);var halign="l";if(opts.length>1){halign=opts.substr(1,1);}switch(valign){case "T":p.y-=h;break;case "B":p.y+=el.offsetHeight;break;case "C":p.y+=(el.offsetHeight-h)/2;break;case "t":p.y+=el.offsetHeight-h;break;case "b":break;}switch(halign){case "L":p.x-=w;break;case "R":p.x+=el.offsetWidth;break;case "C":p.x+=(el.offsetWidth-w)/2;break;case "l":p.x+=el.offsetWidth-w;break;case "r":break;}p.width=w;p.height=h+40;self.monthsCombo.style.display="none";fixPosition(p);self.showAt(p.x,p.y);};if(Calendar.is_khtml)setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()",10);else Calendar.continuation_for_the_fucking_khtml_browser();}
}

function bidCheckHours(el) {
    var hour = parseInt(el.value);
    if(hour<0 || hour>24) {
        el.value = "00";
    }
}

function bidCheckMinutes(el) {
    var minutes = parseInt(el.value);
    if(minutes<0 || minutes>59) {
        el.value = "00";
    }
}

function auctionRefreshCustomFields(catselect)
{
    frm=catselect.form;
    if (frm.has_custom_fields_with_cat.value)
    {
        frm.task.value='refreshcategory';
        frm.submit();
    }
}