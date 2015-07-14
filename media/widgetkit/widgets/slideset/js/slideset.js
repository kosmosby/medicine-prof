(function(j){var z=$widgetkit.support,A=[],n=function(){},s={width:"auto",height:"auto",index:0,autoplay:!1,effect:"slide",interval:5E3,easing:"easeOutCirc",duration:300};n.prototype=j.extend(n.prototype,{name:"slideset",initialize:function(b){this.options=j.extend({},s,b);var a=this,d=this.element;this.sets=d.find("ul.set");this.navitems=d.find("ul.nav").children();this.current=this.sets[this.options.index]?this.options.index:0;this.busy=!1;this.timer=null;this.hover=!1;this.gwidth="auto"==this.options.width?
d.width():this.options.width;this.navitems.each(function(d){j(this).bind("click",function(){a.stop().show(d)})});d.find(".next, .prev").bind("click",function(){a.stop()[j(this).hasClass("next")?"next":"previous"]()});"ontouchend"in document&&(d.bind("touchstart",function(b){function e(b){if(a){var l=b.originalEvent.touches?b.originalEvent.touches[0]:b;c={time:(new Date).getTime(),coords:[l.pageX,l.pageY]};10<Math.abs(a.coords[0]-c.coords[0])&&b.preventDefault()}}var f=b.originalEvent.touches?b.originalEvent.touches[0]:
b,a={time:(new Date).getTime(),coords:[f.pageX,f.pageY],origin:j(b.target)},c;d.bind("touchmove",e).one("touchend",function(){d.unbind("touchmove",e);a&&c&&1E3>c.time-a.time&&(30<Math.abs(a.coords[0]-c.coords[0])&&75>Math.abs(a.coords[1]-c.coords[1]))&&a.origin.trigger("swipe").trigger(a.coords[0]>c.coords[0]?"swipeleft":"swiperight");a=c=void 0})}),d.bind("swipeleft",function(){a.next()}).bind("swiperight",function(){a.previous()}));this.resize();j(window).bind("debouncedresize",function(){a.resize()});
this.navitems.eq(this.current).addClass("active");d.hover(function(){a.hover=!0},function(){a.hover=!1});this.options.autoplay&&this.start()},resize:function(){this.sets.css($widgetkit.css3({transform:""}));var b=this.element.find(".sets:first"),a=b.css({width:""}).width(),d=0,h="auto"==this.options.width?this.element.width():this.options.width,e="auto"==this.options.height?this.sets.eq(0).children().eq(0).outerHeight(!0):this.options.height;this.sets.each(function(){var b=j(this).show(),a=j(this).children();
tmp=0;a.each(function(){var c=j(this);c.css("left",tmp).data("left",tmp);tmp+=c.width()});d=Math.max(d,tmp);b.css("width",tmp).css("margin-left","").css("margin","0 auto").data("width",tmp)});this.element.data("optimalwidth",d);h="auto"==this.options.width?this.element.width():this.options.width;e="auto"==this.options.height?this.sets.eq(0).children().eq(0).outerHeight(!0):this.options.height;this.sets.css({height:e});b.css({height:e});this.element.data("optimalwidth")>a&&(h=d,a=d/a,this.sets.css($widgetkit.css3({transform:"scale("+
1/a+")"})),b.css("height",e/a));this.sets.each(function(){var b=j(this);b.data("margin-left",(h-b.data("width"))/2)});this.sets.hide().eq(this.current).show();this.gheight=e},next:function(){this.show(this.sets[this.current+1]?this.current+1:0)},previous:function(){this.show(-1<this.current-1?this.current-1:this.sets.length-1)},start:function(){if(!this.timer){var b=this;this.timer=setInterval(function(){!b.hover&&!b.busy&&b.next()},this.options.interval);return this}},stop:function(){if(this.timer){clearInterval(this.timer);
this.tmptimer&&clearTimeout(this.tmptimer);var b=this;this.tmptimer=setTimeout(function(){b.start();this.tmptimer=!1},3E4);this.timer=!1}return this},show:function(b){this.current==b||this.busy||(this.element.trigger("slideset-show",[this.current,b]),this[this[this.options.effect]?this.options.effect:"slide"](b),this.navitems.removeClass("active").eq(b).addClass("active"))},slide:function(b){var a=b>this.current?"left":"right",d=this.sets.eq(b),h=this;this.busy=!0;this.sets.eq(this.current).animate({"margin-left":2*
("left"==a?-1:1)*this.gwidth},{complete:function(){d.css("margin-left","auto").children().hide().css({left:2*("left"==a?1:-1)*h.gwidth});d.show();h.sets.eq(h.current).hide();var e=d.children(),f=0;e.each(function(d){"right"==a&&(d=e.length-1-d);var c=d;setTimeout(function(){e.eq(c).show().animate({left:e.eq(c).data("left")},{complete:function(){if("left"==a&&c==e.length-1||"right"==a&&0==c)h.busy=!1,h.current=b},duration:h.options.duration,easing:h.options.easing})},100*f);f+=1})}})},zoom:function(b){var a=
this.sets.eq(b),d=0,h=this.sets.eq(this.current).children(),e=this;this.busy=!0;this.sets.eq(this.current).children().animate(jQuery.support.opacity?{transform:"scale(0)",opacity:0}:{opacity:0},{complete:function(){d+=1;if(!(-1!=d&&d<h.length-1)){d=-1;var f=a.children().css(jQuery.support.opacity?{transform:"scale(0)",opacity:0}:{opacity:0}),j=0;e.sets.eq(e.current).hide();a.show();f.each(function(c){f.eq(c).css({left:f.eq(c).data("left")}).show();setTimeout(function(){f.eq(c).show().animate(jQuery.support.opacity?
{transform:"scale(1)",opacity:1}:{opacity:1},{complete:function(){c==f.length-1&&(e.busy=!1,e.current=b)},duration:e.options.duration,easing:e.options.easing})},Math.round(e.options.duration/3)*j);j+=1})}},easing:"swing",duration:Math.round(e.options.duration/2)})},deck:function(b){if(!jQuery.support.opacity)return this.zoom(b);var a=b>this.current?"left":"right",d=this.sets.eq(b),h=this.sets.eq(this.current).children(),e=this.sets.eq(b).children();h[b>this.current?"first":"last"]();e[b>this.current?
"last":"first"]();var f=this;"right"==a&&h._reverse();"right"==a&&e._reverse();this.busy=!0;h.each(function(a){var c=j(this);setTimeout(function(){c.animate({transform:"scale(0)",opacity:0},{complete:function(){c.hide();a==h.length-1&&(f.sets.eq(f.current).hide(),e.css({transform:"scale(0)",opacity:0}),d.show(),e.each(function(c){var l=j(this);setTimeout(function(){l.animate({transform:"scale(1)",opacity:1},{complete:function(){c==e.length-1&&(h.show().each(function(c){h.eq(c).css({transform:"scale(1)",
opacity:1,left:h.eq(c).data("left")})}),f.busy=!1,f.current=b)},duration:f.options.duration,easing:f.options.easing})},Math.round(f.options.duration/3)*c)}))},duration:f.options.duration,easing:f.options.easing})},Math.round(f.options.duration/3)*a)})},drops:function(b){if(!jQuery.support.opacity)return this.zoom(b);var a=b>this.current?"left":"right",d=this.sets.eq(b),h=this.sets.eq(this.current).children().css($widgetkit.css3({transition:""})).css($widgetkit.css3({transform:"rotate(0deg)",top:0,
opacity:1})),e=this.sets.eq(b).children().css($widgetkit.css3({transition:""}));h[b>this.current?"first":"last"]();e[b>this.current?"last":"first"]();var f=this;"right"==a&&h._reverse();"right"==a&&e._reverse();this.busy=!0;h.each(function(a){var c=j(this);setTimeout(function(){if(z.transition){c.css($widgetkit.css3({transition:"all "+f.options.duration+"ms ease-out"}));if(a==h.length-1)c.one("webkitTransitionEnd transitionend oTransitionEnd msTransitionEnd",function(){f.sets.eq(f.current).hide();
e.css($widgetkit.css3({top:f.gheight,opacity:0,transform:"rotate(15deg)"}));d.show();e.each(function(c){var l=j(this);setTimeout(function(){l.css($widgetkit.css3({transition:"all "+f.options.duration+"ms ease-in"}));if(c==e.length-1)l.one("webkitTransitionEnd transitionend oTransitionEnd msTransitionEnd",function(){f.busy=!1;f.current=b});l.css($widgetkit.css3({opacity:1,top:0,transform:"rotate(0deg)"}))},Math.round(f.options.duration/3)*c)})});c.css($widgetkit.css3({opacity:0,top:f.gheight,transform:"rotate(15deg)"}))}else c.animate({opacity:0,
top:f.gheight},{complete:function(){c.hide();a==h.length-1&&(f.sets.eq(f.current).hide(),e.css({top:f.gheight,opacity:0}),d.css("margin-left","").show(),e.each(function(c){var l=j(this);setTimeout(function(){l.animate({opacity:1,top:0},{complete:function(){c==e.length-1&&(h.show().each(function(c){h.eq(c).css({opacity:1,top:0})}),f.busy=!1,f.current=b)},duration:f.options.duration,easing:f.options.easing})},Math.round(f.options.duration/3)*c)}))},duration:f.options.duration,easing:f.options.easing})},
20+Math.round(f.options.duration/3)*a)})}});j.fn._reverse||(j.fn._reverse=[].reverse);j.fn[n.prototype.name]=function(){var b=arguments,a=b[0]?b[0]:null;return this.each(function(){var d=j(this);if(n.prototype[a]&&d.data(n.prototype.name)&&"initialize"!=a)d.data(n.prototype.name)[a].apply(d.data(n.prototype.name),Array.prototype.slice.call(b,1));else if(!a||j.isPlainObject(a)){var h=new n;h.element=d;A.push(h);n.prototype.initialize&&h.initialize.apply(h,b);d.data(n.prototype.name,h)}else j.error("Method "+
a+" does not exist on jQuery."+n.prototype.name)})}})(jQuery);
(function(j){function z(c){c=c.split(")");for(var b=j.trim,l=c.length-1,k,a,g,d=1,e=0,f=0,h=1,r,u,p,v=0,q=0;l--;){k=c[l].split("(");a=b(k[0]);g=k[1];r=k=u=p=0;switch(a){case "translateX":v+=parseInt(g,10);continue;case "translateY":q+=parseInt(g,10);continue;case "translate":g=g.split(",");v+=parseInt(g[0],10);q+=parseInt(g[1]||0,10);continue;case "rotate":g=n(g);r=Math.cos(g);k=Math.sin(g);u=-Math.sin(g);p=Math.cos(g);break;case "scaleX":r=g;p=1;break;case "scaleY":r=1;p=g;break;case "scale":g=g.split(",");
r=g[0];p=1<g.length?g[1]:g[0];break;case "skewX":r=p=1;u=Math.tan(n(g));break;case "skewY":r=p=1;k=Math.tan(n(g));break;case "skew":r=p=1;g=g.split(",");u=Math.tan(n(g[0]));k=Math.tan(n(g[1]||0));break;case "matrix":g=g.split(","),r=+g[0],k=+g[1],u=+g[2],p=+g[3],v+=parseInt(g[4],10),q+=parseInt(g[5],10)}a=d*r+e*u;e=d*k+e*p;r=f*r+h*u;h=f*k+h*p;d=a;f=r}return[d,e,f,h,v,q]}function A(c){var b,l,a,d=c[0],g=c[1],e=c[2],f=c[3];d*f-g*e?(b=Math.sqrt(d*d+g*g),d/=b,g/=b,a=d*e+g*f,e-=d*a,f-=g*a,l=Math.sqrt(e*
e+f*f),a/=l,d*(f/l)<g*(e/l)&&(d=-d,g=-g,a=-a,b=-b)):rotate=b=l=a=0;return{translate:[+c[4],+c[5]],rotate:Math.atan2(g,d),scale:[b,l],skew:[a,0]}}function n(c){return~c.indexOf("deg")?parseInt(c,10)*(2*Math.PI/360):~c.indexOf("grad")?parseInt(c,10)*(Math.PI/200):parseFloat(c)}for(var s=document.createElement("div"),s=s.style,b=["OTransform","msTransform","WebkitTransform","MozTransform","transform"],a=b.length,d,h,e,f,y=/Matrix([^)]*)/;a--;)b[a]in s&&(j.support.transform=d=b[a]);d||(j.support.matrixFilter=
h=""===s.filter);s=s=null;j.cssNumber.transform=!0;d&&"transform"!=d?(j.cssProps.transform=d,"MozTransform"==d?e={get:function(c,b){return b?j.css(c,d).split("px").join(""):c.style[d]},set:function(c,b){c.style[d]=/matrix[^)p]*\)/.test(b)?b.replace(/matrix((?:[^,]*,){4})([^,]*),([^)]*)/,"matrix$1$2px,$3px"):b}}:/^1\.[0-5](?:\.|$)/.test(j.fn.jquery)&&(e={get:function(c,b){return b?j.css(c,d.replace(/^ms/,"Ms")):c.style[d]}})):h&&(e={get:function(c,b){var l=b&&c.currentStyle?c.currentStyle:c.style,
a;l&&y.test(l.filter)?(a=RegExp.$1.split(","),a=[a[0].split("=")[1],a[2].split("=")[1],a[1].split("=")[1],a[3].split("=")[1]]):a=[1,0,0,1];a[4]=l?l.left:0;a[5]=l?l.top:0;return"matrix("+a+")"},set:function(c,b,a){var d=c.style,e,f,h;a||(d.zoom=1);b=z(b);if(!a||a.M)if(f=["Matrix(M11="+b[0],"M12="+b[2],"M21="+b[1],"M22="+b[3],"SizingMethod='auto expand'"].join(),h=(e=c.currentStyle)&&e.filter||d.filter||"",d.filter=y.test(h)?h.replace(y,f):h+" progid:DXImageTransform.Microsoft."+f+")",centerOrigin=
j.transform.centerOrigin)d["margin"==centerOrigin?"marginLeft":"left"]=-(c.offsetWidth/2)+c.clientWidth/2+"px",d["margin"==centerOrigin?"marginTop":"top"]=-(c.offsetHeight/2)+c.clientHeight/2+"px";if(!a||a.T)d.left=b[4]+"px",d.top=b[5]+"px"}});e&&(j.cssHooks.transform=e);f=e&&e.get||j.css;j.fx.step.transform=function(c){var b=c.elem,a=c.start,k=c.end,t,g=c.pos,s,B,C,D,r=!1,u=!1,p;s=B=C=D="";if(!a||"string"===typeof a){a||(a=f(b,d));h&&(b.style.zoom=1);t=k.split(a);2==t.length&&(k=t.join(""),c.origin=
a,a="none");"none"==a?a={translate:[0,0],rotate:0,scale:[1,1],skew:[0,0]}:(a=/\(([^,]*),([^,]*),([^,]*),([^,]*),([^,p]*)(?:px)?,([^)p]*)(?:px)?/.exec(a),a=A([a[1],a[2],a[3],a[4],a[5],a[6]]));c.start=a;if(~k.indexOf("matrix"))k=A(z(k));else{k=k.split(")");t=[0,0];for(var v=0,q=[1,1],x=[0,0],y=k.length-1,E=j.trim,m,w;y--;)m=k[y].split("("),w=E(m[0]),m=m[1],"translateX"==w?t[0]+=parseInt(m,10):"translateY"==w?t[1]+=parseInt(m,10):"translate"==w?(m=m.split(","),t[0]+=parseInt(m[0],10),t[1]+=parseInt(m[1]||
0,10)):"rotate"==w?v+=n(m):"scaleX"==w?q[0]*=m:"scaleY"==w?q[1]*=m:"scale"==w?(m=m.split(","),q[0]*=m[0],q[1]*=1<m.length?m[1]:m[0]):"skewX"==w?x[0]+=n(m):"skewY"==w?x[1]+=n(m):"skew"==w&&(m=m.split(","),x[0]+=n(m[0]),x[1]+=n(m[1]||"0"));k={translate:t,rotate:v,scale:q,skew:x}}c.end=k;for(p in a)("rotate"==p?a[p]==k[p]:a[p][0]==k[p][0]&&a[p][1]==k[p][1])&&delete a[p]}a.translate&&(s=" translate("+(a.translate[0]+(k.translate[0]-a.translate[0])*g+0.5|0)+"px,"+(a.translate[1]+(k.translate[1]-a.translate[1])*
g+0.5|0)+"px)",r=!0);void 0!=a.rotate&&(B=" rotate("+(a.rotate+(k.rotate-a.rotate)*g)+"rad)",u=!0);a.scale&&(C=" scale("+(a.scale[0]+(k.scale[0]-a.scale[0])*g)+","+(a.scale[1]+(k.scale[1]-a.scale[1])*g)+")",u=!0);a.skew&&(D=" skew("+(a.skew[0]+(k.skew[0]-a.skew[0])*g)+"rad,"+(a.skew[1]+(k.skew[1]-a.skew[1])*g)+"rad)",u=!0);c=c.origin?c.origin+s+D+C+B:s+B+C+D;e&&e.set?e.set(b,c,{M:u,T:r}):b.style[d]=c};j.transform={centerOrigin:"margin"}})(jQuery);
