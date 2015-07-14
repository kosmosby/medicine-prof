/**
 * Copyright (C) 2007-2014 and Trademark of Lightning MultiCom SA, Switzerland - www.joomlapolis.com - and its licensors, all rights reserved
 * License: GNU General Public License version 2 http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/* unused:
function cbPlotTicksDate( axis ) {
	var ticks = [];
	for (var i=axis.min; i<=axis.max; i++) {
		var d = new Date((i+1)*86400000);
		ticks.push([i, d.getDate()]);
	}
	return ticks;
}
*/
function cbPlotTicksWeekDays( axis ) {
	var ticks = [];
	var weekDays = ["-","Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];
	//for (var i=axis.min; i<=axis.max; i++) {
	for (var i=1; i<=7; i++) {
		ticks.push([i, weekDays[i]]);
	}
	return ticks;
}
/*
 * helper for returning the weekends in a period
 */
function cbPlotWeekends(axes) {
    var markings = [];
    var d = new Date(axes.xaxis.min);
    // go to the first Saturday
    d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7));
    d.setUTCSeconds(0);
    d.setUTCMinutes(0);
    d.setUTCHours(0);
    var i = d.getTime();
    do {
        // when we don't set yaxis the rectangle automatically
        // extends to infinity upwards and downwards
        markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
        i += 7 * 24 * 60 * 60 * 1000;
    } while (i < axes.xaxis.max);

    return markings;
}

/*
jQuery(document).ready(function($){
});
*/
