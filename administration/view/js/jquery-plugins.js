/**
 * Timeago is a jQuery plugin that makes it easy to support automatically
 * updating fuzzy timestamps (e.g. "4 minutes ago" or "about 1 day ago").
 *
 * @name timeago
 * @version 1.3.0
 * @requires jQuery v1.2.3+
 * @author Ryan McGeary
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 *
 * For usage and examples, visit:
 * http://timeago.yarp.com/
 *
 * Copyright (c) 2008-2013, Ryan McGeary (ryan -[at]- mcgeary [*dot*] org)
 */

(function(factory){if(typeof define==="function"&&define.amd)define(["jquery"],factory);else factory(jQuery)})(function($){$.timeago=function(timestamp){if(timestamp instanceof Date)return inWords(timestamp);else if(typeof timestamp==="string")return inWords($.timeago.parse(timestamp));else if(typeof timestamp==="number")return inWords(new Date(timestamp));else return inWords($.timeago.datetime(timestamp))};var $t=$.timeago;$.extend($.timeago,{settings:{refreshMillis:6E4,allowFuture:false,localeTitle:false,
cutoff:0,strings:{prefixAgo:null,prefixFromNow:null,suffixAgo:"ago",suffixFromNow:"from now",seconds:"less than a minute",minute:"about a minute",minutes:"%d minutes",hour:"about an hour",hours:"about %d hours",day:"a day",days:"%d days",month:"about a month",months:"%d months",year:"about a year",years:"%d years",wordSeparator:" ",numbers:[]}},inWords:function(distanceMillis){var $l=this.settings.strings;var prefix=$l.prefixAgo;var suffix=$l.suffixAgo;if(this.settings.allowFuture)if(distanceMillis<
0){prefix=$l.prefixFromNow;suffix=$l.suffixFromNow}var seconds=Math.abs(distanceMillis)/1E3;var minutes=seconds/60;var hours=minutes/60;var days=hours/24;var years=days/365;function substitute(stringOrFunction,number){var string=$.isFunction(stringOrFunction)?stringOrFunction(number,distanceMillis):stringOrFunction;var value=$l.numbers&&$l.numbers[number]||number;return string.replace(/%d/i,value)}var words=seconds<45&&substitute($l.seconds,Math.round(seconds))||(seconds<90&&substitute($l.minute,
1)||(minutes<45&&substitute($l.minutes,Math.round(minutes))||(minutes<90&&substitute($l.hour,1)||(hours<24&&substitute($l.hours,Math.round(hours))||(hours<42&&substitute($l.day,1)||(days<30&&substitute($l.days,Math.round(days))||(days<45&&substitute($l.month,1)||(days<365&&substitute($l.months,Math.round(days/30))||(years<1.5&&substitute($l.year,1)||substitute($l.years,Math.round(years)))))))))));var separator=$l.wordSeparator||"";if($l.wordSeparator===undefined)separator=" ";return $.trim([prefix,
words,suffix].join(separator))},parse:function(iso8601){var s=$.trim(iso8601);s=s.replace(/\.\d+/,"");s=s.replace(/-/,"/").replace(/-/,"/");s=s.replace(/T/," ").replace(/Z/," UTC");s=s.replace(/([\+\-]\d\d)\:?(\d\d)/," $1$2");return new Date(s)},datetime:function(elem){var iso8601=$t.isTime(elem)?$(elem).attr("datetime"):$(elem).attr("title");return $t.parse(iso8601)},isTime:function(elem){return $(elem).get(0).tagName.toLowerCase()==="time"}});var functions={init:function(){var refresh_el=$.proxy(refresh,
this);refresh_el();var $s=$t.settings;if($s.refreshMillis>0)setInterval(refresh_el,$s.refreshMillis)},update:function(time){$(this).data("timeago",{datetime:$t.parse(time)});refresh.apply(this)},updateFromDOM:function(){$(this).data("timeago",{datetime:$t.parse($t.isTime(this)?$(this).attr("datetime"):$(this).attr("title"))});refresh.apply(this)}};$.fn.timeago=function(action,options){var fn=action?functions[action]:functions.init;if(!fn)throw new Error("Unknown function name '"+action+"' for timeago");
this.each(function(){fn.call(this,options)});return this};function refresh(){var data=prepareData(this);var $s=$t.settings;if(!isNaN(data.datetime))if($s.cutoff==0||distance(data.datetime)<$s.cutoff)$(this).text(inWords(data.datetime));return this}function prepareData(element){element=$(element);if(!element.data("timeago")){element.data("timeago",{datetime:$t.datetime(element)});var text=$.trim(element.text());if($t.settings.localeTitle)element.attr("title",element.data("timeago").datetime.toLocaleString());
else if(text.length>0&&!($t.isTime(element)&&element.attr("title")))element.attr("title",text)}return element.data("timeago")}function inWords(date){return $t.inWords(distance(date))}function distance(date){return(new Date).getTime()-date.getTime()}document.createElement("abbr");document.createElement("time")});



// jQuery-typing
//
// Version: 0.2.0
// Website: http://narf.pl/jquery-typing/
// License: public domain <http://unlicense.org/>
// Author:  Maciej Konieczny <hello@narf.pl>
(function(f){function l(g,h){function d(a){if(!e){e=true;c.start&&c.start(a,b)}}function i(a,j){if(e){clearTimeout(k);k=setTimeout(function(){e=false;c.stop&&c.stop(a,b)},j>=0?j:c.delay)}}var c=f.extend({start:null,stop:null,delay:400},h),b=f(g),e=false,k;b.keypress(d);b.keydown(function(a){if(a.keyCode===8||a.keyCode===46)d(a)});b.keyup(i);b.blur(function(a){i(a,0)})}f.fn.typing=function(g){return this.each(function(h,d){l(d,g)})}})(jQuery);

// Sticky Plugin v1.0.0 for jQuery
// =============
// Author: Anthony Garand
// Improvements by German M. Bravo (Kronuz) and Ruud Kamphuis (ruudk)
// Improvements by Leonardo C. Daronco (daronco)
// Created: 2/14/2011
// Date: 2/12/2012
// Website: http://labs.anthonygarand.com/sticky
// Description: Makes an element on the page stick on the screen as you scroll
// It will only set the 'top' and 'position' of your element, you
// might need to adjust the width in some cases.

(function($){var defaults={topSpacing:0,bottomSpacing:0,className:"is-sticky",wrapperClassName:"sticky-wrapper",center:false,getWidthFrom:""},$window=$(window),$document=$(document),sticked=[],windowHeight=$window.height(),scroller=function(){var scrollTop=$window.scrollTop(),documentHeight=$document.height(),dwh=documentHeight-windowHeight,extra=scrollTop>dwh?dwh-scrollTop:0;for(var i=0;i<sticked.length;i++){var s=sticked[i],elementTop=s.stickyWrapper.offset().top,etse=elementTop-s.topSpacing-extra;
if(scrollTop<=etse){if(s.currentTop!==null){s.stickyElement.css("position","").css("top","");s.stickyElement.removeClass(s.className);s.currentTop=null}}else{var newTop=documentHeight-s.stickyElement.outerHeight()-s.topSpacing-s.bottomSpacing-scrollTop-extra;if(newTop<0)newTop=newTop+s.topSpacing;else newTop=s.topSpacing;if(s.currentTop!=newTop){s.stickyElement.css("position","fixed").css("top",newTop);if(typeof s.getWidthFrom!=="undefined")s.stickyElement.css("width",$(s.getWidthFrom).width());s.stickyElement.addClass(s.className);
s.currentTop=newTop}}}},resizer=function(){windowHeight=$window.height()},methods={init:function(options){var o=$.extend(defaults,options);return this.each(function(){var stickyElement=$(this);var stickyId=stickyElement.attr("id");var wrapper=$("<div></div>").attr("id",stickyId+"-sticky-wrapper").addClass(o.wrapperClassName);stickyElement.wrapAll(wrapper);if(o.center)stickyElement.parent().css({width:stickyElement.outerWidth(),marginLeft:"auto",marginRight:"auto"});if(stickyElement.css("float")==
"right")stickyElement.css({"float":"none"}).parent().css({"float":"right"});var stickyWrapper=stickyElement.parent();stickyWrapper.css("height",stickyElement.outerHeight());sticked.push({topSpacing:o.topSpacing,bottomSpacing:o.bottomSpacing,stickyElement:stickyElement,currentTop:null,stickyWrapper:stickyWrapper,className:o.className,getWidthFrom:o.getWidthFrom})})},update:scroller};if(window.addEventListener){window.addEventListener("scroll",scroller,false);window.addEventListener("resize",resizer,
false)}else if(window.attachEvent){window.attachEvent("onscroll",scroller);window.attachEvent("onresize",resizer)}$.fn.sticky=function(method){if(methods[method])return methods[method].apply(this,Array.prototype.slice.call(arguments,1));else if(typeof method==="object"||!method)return methods.init.apply(this,arguments);else $.error("Method "+method+" does not exist on jQuery.sticky")};$(function(){setTimeout(scroller,0)})})(jQuery);




/*
 HTML5 Shiv v3.7.0 | @afarkas @jdalton @jon_neal @rem | MIT/GPL2 Licensed
*/
(function(l,f){function m(){var a=e.elements;return"string"==typeof a?a.split(" "):a}function i(a){var b=n[a[o]];b||(b={},h++,a[o]=h,n[h]=b);return b}function p(a,b,c){b||(b=f);if(g)return b.createElement(a);c||(c=i(b));b=c.cache[a]?c.cache[a].cloneNode():r.test(a)?(c.cache[a]=c.createElem(a)).cloneNode():c.createElem(a);return b.canHaveChildren&&!s.test(a)?c.frag.appendChild(b):b}function t(a,b){if(!b.cache)b.cache={},b.createElem=a.createElement,b.createFrag=a.createDocumentFragment,b.frag=b.createFrag();
a.createElement=function(c){return!e.shivMethods?b.createElem(c):p(c,a,b)};a.createDocumentFragment=Function("h,f","return function(){var n=f.cloneNode(),c=n.createElement;h.shivMethods&&("+m().join().replace(/[\w\-]+/g,function(a){b.createElem(a);b.frag.createElement(a);return'c("'+a+'")'})+");return n}")(e,b.frag)}function q(a){a||(a=f);var b=i(a);if(e.shivCSS&&!j&&!b.hasCSS){var c,d=a;c=d.createElement("p");d=d.getElementsByTagName("head")[0]||d.documentElement;c.innerHTML="x<style>article,aside,dialog,figcaption,figure,footer,header,hgroup,main,nav,section{display:block}mark{background:#FF0;color:#000}template{display:none}</style>";
c=d.insertBefore(c.lastChild,d.firstChild);b.hasCSS=!!c}g||t(a,b);return a}var k=l.html5||{},s=/^<|^(?:button|map|select|textarea|object|iframe|option|optgroup)$/i,r=/^(?:a|b|code|div|fieldset|h1|h2|h3|h4|h5|h6|i|label|li|ol|p|q|span|strong|style|table|tbody|td|th|tr|ul)$/i,j,o="_html5shiv",h=0,n={},g;(function(){try{var a=f.createElement("a");a.innerHTML="<xyz></xyz>";j="hidden"in a;var b;if(!(b=1==a.childNodes.length)){f.createElement("a");var c=f.createDocumentFragment();b="undefined"==typeof c.cloneNode||
"undefined"==typeof c.createDocumentFragment||"undefined"==typeof c.createElement}g=b}catch(d){g=j=!0}})();var e={elements:k.elements||"abbr article aside audio bdi canvas data datalist details dialog figcaption figure footer header hgroup main mark meter nav output progress section summary template time video",version:"3.7.0",shivCSS:!1!==k.shivCSS,supportsUnknownElements:g,shivMethods:!1!==k.shivMethods,type:"default",shivDocument:q,createElement:p,createDocumentFragment:function(a,b){a||(a=f);
if(g)return a.createDocumentFragment();for(var b=b||i(a),c=b.frag.cloneNode(),d=0,e=m(),h=e.length;d<h;d++)c.createElement(e[d]);return c}};l.html5=e;q(f)})(this,document);

/*
** json2.js by Douglas Crockford
** 02-04-2014
*/

if(typeof JSON!=="object")JSON={};
(function(){function f(n){return n<10?"0"+n:n}if(typeof Date.prototype.toJSON!=="function"){Date.prototype.toJSON=function(){return isFinite(this.valueOf())?this.getUTCFullYear()+"-"+f(this.getUTCMonth()+1)+"-"+f(this.getUTCDate())+"T"+f(this.getUTCHours())+":"+f(this.getUTCMinutes())+":"+f(this.getUTCSeconds())+"Z":null};String.prototype.toJSON=Number.prototype.toJSON=Boolean.prototype.toJSON=function(){return this.valueOf()}}var cx,escapable,gap,indent,meta,rep;function quote(string){escapable.lastIndex=
0;return escapable.test(string)?'"'+string.replace(escapable,function(a){var c=meta[a];return typeof c==="string"?c:"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)})+'"':'"'+string+'"'}function str(key,holder){var i,k,v,length,mind=gap,partial,value=holder[key];if(value&&(typeof value==="object"&&typeof value.toJSON==="function"))value=value.toJSON(key);if(typeof rep==="function")value=rep.call(holder,key,value);switch(typeof value){case "string":return quote(value);case "number":return isFinite(value)?
String(value):"null";case "boolean":case "null":return String(value);case "object":if(!value)return"null";gap+=indent;partial=[];if(Object.prototype.toString.apply(value)==="[object Array]"){length=value.length;for(i=0;i<length;i+=1)partial[i]=str(i,value)||"null";v=partial.length===0?"[]":gap?"[\n"+gap+partial.join(",\n"+gap)+"\n"+mind+"]":"["+partial.join(",")+"]";gap=mind;return v}if(rep&&typeof rep==="object"){length=rep.length;for(i=0;i<length;i+=1)if(typeof rep[i]==="string"){k=rep[i];v=str(k,
value);if(v)partial.push(quote(k)+(gap?": ":":")+v)}}else for(k in value)if(Object.prototype.hasOwnProperty.call(value,k)){v=str(k,value);if(v)partial.push(quote(k)+(gap?": ":":")+v)}v=partial.length===0?"{}":gap?"{\n"+gap+partial.join(",\n"+gap)+"\n"+mind+"}":"{"+partial.join(",")+"}";gap=mind;return v}}if(typeof JSON.stringify!=="function"){escapable=/[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;meta={"\b":"\\b","\t":"\\t",
"\n":"\\n","\f":"\\f","\r":"\\r",'"':'\\"',"\\":"\\\\"};JSON.stringify=function(value,replacer,space){var i;gap="";indent="";if(typeof space==="number")for(i=0;i<space;i+=1)indent+=" ";else if(typeof space==="string")indent=space;rep=replacer;if(replacer&&(typeof replacer!=="function"&&(typeof replacer!=="object"||typeof replacer.length!=="number")))throw new Error("JSON.stringify");return str("",{"":value})}}if(typeof JSON.parse!=="function"){cx=/[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
JSON.parse=function(text,reviver){var j;function walk(holder,key){var k,v,value=holder[key];if(value&&typeof value==="object")for(k in value)if(Object.prototype.hasOwnProperty.call(value,k)){v=walk(value,k);if(v!==undefined)value[k]=v;else delete value[k]}return reviver.call(holder,key,value)}text=String(text);cx.lastIndex=0;if(cx.test(text))text=text.replace(cx,function(a){return"\\u"+("0000"+a.charCodeAt(0).toString(16)).slice(-4)});if(/^[\],:{}\s]*$/.test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,
"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,""))){j=eval("("+text+")");return typeof reviver==="function"?walk({"":j},""):j}throw new SyntaxError("JSON.parse");}}})();


/**
 * jQuery.timers - Timer abstractions for jQuery
 * Written by Blair Mitchelmore (blair DOT mitchelmore AT gmail DOT com)
 * Licensed under the WTFPL (http://sam.zoy.org/wtfpl/).
 * Date: 2009/10/16
 *
 * @author Blair Mitchelmore
 * @version 1.2
 *
 **/

jQuery.fn.extend({everyTime:function(c,a,d,b){return this.each(function(){jQuery.timer.add(this,c,a,d,b)})},oneTime:function(c,a,d){return this.each(function(){jQuery.timer.add(this,c,a,d,1)})},stopTime:function(c,a){return this.each(function(){jQuery.timer.remove(this,c,a)})}});
jQuery.extend({timer:{global:[],guid:1,dataKey:"jQuery.timer",regex:/^([0-9]+(?:\.[0-9]*)?)\s*(.*s)?$/,powers:{ms:1,cs:10,ds:100,s:1E3,das:1E4,hs:1E5,ks:1E6},timeParse:function(c){if(c==undefined||c==null)return null;var a=this.regex.exec(jQuery.trim(c.toString()));return a[2]?parseFloat(a[1])*(this.powers[a[2]]||1):c},add:function(c,a,d,b,e){var g=0;if(jQuery.isFunction(d)){e||(e=b);b=d;d=a}a=jQuery.timer.timeParse(a);if(!(typeof a!="number"||isNaN(a)||a<0)){if(typeof e!="number"||isNaN(e)||e<0)e=
0;e=e||0;var f=jQuery.data(c,this.dataKey)||jQuery.data(c,this.dataKey,{});f[d]||(f[d]={});b.timerID=b.timerID||this.guid++;var h=function(){if(++g>e&&e!==0||b.call(c,g)===false)jQuery.timer.remove(c,d,b)};h.timerID=b.timerID;f[d][b.timerID]||(f[d][b.timerID]=window.setInterval(h,a));this.global.push(c)}},remove:function(c,a,d){var b=jQuery.data(c,this.dataKey),e;if(b){if(a){if(b[a]){if(d){if(d.timerID){window.clearInterval(b[a][d.timerID]);delete b[a][d.timerID]}}else for(d in b[a]){window.clearInterval(b[a][d]);
delete b[a][d]}for(e in b[a])break;if(!e){e=null;delete b[a]}}}else for(a in b)this.remove(c,a,d);for(e in b)break;e||jQuery.removeData(c,this.dataKey)}}}});jQuery(window).bind("unload",function(){jQuery.each(jQuery.timer.global,function(c,a){jQuery.timer.remove(a)})});


 /*
* Placeholder plugin for jQuery
* ---
* Copyright 2010, Daniel Stocks (http://webcloud.se)
* Released under the MIT, BSD, and GPL Licenses.
*/

(function(b){function d(a){this.input=a;a.attr("type")=="password"&&this.handlePassword();b(a[0].form).submit(function(){if(a.hasClass("placeholder")&&a[0].value==a.attr("placeholder"))a[0].value=""})}d.prototype={show:function(a){if(this.input[0].value===""||a&&this.valueIsPlaceholder()){if(this.isPassword)try{this.input[0].setAttribute("type","text")}catch(b){this.input.before(this.fakePassword.show()).hide()}this.input.addClass("placeholder");this.input[0].value=this.input.attr("placeholder")}},
hide:function(){if(this.valueIsPlaceholder()&&this.input.hasClass("placeholder")&&(this.input.removeClass("placeholder"),this.input[0].value="",this.isPassword)){try{this.input[0].setAttribute("type","password")}catch(a){}this.input.show();this.input[0].focus()}},valueIsPlaceholder:function(){return this.input[0].value==this.input.attr("placeholder")},handlePassword:function(){var a=this.input;a.attr("realType","password");this.isPassword=!0;if(b.browser.msie&&a[0].outerHTML){var c=b(a[0].outerHTML.replace(/type=(['"])?password\1/gi,
"type=$1text$1"));this.fakePassword=c.val(a.attr("placeholder")).addClass("placeholder").focus(function(){a.trigger("focus");b(this).hide()});b(a[0].form).submit(function(){c.remove();a.show()})}}};var e=!!("placeholder"in document.createElement("input"));b.fn.placeholder=function(){return e?this:this.each(function(){var a=b(this),c=new d(a);c.show(!0);a.focus(function(){c.hide()});a.blur(function(){c.show(!1)});b.browser.msie&&(b(window).load(function(){a.val()&&a.removeClass("placeholder");c.show(!0)}),
a.focus(function(){if(this.value==""){var a=this.createTextRange();a.collapse(!0);a.moveStart("character",0);a.select()}}))})}})(jQuery);


/*!
 * jQuery Cookie Plugin v1.3.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function(a){if(typeof define==="function"&&define.amd)define(["jquery"],a);else if(typeof exports==="object")a(require("jquery"));else a(jQuery)})(function(f){var a=/\+/g;function d(i){return b.raw?i:encodeURIComponent(i)}function g(i){return b.raw?i:decodeURIComponent(i)}function h(i){return d(b.json?JSON.stringify(i):String(i))}function c(i){if(i.indexOf('"')===0)i=i.slice(1,-1).replace(/\\"/g,'"').replace(/\\\\/g,"\\");try{i=decodeURIComponent(i.replace(a," "));return b.json?JSON.parse(i):i}catch(j){}}
function e(j,i){var k=b.raw?j:c(j);return f.isFunction(i)?i(k):k}var b=f.cookie=function(q,p,v){if(p!==undefined&&!f.isFunction(p)){v=f.extend({},b.defaults,v);if(typeof v.expires==="number"){var r=v.expires,u=v.expires=new Date;u.setTime(+u+r*864E5)}return document.cookie=[d(q),"=",h(p),v.expires?"; expires="+v.expires.toUTCString():"",v.path?"; path="+v.path:"",v.domain?"; domain="+v.domain:"",v.secure?"; secure":""].join("")}var w=q?undefined:{};var s=document.cookie?document.cookie.split("; "):
[];for(var o=0,m=s.length;o<m;o++){var n=s[o].split("=");var j=g(n.shift());var k=n.join("=");if(q&&q===j){w=e(k,p);break}if(!q&&(k=e(k))!==undefined)w[j]=k}return w};b.defaults={};f.removeCookie=function(j,i){if(f.cookie(j)===undefined)return false;f.cookie(j,"",f.extend({},i,{expires:-1}));return!f.cookie(j)}});







