String.prototype.trim = function () {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}
String.prototype.copyTo = function (substr) {
    return this.substring(0,this.indexOf(substr)).trim();
}
String.prototype.deleteFrom = function (substr) {
    return this.slice(this.indexOf(substr)+substr.length);
}
function toDoubleDigit(i)
{
	var s=new String(i);
	if (s.length==0) s='00'
	else
		if (s.length==1) s='0'+s;
	return s;
}
function setTimeLeft()
{
    var timers = jQueryBids('.bidCountdown');
	for(i=0;i<=timers.length;i++) {
	    el=timers[i];
	    if (!el) continue;
		val=el.innerHTML;
		if (val==expired || val==auctionClosed) continue;
		d=0;
		h=0;
		m=0;
		s=0;
		if (val.indexOf(days)>=0){
			ds=val.copyTo(days);
			val=val.deleteFrom(days);
			if(parseInt(ds)!=NaN){
				d=parseInt(ds,10);
			}
		}
		if (val.indexOf(':')>=0){
			hs=val.copyTo(':');
			val=val.deleteFrom(':');
			if(parseInt(hs)!=NaN){
				h=parseInt(hs,10);
			}
		}
		if (val.indexOf(':')>=0){
			ms=val.copyTo(':');
			val=val.deleteFrom(':');
			if(parseInt(ms)!=NaN){
				m=parseInt(ms,10);
			}
		}
		if(parseInt(val)!=NaN){
			s=parseInt(val,10);
		}
		timedout=false;
		if (s>0){
			s--;
		}else{
			s=59;
			if(m>0){
				m--;
			}else{
				m=59;
				if(h>0){
					h--;
				}else{
					h=23;
					if(d>0){
						d--;
					}else{
						timedout=true;
					}
				}
			}
		}
		newval='';
		if(!timedout){
			if(d>0)
				newval=d+' '+days+' ';
			newval+=toDoubleDigit(h)+':'+toDoubleDigit(m)+':'+toDoubleDigit(s);
		}else{
			newval=expired;
		}
		el.innerHTML=newval;

	}

    if(i > 1) {
		window.setTimeout('setTimeLeft()',1000);
	}
}