define('lib/jquery.min', [], function(require, exports, module) {});
/*! jQuery v1.9.0 | (c) 2005, 2012 jQuery Foundation, Inc. | jquery.org/license */(function(e,t){"use strict";function n(e){var t=e.length,n=st.type(e);return st.isWindow(e)?!1:1===e.nodeType&&t?!0:"array"===n||"function"!==n&&(0===t||"number"==typeof t&&t>0&&t-1 in e)}function r(e){var t=Tt[e]={};return st.each(e.match(lt)||[],function(e,n){t[n]=!0}),t}function i(e,n,r,i){if(st.acceptData(e)){var o,a,s=st.expando,u="string"==typeof n,l=e.nodeType,c=l?st.cache:e,f=l?e[s]:e[s]&&s;if(f&&c[f]&&(i||c[f].data)||!u||r!==t)return f||(l?e[s]=f=K.pop()||st.guid++:f=s),c[f]||(c[f]={},l||(c[f].toJSON=st.noop)),("object"==typeof n||"function"==typeof n)&&(i?c[f]=st.extend(c[f],n):c[f].data=st.extend(c[f].data,n)),o=c[f],i||(o.data||(o.data={}),o=o.data),r!==t&&(o[st.camelCase(n)]=r),u?(a=o[n],null==a&&(a=o[st.camelCase(n)])):a=o,a}}function o(e,t,n){if(st.acceptData(e)){var r,i,o,a=e.nodeType,u=a?st.cache:e,l=a?e[st.expando]:st.expando;if(u[l]){if(t&&(r=n?u[l]:u[l].data)){st.isArray(t)?t=t.concat(st.map(t,st.camelCase)):t in r?t=[t]:(t=st.camelCase(t),t=t in r?[t]:t.split(" "));for(i=0,o=t.length;o>i;i++)delete r[t[i]];if(!(n?s:st.isEmptyObject)(r))return}(n||(delete u[l].data,s(u[l])))&&(a?st.cleanData([e],!0):st.support.deleteExpando||u!=u.window?delete u[l]:u[l]=null)}}}function a(e,n,r){if(r===t&&1===e.nodeType){var i="data-"+n.replace(Nt,"-$1").toLowerCase();if(r=e.getAttribute(i),"string"==typeof r){try{r="true"===r?!0:"false"===r?!1:"null"===r?null:+r+""===r?+r:wt.test(r)?st.parseJSON(r):r}catch(o){}st.data(e,n,r)}else r=t}return r}function s(e){var t;for(t in e)if(("data"!==t||!st.isEmptyObject(e[t]))&&"toJSON"!==t)return!1;return!0}function u(){return!0}function l(){return!1}function c(e,t){do e=e[t];while(e&&1!==e.nodeType);return e}function f(e,t,n){if(t=t||0,st.isFunction(t))return st.grep(e,function(e,r){var i=!!t.call(e,r,e);return i===n});if(t.nodeType)return st.grep(e,function(e){return e===t===n});if("string"==typeof t){var r=st.grep(e,function(e){return 1===e.nodeType});if(Wt.test(t))return st.filter(t,r,!n);t=st.filter(t,r)}return st.grep(e,function(e){return st.inArray(e,t)>=0===n})}function p(e){var t=zt.split("|"),n=e.createDocumentFragment();if(n.createElement)for(;t.length;)n.createElement(t.pop());return n}function d(e,t){return e.getElementsByTagName(t)[0]||e.appendChild(e.ownerDocument.createElement(t))}function h(e){var t=e.getAttributeNode("type");return e.type=(t&&t.specified)+"/"+e.type,e}function g(e){var t=nn.exec(e.type);return t?e.type=t[1]:e.removeAttribute("type"),e}function m(e,t){for(var n,r=0;null!=(n=e[r]);r++)st._data(n,"globalEval",!t||st._data(t[r],"globalEval"))}function y(e,t){if(1===t.nodeType&&st.hasData(e)){var n,r,i,o=st._data(e),a=st._data(t,o),s=o.events;if(s){delete a.handle,a.events={};for(n in s)for(r=0,i=s[n].length;i>r;r++)st.event.add(t,n,s[n][r])}a.data&&(a.data=st.extend({},a.data))}}function v(e,t){var n,r,i;if(1===t.nodeType){if(n=t.nodeName.toLowerCase(),!st.support.noCloneEvent&&t[st.expando]){r=st._data(t);for(i in r.events)st.removeEvent(t,i,r.handle);t.removeAttribute(st.expando)}"script"===n&&t.text!==e.text?(h(t).text=e.text,g(t)):"object"===n?(t.parentNode&&(t.outerHTML=e.outerHTML),st.support.html5Clone&&e.innerHTML&&!st.trim(t.innerHTML)&&(t.innerHTML=e.innerHTML)):"input"===n&&Zt.test(e.type)?(t.defaultChecked=t.checked=e.checked,t.value!==e.value&&(t.value=e.value)):"option"===n?t.defaultSelected=t.selected=e.defaultSelected:("input"===n||"textarea"===n)&&(t.defaultValue=e.defaultValue)}}function b(e,n){var r,i,o=0,a=e.getElementsByTagName!==t?e.getElementsByTagName(n||"*"):e.querySelectorAll!==t?e.querySelectorAll(n||"*"):t;if(!a)for(a=[],r=e.childNodes||e;null!=(i=r[o]);o++)!n||st.nodeName(i,n)?a.push(i):st.merge(a,b(i,n));return n===t||n&&st.nodeName(e,n)?st.merge([e],a):a}function x(e){Zt.test(e.type)&&(e.defaultChecked=e.checked)}function T(e,t){if(t in e)return t;for(var n=t.charAt(0).toUpperCase()+t.slice(1),r=t,i=Nn.length;i--;)if(t=Nn[i]+n,t in e)return t;return r}function w(e,t){return e=t||e,"none"===st.css(e,"display")||!st.contains(e.ownerDocument,e)}function N(e,t){for(var n,r=[],i=0,o=e.length;o>i;i++)n=e[i],n.style&&(r[i]=st._data(n,"olddisplay"),t?(r[i]||"none"!==n.style.display||(n.style.display=""),""===n.style.display&&w(n)&&(r[i]=st._data(n,"olddisplay",S(n.nodeName)))):r[i]||w(n)||st._data(n,"olddisplay",st.css(n,"display")));for(i=0;o>i;i++)n=e[i],n.style&&(t&&"none"!==n.style.display&&""!==n.style.display||(n.style.display=t?r[i]||"":"none"));return e}function C(e,t,n){var r=mn.exec(t);return r?Math.max(0,r[1]-(n||0))+(r[2]||"px"):t}function k(e,t,n,r,i){for(var o=n===(r?"border":"content")?4:"width"===t?1:0,a=0;4>o;o+=2)"margin"===n&&(a+=st.css(e,n+wn[o],!0,i)),r?("content"===n&&(a-=st.css(e,"padding"+wn[o],!0,i)),"margin"!==n&&(a-=st.css(e,"border"+wn[o]+"Width",!0,i))):(a+=st.css(e,"padding"+wn[o],!0,i),"padding"!==n&&(a+=st.css(e,"border"+wn[o]+"Width",!0,i)));return a}function E(e,t,n){var r=!0,i="width"===t?e.offsetWidth:e.offsetHeight,o=ln(e),a=st.support.boxSizing&&"border-box"===st.css(e,"boxSizing",!1,o);if(0>=i||null==i){if(i=un(e,t,o),(0>i||null==i)&&(i=e.style[t]),yn.test(i))return i;r=a&&(st.support.boxSizingReliable||i===e.style[t]),i=parseFloat(i)||0}return i+k(e,t,n||(a?"border":"content"),r,o)+"px"}function S(e){var t=V,n=bn[e];return n||(n=A(e,t),"none"!==n&&n||(cn=(cn||st("<iframe frameborder='0' width='0' height='0'/>").css("cssText","display:block !important")).appendTo(t.documentElement),t=(cn[0].contentWindow||cn[0].contentDocument).document,t.write("<!doctype html><html><body>"),t.close(),n=A(e,t),cn.detach()),bn[e]=n),n}function A(e,t){var n=st(t.createElement(e)).appendTo(t.body),r=st.css(n[0],"display");return n.remove(),r}function j(e,t,n,r){var i;if(st.isArray(t))st.each(t,function(t,i){n||kn.test(e)?r(e,i):j(e+"["+("object"==typeof i?t:"")+"]",i,n,r)});else if(n||"object"!==st.type(t))r(e,t);else for(i in t)j(e+"["+i+"]",t[i],n,r)}function D(e){return function(t,n){"string"!=typeof t&&(n=t,t="*");var r,i=0,o=t.toLowerCase().match(lt)||[];if(st.isFunction(n))for(;r=o[i++];)"+"===r[0]?(r=r.slice(1)||"*",(e[r]=e[r]||[]).unshift(n)):(e[r]=e[r]||[]).push(n)}}function L(e,n,r,i){function o(u){var l;return a[u]=!0,st.each(e[u]||[],function(e,u){var c=u(n,r,i);return"string"!=typeof c||s||a[c]?s?!(l=c):t:(n.dataTypes.unshift(c),o(c),!1)}),l}var a={},s=e===$n;return o(n.dataTypes[0])||!a["*"]&&o("*")}function H(e,n){var r,i,o=st.ajaxSettings.flatOptions||{};for(r in n)n[r]!==t&&((o[r]?e:i||(i={}))[r]=n[r]);return i&&st.extend(!0,e,i),e}function M(e,n,r){var i,o,a,s,u=e.contents,l=e.dataTypes,c=e.responseFields;for(o in c)o in r&&(n[c[o]]=r[o]);for(;"*"===l[0];)l.shift(),i===t&&(i=e.mimeType||n.getResponseHeader("Content-Type"));if(i)for(o in u)if(u[o]&&u[o].test(i)){l.unshift(o);break}if(l[0]in r)a=l[0];else{for(o in r){if(!l[0]||e.converters[o+" "+l[0]]){a=o;break}s||(s=o)}a=a||s}return a?(a!==l[0]&&l.unshift(a),r[a]):t}function q(e,t){var n,r,i,o,a={},s=0,u=e.dataTypes.slice(),l=u[0];if(e.dataFilter&&(t=e.dataFilter(t,e.dataType)),u[1])for(n in e.converters)a[n.toLowerCase()]=e.converters[n];for(;i=u[++s];)if("*"!==i){if("*"!==l&&l!==i){if(n=a[l+" "+i]||a["* "+i],!n)for(r in a)if(o=r.split(" "),o[1]===i&&(n=a[l+" "+o[0]]||a["* "+o[0]])){n===!0?n=a[r]:a[r]!==!0&&(i=o[0],u.splice(s--,0,i));break}if(n!==!0)if(n&&e["throws"])t=n(t);else try{t=n(t)}catch(c){return{state:"parsererror",error:n?c:"No conversion from "+l+" to "+i}}}l=i}return{state:"success",data:t}}function _(){try{return new e.XMLHttpRequest}catch(t){}}function F(){try{return new e.ActiveXObject("Microsoft.XMLHTTP")}catch(t){}}function O(){return setTimeout(function(){Qn=t}),Qn=st.now()}function B(e,t){st.each(t,function(t,n){for(var r=(rr[t]||[]).concat(rr["*"]),i=0,o=r.length;o>i;i++)if(r[i].call(e,t,n))return})}function P(e,t,n){var r,i,o=0,a=nr.length,s=st.Deferred().always(function(){delete u.elem}),u=function(){if(i)return!1;for(var t=Qn||O(),n=Math.max(0,l.startTime+l.duration-t),r=n/l.duration||0,o=1-r,a=0,u=l.tweens.length;u>a;a++)l.tweens[a].run(o);return s.notifyWith(e,[l,o,n]),1>o&&u?n:(s.resolveWith(e,[l]),!1)},l=s.promise({elem:e,props:st.extend({},t),opts:st.extend(!0,{specialEasing:{}},n),originalProperties:t,originalOptions:n,startTime:Qn||O(),duration:n.duration,tweens:[],createTween:function(t,n){var r=st.Tween(e,l.opts,t,n,l.opts.specialEasing[t]||l.opts.easing);return l.tweens.push(r),r},stop:function(t){var n=0,r=t?l.tweens.length:0;if(i)return this;for(i=!0;r>n;n++)l.tweens[n].run(1);return t?s.resolveWith(e,[l,t]):s.rejectWith(e,[l,t]),this}}),c=l.props;for(R(c,l.opts.specialEasing);a>o;o++)if(r=nr[o].call(l,e,c,l.opts))return r;return B(l,c),st.isFunction(l.opts.start)&&l.opts.start.call(e,l),st.fx.timer(st.extend(u,{elem:e,anim:l,queue:l.opts.queue})),l.progress(l.opts.progress).done(l.opts.done,l.opts.complete).fail(l.opts.fail).always(l.opts.always)}function R(e,t){var n,r,i,o,a;for(n in e)if(r=st.camelCase(n),i=t[r],o=e[n],st.isArray(o)&&(i=o[1],o=e[n]=o[0]),n!==r&&(e[r]=o,delete e[n]),a=st.cssHooks[r],a&&"expand"in a){o=a.expand(o),delete e[r];for(n in o)n in e||(e[n]=o[n],t[n]=i)}else t[r]=i}function W(e,t,n){var r,i,o,a,s,u,l,c,f,p=this,d=e.style,h={},g=[],m=e.nodeType&&w(e);n.queue||(c=st._queueHooks(e,"fx"),null==c.unqueued&&(c.unqueued=0,f=c.empty.fire,c.empty.fire=function(){c.unqueued||f()}),c.unqueued++,p.always(function(){p.always(function(){c.unqueued--,st.queue(e,"fx").length||c.empty.fire()})})),1===e.nodeType&&("height"in t||"width"in t)&&(n.overflow=[d.overflow,d.overflowX,d.overflowY],"inline"===st.css(e,"display")&&"none"===st.css(e,"float")&&(st.support.inlineBlockNeedsLayout&&"inline"!==S(e.nodeName)?d.zoom=1:d.display="inline-block")),n.overflow&&(d.overflow="hidden",st.support.shrinkWrapBlocks||p.done(function(){d.overflow=n.overflow[0],d.overflowX=n.overflow[1],d.overflowY=n.overflow[2]}));for(r in t)if(o=t[r],Zn.exec(o)){if(delete t[r],u=u||"toggle"===o,o===(m?"hide":"show"))continue;g.push(r)}if(a=g.length){s=st._data(e,"fxshow")||st._data(e,"fxshow",{}),"hidden"in s&&(m=s.hidden),u&&(s.hidden=!m),m?st(e).show():p.done(function(){st(e).hide()}),p.done(function(){var t;st._removeData(e,"fxshow");for(t in h)st.style(e,t,h[t])});for(r=0;a>r;r++)i=g[r],l=p.createTween(i,m?s[i]:0),h[i]=s[i]||st.style(e,i),i in s||(s[i]=l.start,m&&(l.end=l.start,l.start="width"===i||"height"===i?1:0))}}function $(e,t,n,r,i){return new $.prototype.init(e,t,n,r,i)}function I(e,t){var n,r={height:e},i=0;for(t=t?1:0;4>i;i+=2-t)n=wn[i],r["margin"+n]=r["padding"+n]=e;return t&&(r.opacity=r.width=e),r}function z(e){return st.isWindow(e)?e:9===e.nodeType?e.defaultView||e.parentWindow:!1}var X,U,V=e.document,Y=e.location,J=e.jQuery,G=e.$,Q={},K=[],Z="1.9.0",et=K.concat,tt=K.push,nt=K.slice,rt=K.indexOf,it=Q.toString,ot=Q.hasOwnProperty,at=Z.trim,st=function(e,t){return new st.fn.init(e,t,X)},ut=/[+-]?(?:\d*\.|)\d+(?:[eE][+-]?\d+|)/.source,lt=/\S+/g,ct=/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,ft=/^(?:(<[\w\W]+>)[^>]*|#([\w-]*))$/,pt=/^<(\w+)\s*\/?>(?:<\/\1>|)$/,dt=/^[\],:{}\s]*$/,ht=/(?:^|:|,)(?:\s*\[)+/g,gt=/\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,mt=/"[^"\\\r\n]*"|true|false|null|-?(?:\d+\.|)\d+(?:[eE][+-]?\d+|)/g,yt=/^-ms-/,vt=/-([\da-z])/gi,bt=function(e,t){return t.toUpperCase()},xt=function(){V.addEventListener?(V.removeEventListener("DOMContentLoaded",xt,!1),st.ready()):"complete"===V.readyState&&(V.detachEvent("onreadystatechange",xt),st.ready())};st.fn=st.prototype={jquery:Z,constructor:st,init:function(e,n,r){var i,o;if(!e)return this;if("string"==typeof e){if(i="<"===e.charAt(0)&&">"===e.charAt(e.length-1)&&e.length>=3?[null,e,null]:ft.exec(e),!i||!i[1]&&n)return!n||n.jquery?(n||r).find(e):this.constructor(n).find(e);if(i[1]){if(n=n instanceof st?n[0]:n,st.merge(this,st.parseHTML(i[1],n&&n.nodeType?n.ownerDocument||n:V,!0)),pt.test(i[1])&&st.isPlainObject(n))for(i in n)st.isFunction(this[i])?this[i](n[i]):this.attr(i,n[i]);return this}if(o=V.getElementById(i[2]),o&&o.parentNode){if(o.id!==i[2])return r.find(e);this.length=1,this[0]=o}return this.context=V,this.selector=e,this}return e.nodeType?(this.context=this[0]=e,this.length=1,this):st.isFunction(e)?r.ready(e):(e.selector!==t&&(this.selector=e.selector,this.context=e.context),st.makeArray(e,this))},selector:"",length:0,size:function(){return this.length},toArray:function(){return nt.call(this)},get:function(e){return null==e?this.toArray():0>e?this[this.length+e]:this[e]},pushStack:function(e){var t=st.merge(this.constructor(),e);return t.prevObject=this,t.context=this.context,t},each:function(e,t){return st.each(this,e,t)},ready:function(e){return st.ready.promise().done(e),this},slice:function(){return this.pushStack(nt.apply(this,arguments))},first:function(){return this.eq(0)},last:function(){return this.eq(-1)},eq:function(e){var t=this.length,n=+e+(0>e?t:0);return this.pushStack(n>=0&&t>n?[this[n]]:[])},map:function(e){return this.pushStack(st.map(this,function(t,n){return e.call(t,n,t)}))},end:function(){return this.prevObject||this.constructor(null)},push:tt,sort:[].sort,splice:[].splice},st.fn.init.prototype=st.fn,st.extend=st.fn.extend=function(){var e,n,r,i,o,a,s=arguments[0]||{},u=1,l=arguments.length,c=!1;for("boolean"==typeof s&&(c=s,s=arguments[1]||{},u=2),"object"==typeof s||st.isFunction(s)||(s={}),l===u&&(s=this,--u);l>u;u++)if(null!=(e=arguments[u]))for(n in e)r=s[n],i=e[n],s!==i&&(c&&i&&(st.isPlainObject(i)||(o=st.isArray(i)))?(o?(o=!1,a=r&&st.isArray(r)?r:[]):a=r&&st.isPlainObject(r)?r:{},s[n]=st.extend(c,a,i)):i!==t&&(s[n]=i));return s},st.extend({noConflict:function(t){return e.$===st&&(e.$=G),t&&e.jQuery===st&&(e.jQuery=J),st},isReady:!1,readyWait:1,holdReady:function(e){e?st.readyWait++:st.ready(!0)},ready:function(e){if(e===!0?!--st.readyWait:!st.isReady){if(!V.body)return setTimeout(st.ready);st.isReady=!0,e!==!0&&--st.readyWait>0||(U.resolveWith(V,[st]),st.fn.trigger&&st(V).trigger("ready").off("ready"))}},isFunction:function(e){return"function"===st.type(e)},isArray:Array.isArray||function(e){return"array"===st.type(e)},isWindow:function(e){return null!=e&&e==e.window},isNumeric:function(e){return!isNaN(parseFloat(e))&&isFinite(e)},type:function(e){return null==e?e+"":"object"==typeof e||"function"==typeof e?Q[it.call(e)]||"object":typeof e},isPlainObject:function(e){if(!e||"object"!==st.type(e)||e.nodeType||st.isWindow(e))return!1;try{if(e.constructor&&!ot.call(e,"constructor")&&!ot.call(e.constructor.prototype,"isPrototypeOf"))return!1}catch(n){return!1}var r;for(r in e);return r===t||ot.call(e,r)},isEmptyObject:function(e){var t;for(t in e)return!1;return!0},error:function(e){throw Error(e)},parseHTML:function(e,t,n){if(!e||"string"!=typeof e)return null;"boolean"==typeof t&&(n=t,t=!1),t=t||V;var r=pt.exec(e),i=!n&&[];return r?[t.createElement(r[1])]:(r=st.buildFragment([e],t,i),i&&st(i).remove(),st.merge([],r.childNodes))},parseJSON:function(n){return e.JSON&&e.JSON.parse?e.JSON.parse(n):null===n?n:"string"==typeof n&&(n=st.trim(n),n&&dt.test(n.replace(gt,"@").replace(mt,"]").replace(ht,"")))?Function("return "+n)():(st.error("Invalid JSON: "+n),t)},parseXML:function(n){var r,i;if(!n||"string"!=typeof n)return null;try{e.DOMParser?(i=new DOMParser,r=i.parseFromString(n,"text/xml")):(r=new ActiveXObject("Microsoft.XMLDOM"),r.async="false",r.loadXML(n))}catch(o){r=t}return r&&r.documentElement&&!r.getElementsByTagName("parsererror").length||st.error("Invalid XML: "+n),r},noop:function(){},globalEval:function(t){t&&st.trim(t)&&(e.execScript||function(t){e.eval.call(e,t)})(t)},camelCase:function(e){return e.replace(yt,"ms-").replace(vt,bt)},nodeName:function(e,t){return e.nodeName&&e.nodeName.toLowerCase()===t.toLowerCase()},each:function(e,t,r){var i,o=0,a=e.length,s=n(e);if(r){if(s)for(;a>o&&(i=t.apply(e[o],r),i!==!1);o++);else for(o in e)if(i=t.apply(e[o],r),i===!1)break}else if(s)for(;a>o&&(i=t.call(e[o],o,e[o]),i!==!1);o++);else for(o in e)if(i=t.call(e[o],o,e[o]),i===!1)break;return e},trim:at&&!at.call("\ufeff\u00a0")?function(e){return null==e?"":at.call(e)}:function(e){return null==e?"":(e+"").replace(ct,"")},makeArray:function(e,t){var r=t||[];return null!=e&&(n(Object(e))?st.merge(r,"string"==typeof e?[e]:e):tt.call(r,e)),r},inArray:function(e,t,n){var r;if(t){if(rt)return rt.call(t,e,n);for(r=t.length,n=n?0>n?Math.max(0,r+n):n:0;r>n;n++)if(n in t&&t[n]===e)return n}return-1},merge:function(e,n){var r=n.length,i=e.length,o=0;if("number"==typeof r)for(;r>o;o++)e[i++]=n[o];else for(;n[o]!==t;)e[i++]=n[o++];return e.length=i,e},grep:function(e,t,n){var r,i=[],o=0,a=e.length;for(n=!!n;a>o;o++)r=!!t(e[o],o),n!==r&&i.push(e[o]);return i},map:function(e,t,r){var i,o=0,a=e.length,s=n(e),u=[];if(s)for(;a>o;o++)i=t(e[o],o,r),null!=i&&(u[u.length]=i);else for(o in e)i=t(e[o],o,r),null!=i&&(u[u.length]=i);return et.apply([],u)},guid:1,proxy:function(e,n){var r,i,o;return"string"==typeof n&&(r=e[n],n=e,e=r),st.isFunction(e)?(i=nt.call(arguments,2),o=function(){return e.apply(n||this,i.concat(nt.call(arguments)))},o.guid=e.guid=e.guid||st.guid++,o):t},access:function(e,n,r,i,o,a,s){var u=0,l=e.length,c=null==r;if("object"===st.type(r)){o=!0;for(u in r)st.access(e,n,u,r[u],!0,a,s)}else if(i!==t&&(o=!0,st.isFunction(i)||(s=!0),c&&(s?(n.call(e,i),n=null):(c=n,n=function(e,t,n){return c.call(st(e),n)})),n))for(;l>u;u++)n(e[u],r,s?i:i.call(e[u],u,n(e[u],r)));return o?e:c?n.call(e):l?n(e[0],r):a},now:function(){return(new Date).getTime()}}),st.ready.promise=function(t){if(!U)if(U=st.Deferred(),"complete"===V.readyState)setTimeout(st.ready);else if(V.addEventListener)V.addEventListener("DOMContentLoaded",xt,!1),e.addEventListener("load",st.ready,!1);else{V.attachEvent("onreadystatechange",xt),e.attachEvent("onload",st.ready);var n=!1;try{n=null==e.frameElement&&V.documentElement}catch(r){}n&&n.doScroll&&function i(){if(!st.isReady){try{n.doScroll("left")}catch(e){return setTimeout(i,50)}st.ready()}}()}return U.promise(t)},st.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),function(e,t){Q["[object "+t+"]"]=t.toLowerCase()}),X=st(V);var Tt={};st.Callbacks=function(e){e="string"==typeof e?Tt[e]||r(e):st.extend({},e);var n,i,o,a,s,u,l=[],c=!e.once&&[],f=function(t){for(n=e.memory&&t,i=!0,u=a||0,a=0,s=l.length,o=!0;l&&s>u;u++)if(l[u].apply(t[0],t[1])===!1&&e.stopOnFalse){n=!1;break}o=!1,l&&(c?c.length&&f(c.shift()):n?l=[]:p.disable())},p={add:function(){if(l){var t=l.length;(function r(t){st.each(t,function(t,n){var i=st.type(n);"function"===i?e.unique&&p.has(n)||l.push(n):n&&n.length&&"string"!==i&&r(n)})})(arguments),o?s=l.length:n&&(a=t,f(n))}return this},remove:function(){return l&&st.each(arguments,function(e,t){for(var n;(n=st.inArray(t,l,n))>-1;)l.splice(n,1),o&&(s>=n&&s--,u>=n&&u--)}),this},has:function(e){return st.inArray(e,l)>-1},empty:function(){return l=[],this},disable:function(){return l=c=n=t,this},disabled:function(){return!l},lock:function(){return c=t,n||p.disable(),this},locked:function(){return!c},fireWith:function(e,t){return t=t||[],t=[e,t.slice?t.slice():t],!l||i&&!c||(o?c.push(t):f(t)),this},fire:function(){return p.fireWith(this,arguments),this},fired:function(){return!!i}};return p},st.extend({Deferred:function(e){var t=[["resolve","done",st.Callbacks("once memory"),"resolved"],["reject","fail",st.Callbacks("once memory"),"rejected"],["notify","progress",st.Callbacks("memory")]],n="pending",r={state:function(){return n},always:function(){return i.done(arguments).fail(arguments),this},then:function(){var e=arguments;return st.Deferred(function(n){st.each(t,function(t,o){var a=o[0],s=st.isFunction(e[t])&&e[t];i[o[1]](function(){var e=s&&s.apply(this,arguments);e&&st.isFunction(e.promise)?e.promise().done(n.resolve).fail(n.reject).progress(n.notify):n[a+"With"](this===r?n.promise():this,s?[e]:arguments)})}),e=null}).promise()},promise:function(e){return null!=e?st.extend(e,r):r}},i={};return r.pipe=r.then,st.each(t,function(e,o){var a=o[2],s=o[3];r[o[1]]=a.add,s&&a.add(function(){n=s},t[1^e][2].disable,t[2][2].lock),i[o[0]]=function(){return i[o[0]+"With"](this===i?r:this,arguments),this},i[o[0]+"With"]=a.fireWith}),r.promise(i),e&&e.call(i,i),i},when:function(e){var t,n,r,i=0,o=nt.call(arguments),a=o.length,s=1!==a||e&&st.isFunction(e.promise)?a:0,u=1===s?e:st.Deferred(),l=function(e,n,r){return function(i){n[e]=this,r[e]=arguments.length>1?nt.call(arguments):i,r===t?u.notifyWith(n,r):--s||u.resolveWith(n,r)}};if(a>1)for(t=Array(a),n=Array(a),r=Array(a);a>i;i++)o[i]&&st.isFunction(o[i].promise)?o[i].promise().done(l(i,r,o)).fail(u.reject).progress(l(i,n,t)):--s;return s||u.resolveWith(r,o),u.promise()}}),st.support=function(){var n,r,i,o,a,s,u,l,c,f,p=V.createElement("div");if(p.setAttribute("className","t"),p.innerHTML="  <link/><table></table><a href='/a'>a</a><input type='checkbox'/>",r=p.getElementsByTagName("*"),i=p.getElementsByTagName("a")[0],!r||!i||!r.length)return{};o=V.createElement("select"),a=o.appendChild(V.createElement("option")),s=p.getElementsByTagName("input")[0],i.style.cssText="top:1px;float:left;opacity:.5",n={getSetAttribute:"t"!==p.className,leadingWhitespace:3===p.firstChild.nodeType,tbody:!p.getElementsByTagName("tbody").length,htmlSerialize:!!p.getElementsByTagName("link").length,style:/top/.test(i.getAttribute("style")),hrefNormalized:"/a"===i.getAttribute("href"),opacity:/^0.5/.test(i.style.opacity),cssFloat:!!i.style.cssFloat,checkOn:!!s.value,optSelected:a.selected,enctype:!!V.createElement("form").enctype,html5Clone:"<:nav></:nav>"!==V.createElement("nav").cloneNode(!0).outerHTML,boxModel:"CSS1Compat"===V.compatMode,deleteExpando:!0,noCloneEvent:!0,inlineBlockNeedsLayout:!1,shrinkWrapBlocks:!1,reliableMarginRight:!0,boxSizingReliable:!0,pixelPosition:!1},s.checked=!0,n.noCloneChecked=s.cloneNode(!0).checked,o.disabled=!0,n.optDisabled=!a.disabled;try{delete p.test}catch(d){n.deleteExpando=!1}s=V.createElement("input"),s.setAttribute("value",""),n.input=""===s.getAttribute("value"),s.value="t",s.setAttribute("type","radio"),n.radioValue="t"===s.value,s.setAttribute("checked","t"),s.setAttribute("name","t"),u=V.createDocumentFragment(),u.appendChild(s),n.appendChecked=s.checked,n.checkClone=u.cloneNode(!0).cloneNode(!0).lastChild.checked,p.attachEvent&&(p.attachEvent("onclick",function(){n.noCloneEvent=!1}),p.cloneNode(!0).click());for(f in{submit:!0,change:!0,focusin:!0})p.setAttribute(l="on"+f,"t"),n[f+"Bubbles"]=l in e||p.attributes[l].expando===!1;return p.style.backgroundClip="content-box",p.cloneNode(!0).style.backgroundClip="",n.clearCloneStyle="content-box"===p.style.backgroundClip,st(function(){var r,i,o,a="padding:0;margin:0;border:0;display:block;box-sizing:content-box;-moz-box-sizing:content-box;-webkit-box-sizing:content-box;",s=V.getElementsByTagName("body")[0];s&&(r=V.createElement("div"),r.style.cssText="border:0;width:0;height:0;position:absolute;top:0;left:-9999px;margin-top:1px",s.appendChild(r).appendChild(p),p.innerHTML="<table><tr><td></td><td>t</td></tr></table>",o=p.getElementsByTagName("td"),o[0].style.cssText="padding:0;margin:0;border:0;display:none",c=0===o[0].offsetHeight,o[0].style.display="",o[1].style.display="none",n.reliableHiddenOffsets=c&&0===o[0].offsetHeight,p.innerHTML="",p.style.cssText="box-sizing:border-box;-moz-box-sizing:border-box;-webkit-box-sizing:border-box;padding:1px;border:1px;display:block;width:4px;margin-top:1%;position:absolute;top:1%;",n.boxSizing=4===p.offsetWidth,n.doesNotIncludeMarginInBodyOffset=1!==s.offsetTop,e.getComputedStyle&&(n.pixelPosition="1%"!==(e.getComputedStyle(p,null)||{}).top,n.boxSizingReliable="4px"===(e.getComputedStyle(p,null)||{width:"4px"}).width,i=p.appendChild(V.createElement("div")),i.style.cssText=p.style.cssText=a,i.style.marginRight=i.style.width="0",p.style.width="1px",n.reliableMarginRight=!parseFloat((e.getComputedStyle(i,null)||{}).marginRight)),p.style.zoom!==t&&(p.innerHTML="",p.style.cssText=a+"width:1px;padding:1px;display:inline;zoom:1",n.inlineBlockNeedsLayout=3===p.offsetWidth,p.style.display="block",p.innerHTML="<div></div>",p.firstChild.style.width="5px",n.shrinkWrapBlocks=3!==p.offsetWidth,s.style.zoom=1),s.removeChild(r),r=p=o=i=null)}),r=o=u=a=i=s=null,n}();var wt=/(?:\{[\s\S]*\}|\[[\s\S]*\])$/,Nt=/([A-Z])/g;st.extend({cache:{},expando:"jQuery"+(Z+Math.random()).replace(/\D/g,""),noData:{embed:!0,object:"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000",applet:!0},hasData:function(e){return e=e.nodeType?st.cache[e[st.expando]]:e[st.expando],!!e&&!s(e)},data:function(e,t,n){return i(e,t,n,!1)},removeData:function(e,t){return o(e,t,!1)},_data:function(e,t,n){return i(e,t,n,!0)},_removeData:function(e,t){return o(e,t,!0)},acceptData:function(e){var t=e.nodeName&&st.noData[e.nodeName.toLowerCase()];return!t||t!==!0&&e.getAttribute("classid")===t}}),st.fn.extend({data:function(e,n){var r,i,o=this[0],s=0,u=null;if(e===t){if(this.length&&(u=st.data(o),1===o.nodeType&&!st._data(o,"parsedAttrs"))){for(r=o.attributes;r.length>s;s++)i=r[s].name,i.indexOf("data-")||(i=st.camelCase(i.substring(5)),a(o,i,u[i]));st._data(o,"parsedAttrs",!0)}return u}return"object"==typeof e?this.each(function(){st.data(this,e)}):st.access(this,function(n){return n===t?o?a(o,e,st.data(o,e)):null:(this.each(function(){st.data(this,e,n)}),t)},null,n,arguments.length>1,null,!0)},removeData:function(e){return this.each(function(){st.removeData(this,e)})}}),st.extend({queue:function(e,n,r){var i;return e?(n=(n||"fx")+"queue",i=st._data(e,n),r&&(!i||st.isArray(r)?i=st._data(e,n,st.makeArray(r)):i.push(r)),i||[]):t},dequeue:function(e,t){t=t||"fx";var n=st.queue(e,t),r=n.length,i=n.shift(),o=st._queueHooks(e,t),a=function(){st.dequeue(e,t)};"inprogress"===i&&(i=n.shift(),r--),o.cur=i,i&&("fx"===t&&n.unshift("inprogress"),delete o.stop,i.call(e,a,o)),!r&&o&&o.empty.fire()},_queueHooks:function(e,t){var n=t+"queueHooks";return st._data(e,n)||st._data(e,n,{empty:st.Callbacks("once memory").add(function(){st._removeData(e,t+"queue"),st._removeData(e,n)})})}}),st.fn.extend({queue:function(e,n){var r=2;return"string"!=typeof e&&(n=e,e="fx",r--),r>arguments.length?st.queue(this[0],e):n===t?this:this.each(function(){var t=st.queue(this,e,n);st._queueHooks(this,e),"fx"===e&&"inprogress"!==t[0]&&st.dequeue(this,e)})},dequeue:function(e){return this.each(function(){st.dequeue(this,e)})},delay:function(e,t){return e=st.fx?st.fx.speeds[e]||e:e,t=t||"fx",this.queue(t,function(t,n){var r=setTimeout(t,e);n.stop=function(){clearTimeout(r)}})},clearQueue:function(e){return this.queue(e||"fx",[])},promise:function(e,n){var r,i=1,o=st.Deferred(),a=this,s=this.length,u=function(){--i||o.resolveWith(a,[a])};for("string"!=typeof e&&(n=e,e=t),e=e||"fx";s--;)r=st._data(a[s],e+"queueHooks"),r&&r.empty&&(i++,r.empty.add(u));return u(),o.promise(n)}});var Ct,kt,Et=/[\t\r\n]/g,St=/\r/g,At=/^(?:input|select|textarea|button|object)$/i,jt=/^(?:a|area)$/i,Dt=/^(?:checked|selected|autofocus|autoplay|async|controls|defer|disabled|hidden|loop|multiple|open|readonly|required|scoped)$/i,Lt=/^(?:checked|selected)$/i,Ht=st.support.getSetAttribute,Mt=st.support.input;st.fn.extend({attr:function(e,t){return st.access(this,st.attr,e,t,arguments.length>1)},removeAttr:function(e){return this.each(function(){st.removeAttr(this,e)})},prop:function(e,t){return st.access(this,st.prop,e,t,arguments.length>1)},removeProp:function(e){return e=st.propFix[e]||e,this.each(function(){try{this[e]=t,delete this[e]}catch(n){}})},addClass:function(e){var t,n,r,i,o,a=0,s=this.length,u="string"==typeof e&&e;if(st.isFunction(e))return this.each(function(t){st(this).addClass(e.call(this,t,this.className))});if(u)for(t=(e||"").match(lt)||[];s>a;a++)if(n=this[a],r=1===n.nodeType&&(n.className?(" "+n.className+" ").replace(Et," "):" ")){for(o=0;i=t[o++];)0>r.indexOf(" "+i+" ")&&(r+=i+" ");n.className=st.trim(r)}return this},removeClass:function(e){var t,n,r,i,o,a=0,s=this.length,u=0===arguments.length||"string"==typeof e&&e;if(st.isFunction(e))return this.each(function(t){st(this).removeClass(e.call(this,t,this.className))});if(u)for(t=(e||"").match(lt)||[];s>a;a++)if(n=this[a],r=1===n.nodeType&&(n.className?(" "+n.className+" ").replace(Et," "):"")){for(o=0;i=t[o++];)for(;r.indexOf(" "+i+" ")>=0;)r=r.replace(" "+i+" "," ");n.className=e?st.trim(r):""}return this},toggleClass:function(e,t){var n=typeof e,r="boolean"==typeof t;return st.isFunction(e)?this.each(function(n){st(this).toggleClass(e.call(this,n,this.className,t),t)}):this.each(function(){if("string"===n)for(var i,o=0,a=st(this),s=t,u=e.match(lt)||[];i=u[o++];)s=r?s:!a.hasClass(i),a[s?"addClass":"removeClass"](i);else("undefined"===n||"boolean"===n)&&(this.className&&st._data(this,"__className__",this.className),this.className=this.className||e===!1?"":st._data(this,"__className__")||"")})},hasClass:function(e){for(var t=" "+e+" ",n=0,r=this.length;r>n;n++)if(1===this[n].nodeType&&(" "+this[n].className+" ").replace(Et," ").indexOf(t)>=0)return!0;return!1},val:function(e){var n,r,i,o=this[0];{if(arguments.length)return i=st.isFunction(e),this.each(function(r){var o,a=st(this);1===this.nodeType&&(o=i?e.call(this,r,a.val()):e,null==o?o="":"number"==typeof o?o+="":st.isArray(o)&&(o=st.map(o,function(e){return null==e?"":e+""})),n=st.valHooks[this.type]||st.valHooks[this.nodeName.toLowerCase()],n&&"set"in n&&n.set(this,o,"value")!==t||(this.value=o))});if(o)return n=st.valHooks[o.type]||st.valHooks[o.nodeName.toLowerCase()],n&&"get"in n&&(r=n.get(o,"value"))!==t?r:(r=o.value,"string"==typeof r?r.replace(St,""):null==r?"":r)}}}),st.extend({valHooks:{option:{get:function(e){var t=e.attributes.value;return!t||t.specified?e.value:e.text}},select:{get:function(e){for(var t,n,r=e.options,i=e.selectedIndex,o="select-one"===e.type||0>i,a=o?null:[],s=o?i+1:r.length,u=0>i?s:o?i:0;s>u;u++)if(n=r[u],!(!n.selected&&u!==i||(st.support.optDisabled?n.disabled:null!==n.getAttribute("disabled"))||n.parentNode.disabled&&st.nodeName(n.parentNode,"optgroup"))){if(t=st(n).val(),o)return t;a.push(t)}return a},set:function(e,t){var n=st.makeArray(t);return st(e).find("option").each(function(){this.selected=st.inArray(st(this).val(),n)>=0}),n.length||(e.selectedIndex=-1),n}}},attr:function(e,n,r){var i,o,a,s=e.nodeType;if(e&&3!==s&&8!==s&&2!==s)return e.getAttribute===t?st.prop(e,n,r):(a=1!==s||!st.isXMLDoc(e),a&&(n=n.toLowerCase(),o=st.attrHooks[n]||(Dt.test(n)?kt:Ct)),r===t?o&&a&&"get"in o&&null!==(i=o.get(e,n))?i:(e.getAttribute!==t&&(i=e.getAttribute(n)),null==i?t:i):null!==r?o&&a&&"set"in o&&(i=o.set(e,r,n))!==t?i:(e.setAttribute(n,r+""),r):(st.removeAttr(e,n),t))},removeAttr:function(e,t){var n,r,i=0,o=t&&t.match(lt);if(o&&1===e.nodeType)for(;n=o[i++];)r=st.propFix[n]||n,Dt.test(n)?!Ht&&Lt.test(n)?e[st.camelCase("default-"+n)]=e[r]=!1:e[r]=!1:st.attr(e,n,""),e.removeAttribute(Ht?n:r)},attrHooks:{type:{set:function(e,t){if(!st.support.radioValue&&"radio"===t&&st.nodeName(e,"input")){var n=e.value;return e.setAttribute("type",t),n&&(e.value=n),t}}}},propFix:{tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"},prop:function(e,n,r){var i,o,a,s=e.nodeType;if(e&&3!==s&&8!==s&&2!==s)return a=1!==s||!st.isXMLDoc(e),a&&(n=st.propFix[n]||n,o=st.propHooks[n]),r!==t?o&&"set"in o&&(i=o.set(e,r,n))!==t?i:e[n]=r:o&&"get"in o&&null!==(i=o.get(e,n))?i:e[n]},propHooks:{tabIndex:{get:function(e){var n=e.getAttributeNode("tabindex");return n&&n.specified?parseInt(n.value,10):At.test(e.nodeName)||jt.test(e.nodeName)&&e.href?0:t}}}}),kt={get:function(e,n){var r=st.prop(e,n),i="boolean"==typeof r&&e.getAttribute(n),o="boolean"==typeof r?Mt&&Ht?null!=i:Lt.test(n)?e[st.camelCase("default-"+n)]:!!i:e.getAttributeNode(n);return o&&o.value!==!1?n.toLowerCase():t},set:function(e,t,n){return t===!1?st.removeAttr(e,n):Mt&&Ht||!Lt.test(n)?e.setAttribute(!Ht&&st.propFix[n]||n,n):e[st.camelCase("default-"+n)]=e[n]=!0,n}},Mt&&Ht||(st.attrHooks.value={get:function(e,n){var r=e.getAttributeNode(n);return st.nodeName(e,"input")?e.defaultValue:r&&r.specified?r.value:t
},set:function(e,n,r){return st.nodeName(e,"input")?(e.defaultValue=n,t):Ct&&Ct.set(e,n,r)}}),Ht||(Ct=st.valHooks.button={get:function(e,n){var r=e.getAttributeNode(n);return r&&("id"===n||"name"===n||"coords"===n?""!==r.value:r.specified)?r.value:t},set:function(e,n,r){var i=e.getAttributeNode(r);return i||e.setAttributeNode(i=e.ownerDocument.createAttribute(r)),i.value=n+="","value"===r||n===e.getAttribute(r)?n:t}},st.attrHooks.contenteditable={get:Ct.get,set:function(e,t,n){Ct.set(e,""===t?!1:t,n)}},st.each(["width","height"],function(e,n){st.attrHooks[n]=st.extend(st.attrHooks[n],{set:function(e,r){return""===r?(e.setAttribute(n,"auto"),r):t}})})),st.support.hrefNormalized||(st.each(["href","src","width","height"],function(e,n){st.attrHooks[n]=st.extend(st.attrHooks[n],{get:function(e){var r=e.getAttribute(n,2);return null==r?t:r}})}),st.each(["href","src"],function(e,t){st.propHooks[t]={get:function(e){return e.getAttribute(t,4)}}})),st.support.style||(st.attrHooks.style={get:function(e){return e.style.cssText||t},set:function(e,t){return e.style.cssText=t+""}}),st.support.optSelected||(st.propHooks.selected=st.extend(st.propHooks.selected,{get:function(e){var t=e.parentNode;return t&&(t.selectedIndex,t.parentNode&&t.parentNode.selectedIndex),null}})),st.support.enctype||(st.propFix.enctype="encoding"),st.support.checkOn||st.each(["radio","checkbox"],function(){st.valHooks[this]={get:function(e){return null===e.getAttribute("value")?"on":e.value}}}),st.each(["radio","checkbox"],function(){st.valHooks[this]=st.extend(st.valHooks[this],{set:function(e,n){return st.isArray(n)?e.checked=st.inArray(st(e).val(),n)>=0:t}})});var qt=/^(?:input|select|textarea)$/i,_t=/^key/,Ft=/^(?:mouse|contextmenu)|click/,Ot=/^(?:focusinfocus|focusoutblur)$/,Bt=/^([^.]*)(?:\.(.+)|)$/;st.event={global:{},add:function(e,n,r,i,o){var a,s,u,l,c,f,p,d,h,g,m,y=3!==e.nodeType&&8!==e.nodeType&&st._data(e);if(y){for(r.handler&&(a=r,r=a.handler,o=a.selector),r.guid||(r.guid=st.guid++),(l=y.events)||(l=y.events={}),(s=y.handle)||(s=y.handle=function(e){return st===t||e&&st.event.triggered===e.type?t:st.event.dispatch.apply(s.elem,arguments)},s.elem=e),n=(n||"").match(lt)||[""],c=n.length;c--;)u=Bt.exec(n[c])||[],h=m=u[1],g=(u[2]||"").split(".").sort(),p=st.event.special[h]||{},h=(o?p.delegateType:p.bindType)||h,p=st.event.special[h]||{},f=st.extend({type:h,origType:m,data:i,handler:r,guid:r.guid,selector:o,needsContext:o&&st.expr.match.needsContext.test(o),namespace:g.join(".")},a),(d=l[h])||(d=l[h]=[],d.delegateCount=0,p.setup&&p.setup.call(e,i,g,s)!==!1||(e.addEventListener?e.addEventListener(h,s,!1):e.attachEvent&&e.attachEvent("on"+h,s))),p.add&&(p.add.call(e,f),f.handler.guid||(f.handler.guid=r.guid)),o?d.splice(d.delegateCount++,0,f):d.push(f),st.event.global[h]=!0;e=null}},remove:function(e,t,n,r,i){var o,a,s,u,l,c,f,p,d,h,g,m=st.hasData(e)&&st._data(e);if(m&&(u=m.events)){for(t=(t||"").match(lt)||[""],l=t.length;l--;)if(s=Bt.exec(t[l])||[],d=g=s[1],h=(s[2]||"").split(".").sort(),d){for(f=st.event.special[d]||{},d=(r?f.delegateType:f.bindType)||d,p=u[d]||[],s=s[2]&&RegExp("(^|\\.)"+h.join("\\.(?:.*\\.|)")+"(\\.|$)"),a=o=p.length;o--;)c=p[o],!i&&g!==c.origType||n&&n.guid!==c.guid||s&&!s.test(c.namespace)||r&&r!==c.selector&&("**"!==r||!c.selector)||(p.splice(o,1),c.selector&&p.delegateCount--,f.remove&&f.remove.call(e,c));a&&!p.length&&(f.teardown&&f.teardown.call(e,h,m.handle)!==!1||st.removeEvent(e,d,m.handle),delete u[d])}else for(d in u)st.event.remove(e,d+t[l],n,r,!0);st.isEmptyObject(u)&&(delete m.handle,st._removeData(e,"events"))}},trigger:function(n,r,i,o){var a,s,u,l,c,f,p,d=[i||V],h=n.type||n,g=n.namespace?n.namespace.split("."):[];if(s=u=i=i||V,3!==i.nodeType&&8!==i.nodeType&&!Ot.test(h+st.event.triggered)&&(h.indexOf(".")>=0&&(g=h.split("."),h=g.shift(),g.sort()),c=0>h.indexOf(":")&&"on"+h,n=n[st.expando]?n:new st.Event(h,"object"==typeof n&&n),n.isTrigger=!0,n.namespace=g.join("."),n.namespace_re=n.namespace?RegExp("(^|\\.)"+g.join("\\.(?:.*\\.|)")+"(\\.|$)"):null,n.result=t,n.target||(n.target=i),r=null==r?[n]:st.makeArray(r,[n]),p=st.event.special[h]||{},o||!p.trigger||p.trigger.apply(i,r)!==!1)){if(!o&&!p.noBubble&&!st.isWindow(i)){for(l=p.delegateType||h,Ot.test(l+h)||(s=s.parentNode);s;s=s.parentNode)d.push(s),u=s;u===(i.ownerDocument||V)&&d.push(u.defaultView||u.parentWindow||e)}for(a=0;(s=d[a++])&&!n.isPropagationStopped();)n.type=a>1?l:p.bindType||h,f=(st._data(s,"events")||{})[n.type]&&st._data(s,"handle"),f&&f.apply(s,r),f=c&&s[c],f&&st.acceptData(s)&&f.apply&&f.apply(s,r)===!1&&n.preventDefault();if(n.type=h,!(o||n.isDefaultPrevented()||p._default&&p._default.apply(i.ownerDocument,r)!==!1||"click"===h&&st.nodeName(i,"a")||!st.acceptData(i)||!c||!i[h]||st.isWindow(i))){u=i[c],u&&(i[c]=null),st.event.triggered=h;try{i[h]()}catch(m){}st.event.triggered=t,u&&(i[c]=u)}return n.result}},dispatch:function(e){e=st.event.fix(e);var n,r,i,o,a,s=[],u=nt.call(arguments),l=(st._data(this,"events")||{})[e.type]||[],c=st.event.special[e.type]||{};if(u[0]=e,e.delegateTarget=this,!c.preDispatch||c.preDispatch.call(this,e)!==!1){for(s=st.event.handlers.call(this,e,l),n=0;(o=s[n++])&&!e.isPropagationStopped();)for(e.currentTarget=o.elem,r=0;(a=o.handlers[r++])&&!e.isImmediatePropagationStopped();)(!e.namespace_re||e.namespace_re.test(a.namespace))&&(e.handleObj=a,e.data=a.data,i=((st.event.special[a.origType]||{}).handle||a.handler).apply(o.elem,u),i!==t&&(e.result=i)===!1&&(e.preventDefault(),e.stopPropagation()));return c.postDispatch&&c.postDispatch.call(this,e),e.result}},handlers:function(e,n){var r,i,o,a,s=[],u=n.delegateCount,l=e.target;if(u&&l.nodeType&&(!e.button||"click"!==e.type))for(;l!=this;l=l.parentNode||this)if(l.disabled!==!0||"click"!==e.type){for(i=[],r=0;u>r;r++)a=n[r],o=a.selector+" ",i[o]===t&&(i[o]=a.needsContext?st(o,this).index(l)>=0:st.find(o,this,null,[l]).length),i[o]&&i.push(a);i.length&&s.push({elem:l,handlers:i})}return n.length>u&&s.push({elem:this,handlers:n.slice(u)}),s},fix:function(e){if(e[st.expando])return e;var t,n,r=e,i=st.event.fixHooks[e.type]||{},o=i.props?this.props.concat(i.props):this.props;for(e=new st.Event(r),t=o.length;t--;)n=o[t],e[n]=r[n];return e.target||(e.target=r.srcElement||V),3===e.target.nodeType&&(e.target=e.target.parentNode),e.metaKey=!!e.metaKey,i.filter?i.filter(e,r):e},props:"altKey bubbles cancelable ctrlKey currentTarget eventPhase metaKey relatedTarget shiftKey target timeStamp view which".split(" "),fixHooks:{},keyHooks:{props:"char charCode key keyCode".split(" "),filter:function(e,t){return null==e.which&&(e.which=null!=t.charCode?t.charCode:t.keyCode),e}},mouseHooks:{props:"button buttons clientX clientY fromElement offsetX offsetY pageX pageY screenX screenY toElement".split(" "),filter:function(e,n){var r,i,o,a=n.button,s=n.fromElement;return null==e.pageX&&null!=n.clientX&&(r=e.target.ownerDocument||V,i=r.documentElement,o=r.body,e.pageX=n.clientX+(i&&i.scrollLeft||o&&o.scrollLeft||0)-(i&&i.clientLeft||o&&o.clientLeft||0),e.pageY=n.clientY+(i&&i.scrollTop||o&&o.scrollTop||0)-(i&&i.clientTop||o&&o.clientTop||0)),!e.relatedTarget&&s&&(e.relatedTarget=s===e.target?n.toElement:s),e.which||a===t||(e.which=1&a?1:2&a?3:4&a?2:0),e}},special:{load:{noBubble:!0},click:{trigger:function(){return st.nodeName(this,"input")&&"checkbox"===this.type&&this.click?(this.click(),!1):t}},focus:{trigger:function(){if(this!==V.activeElement&&this.focus)try{return this.focus(),!1}catch(e){}},delegateType:"focusin"},blur:{trigger:function(){return this===V.activeElement&&this.blur?(this.blur(),!1):t},delegateType:"focusout"},beforeunload:{postDispatch:function(e){e.result!==t&&(e.originalEvent.returnValue=e.result)}}},simulate:function(e,t,n,r){var i=st.extend(new st.Event,n,{type:e,isSimulated:!0,originalEvent:{}});r?st.event.trigger(i,null,t):st.event.dispatch.call(t,i),i.isDefaultPrevented()&&n.preventDefault()}},st.removeEvent=V.removeEventListener?function(e,t,n){e.removeEventListener&&e.removeEventListener(t,n,!1)}:function(e,n,r){var i="on"+n;e.detachEvent&&(e[i]===t&&(e[i]=null),e.detachEvent(i,r))},st.Event=function(e,n){return this instanceof st.Event?(e&&e.type?(this.originalEvent=e,this.type=e.type,this.isDefaultPrevented=e.defaultPrevented||e.returnValue===!1||e.getPreventDefault&&e.getPreventDefault()?u:l):this.type=e,n&&st.extend(this,n),this.timeStamp=e&&e.timeStamp||st.now(),this[st.expando]=!0,t):new st.Event(e,n)},st.Event.prototype={isDefaultPrevented:l,isPropagationStopped:l,isImmediatePropagationStopped:l,preventDefault:function(){var e=this.originalEvent;this.isDefaultPrevented=u,e&&(e.preventDefault?e.preventDefault():e.returnValue=!1)},stopPropagation:function(){var e=this.originalEvent;this.isPropagationStopped=u,e&&(e.stopPropagation&&e.stopPropagation(),e.cancelBubble=!0)},stopImmediatePropagation:function(){this.isImmediatePropagationStopped=u,this.stopPropagation()}},st.each({mouseenter:"mouseover",mouseleave:"mouseout"},function(e,t){st.event.special[e]={delegateType:t,bindType:t,handle:function(e){var n,r=this,i=e.relatedTarget,o=e.handleObj;return(!i||i!==r&&!st.contains(r,i))&&(e.type=o.origType,n=o.handler.apply(this,arguments),e.type=t),n}}}),st.support.submitBubbles||(st.event.special.submit={setup:function(){return st.nodeName(this,"form")?!1:(st.event.add(this,"click._submit keypress._submit",function(e){var n=e.target,r=st.nodeName(n,"input")||st.nodeName(n,"button")?n.form:t;r&&!st._data(r,"submitBubbles")&&(st.event.add(r,"submit._submit",function(e){e._submit_bubble=!0}),st._data(r,"submitBubbles",!0))}),t)},postDispatch:function(e){e._submit_bubble&&(delete e._submit_bubble,this.parentNode&&!e.isTrigger&&st.event.simulate("submit",this.parentNode,e,!0))},teardown:function(){return st.nodeName(this,"form")?!1:(st.event.remove(this,"._submit"),t)}}),st.support.changeBubbles||(st.event.special.change={setup:function(){return qt.test(this.nodeName)?(("checkbox"===this.type||"radio"===this.type)&&(st.event.add(this,"propertychange._change",function(e){"checked"===e.originalEvent.propertyName&&(this._just_changed=!0)}),st.event.add(this,"click._change",function(e){this._just_changed&&!e.isTrigger&&(this._just_changed=!1),st.event.simulate("change",this,e,!0)})),!1):(st.event.add(this,"beforeactivate._change",function(e){var t=e.target;qt.test(t.nodeName)&&!st._data(t,"changeBubbles")&&(st.event.add(t,"change._change",function(e){!this.parentNode||e.isSimulated||e.isTrigger||st.event.simulate("change",this.parentNode,e,!0)}),st._data(t,"changeBubbles",!0))}),t)},handle:function(e){var n=e.target;return this!==n||e.isSimulated||e.isTrigger||"radio"!==n.type&&"checkbox"!==n.type?e.handleObj.handler.apply(this,arguments):t},teardown:function(){return st.event.remove(this,"._change"),!qt.test(this.nodeName)}}),st.support.focusinBubbles||st.each({focus:"focusin",blur:"focusout"},function(e,t){var n=0,r=function(e){st.event.simulate(t,e.target,st.event.fix(e),!0)};st.event.special[t]={setup:function(){0===n++&&V.addEventListener(e,r,!0)},teardown:function(){0===--n&&V.removeEventListener(e,r,!0)}}}),st.fn.extend({on:function(e,n,r,i,o){var a,s;if("object"==typeof e){"string"!=typeof n&&(r=r||n,n=t);for(s in e)this.on(s,n,r,e[s],o);return this}if(null==r&&null==i?(i=n,r=n=t):null==i&&("string"==typeof n?(i=r,r=t):(i=r,r=n,n=t)),i===!1)i=l;else if(!i)return this;return 1===o&&(a=i,i=function(e){return st().off(e),a.apply(this,arguments)},i.guid=a.guid||(a.guid=st.guid++)),this.each(function(){st.event.add(this,e,i,r,n)})},one:function(e,t,n,r){return this.on(e,t,n,r,1)},off:function(e,n,r){var i,o;if(e&&e.preventDefault&&e.handleObj)return i=e.handleObj,st(e.delegateTarget).off(i.namespace?i.origType+"."+i.namespace:i.origType,i.selector,i.handler),this;if("object"==typeof e){for(o in e)this.off(o,n,e[o]);return this}return(n===!1||"function"==typeof n)&&(r=n,n=t),r===!1&&(r=l),this.each(function(){st.event.remove(this,e,r,n)})},bind:function(e,t,n){return this.on(e,null,t,n)},unbind:function(e,t){return this.off(e,null,t)},delegate:function(e,t,n,r){return this.on(t,e,n,r)},undelegate:function(e,t,n){return 1===arguments.length?this.off(e,"**"):this.off(t,e||"**",n)},trigger:function(e,t){return this.each(function(){st.event.trigger(e,t,this)})},triggerHandler:function(e,n){var r=this[0];return r?st.event.trigger(e,n,r,!0):t},hover:function(e,t){return this.mouseenter(e).mouseleave(t||e)}}),st.each("blur focus focusin focusout load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select submit keydown keypress keyup error contextmenu".split(" "),function(e,t){st.fn[t]=function(e,n){return arguments.length>0?this.on(t,null,e,n):this.trigger(t)},_t.test(t)&&(st.event.fixHooks[t]=st.event.keyHooks),Ft.test(t)&&(st.event.fixHooks[t]=st.event.mouseHooks)}),function(e,t){function n(e){return ht.test(e+"")}function r(){var e,t=[];return e=function(n,r){return t.push(n+=" ")>C.cacheLength&&delete e[t.shift()],e[n]=r}}function i(e){return e[P]=!0,e}function o(e){var t=L.createElement("div");try{return e(t)}catch(n){return!1}finally{t=null}}function a(e,t,n,r){var i,o,a,s,u,l,c,d,h,g;if((t?t.ownerDocument||t:R)!==L&&D(t),t=t||L,n=n||[],!e||"string"!=typeof e)return n;if(1!==(s=t.nodeType)&&9!==s)return[];if(!M&&!r){if(i=gt.exec(e))if(a=i[1]){if(9===s){if(o=t.getElementById(a),!o||!o.parentNode)return n;if(o.id===a)return n.push(o),n}else if(t.ownerDocument&&(o=t.ownerDocument.getElementById(a))&&O(t,o)&&o.id===a)return n.push(o),n}else{if(i[2])return Q.apply(n,K.call(t.getElementsByTagName(e),0)),n;if((a=i[3])&&W.getByClassName&&t.getElementsByClassName)return Q.apply(n,K.call(t.getElementsByClassName(a),0)),n}if(W.qsa&&!q.test(e)){if(c=!0,d=P,h=t,g=9===s&&e,1===s&&"object"!==t.nodeName.toLowerCase()){for(l=f(e),(c=t.getAttribute("id"))?d=c.replace(vt,"\\$&"):t.setAttribute("id",d),d="[id='"+d+"'] ",u=l.length;u--;)l[u]=d+p(l[u]);h=dt.test(e)&&t.parentNode||t,g=l.join(",")}if(g)try{return Q.apply(n,K.call(h.querySelectorAll(g),0)),n}catch(m){}finally{c||t.removeAttribute("id")}}}return x(e.replace(at,"$1"),t,n,r)}function s(e,t){for(var n=e&&t&&e.nextSibling;n;n=n.nextSibling)if(n===t)return-1;return e?1:-1}function u(e){return function(t){var n=t.nodeName.toLowerCase();return"input"===n&&t.type===e}}function l(e){return function(t){var n=t.nodeName.toLowerCase();return("input"===n||"button"===n)&&t.type===e}}function c(e){return i(function(t){return t=+t,i(function(n,r){for(var i,o=e([],n.length,t),a=o.length;a--;)n[i=o[a]]&&(n[i]=!(r[i]=n[i]))})})}function f(e,t){var n,r,i,o,s,u,l,c=X[e+" "];if(c)return t?0:c.slice(0);for(s=e,u=[],l=C.preFilter;s;){(!n||(r=ut.exec(s)))&&(r&&(s=s.slice(r[0].length)||s),u.push(i=[])),n=!1,(r=lt.exec(s))&&(n=r.shift(),i.push({value:n,type:r[0].replace(at," ")}),s=s.slice(n.length));for(o in C.filter)!(r=pt[o].exec(s))||l[o]&&!(r=l[o](r))||(n=r.shift(),i.push({value:n,type:o,matches:r}),s=s.slice(n.length));if(!n)break}return t?s.length:s?a.error(e):X(e,u).slice(0)}function p(e){for(var t=0,n=e.length,r="";n>t;t++)r+=e[t].value;return r}function d(e,t,n){var r=t.dir,i=n&&"parentNode"===t.dir,o=I++;return t.first?function(t,n,o){for(;t=t[r];)if(1===t.nodeType||i)return e(t,n,o)}:function(t,n,a){var s,u,l,c=$+" "+o;if(a){for(;t=t[r];)if((1===t.nodeType||i)&&e(t,n,a))return!0}else for(;t=t[r];)if(1===t.nodeType||i)if(l=t[P]||(t[P]={}),(u=l[r])&&u[0]===c){if((s=u[1])===!0||s===N)return s===!0}else if(u=l[r]=[c],u[1]=e(t,n,a)||N,u[1]===!0)return!0}}function h(e){return e.length>1?function(t,n,r){for(var i=e.length;i--;)if(!e[i](t,n,r))return!1;return!0}:e[0]}function g(e,t,n,r,i){for(var o,a=[],s=0,u=e.length,l=null!=t;u>s;s++)(o=e[s])&&(!n||n(o,r,i))&&(a.push(o),l&&t.push(s));return a}function m(e,t,n,r,o,a){return r&&!r[P]&&(r=m(r)),o&&!o[P]&&(o=m(o,a)),i(function(i,a,s,u){var l,c,f,p=[],d=[],h=a.length,m=i||b(t||"*",s.nodeType?[s]:s,[]),y=!e||!i&&t?m:g(m,p,e,s,u),v=n?o||(i?e:h||r)?[]:a:y;if(n&&n(y,v,s,u),r)for(l=g(v,d),r(l,[],s,u),c=l.length;c--;)(f=l[c])&&(v[d[c]]=!(y[d[c]]=f));if(i){if(o||e){if(o){for(l=[],c=v.length;c--;)(f=v[c])&&l.push(y[c]=f);o(null,v=[],l,u)}for(c=v.length;c--;)(f=v[c])&&(l=o?Z.call(i,f):p[c])>-1&&(i[l]=!(a[l]=f))}}else v=g(v===a?v.splice(h,v.length):v),o?o(null,a,v,u):Q.apply(a,v)})}function y(e){for(var t,n,r,i=e.length,o=C.relative[e[0].type],a=o||C.relative[" "],s=o?1:0,u=d(function(e){return e===t},a,!0),l=d(function(e){return Z.call(t,e)>-1},a,!0),c=[function(e,n,r){return!o&&(r||n!==j)||((t=n).nodeType?u(e,n,r):l(e,n,r))}];i>s;s++)if(n=C.relative[e[s].type])c=[d(h(c),n)];else{if(n=C.filter[e[s].type].apply(null,e[s].matches),n[P]){for(r=++s;i>r&&!C.relative[e[r].type];r++);return m(s>1&&h(c),s>1&&p(e.slice(0,s-1)).replace(at,"$1"),n,r>s&&y(e.slice(s,r)),i>r&&y(e=e.slice(r)),i>r&&p(e))}c.push(n)}return h(c)}function v(e,t){var n=0,r=t.length>0,o=e.length>0,s=function(i,s,u,l,c){var f,p,d,h=[],m=0,y="0",v=i&&[],b=null!=c,x=j,T=i||o&&C.find.TAG("*",c&&s.parentNode||s),w=$+=null==x?1:Math.E;for(b&&(j=s!==L&&s,N=n);null!=(f=T[y]);y++){if(o&&f){for(p=0;d=e[p];p++)if(d(f,s,u)){l.push(f);break}b&&($=w,N=++n)}r&&((f=!d&&f)&&m--,i&&v.push(f))}if(m+=y,r&&y!==m){for(p=0;d=t[p];p++)d(v,h,s,u);if(i){if(m>0)for(;y--;)v[y]||h[y]||(h[y]=G.call(l));h=g(h)}Q.apply(l,h),b&&!i&&h.length>0&&m+t.length>1&&a.uniqueSort(l)}return b&&($=w,j=x),v};return r?i(s):s}function b(e,t,n){for(var r=0,i=t.length;i>r;r++)a(e,t[r],n);return n}function x(e,t,n,r){var i,o,a,s,u,l=f(e);if(!r&&1===l.length){if(o=l[0]=l[0].slice(0),o.length>2&&"ID"===(a=o[0]).type&&9===t.nodeType&&!M&&C.relative[o[1].type]){if(t=C.find.ID(a.matches[0].replace(xt,Tt),t)[0],!t)return n;e=e.slice(o.shift().value.length)}for(i=pt.needsContext.test(e)?-1:o.length-1;i>=0&&(a=o[i],!C.relative[s=a.type]);i--)if((u=C.find[s])&&(r=u(a.matches[0].replace(xt,Tt),dt.test(o[0].type)&&t.parentNode||t))){if(o.splice(i,1),e=r.length&&p(o),!e)return Q.apply(n,K.call(r,0)),n;break}}return S(e,l)(r,t,M,n,dt.test(e)),n}function T(){}var w,N,C,k,E,S,A,j,D,L,H,M,q,_,F,O,B,P="sizzle"+-new Date,R=e.document,W={},$=0,I=0,z=r(),X=r(),U=r(),V=typeof t,Y=1<<31,J=[],G=J.pop,Q=J.push,K=J.slice,Z=J.indexOf||function(e){for(var t=0,n=this.length;n>t;t++)if(this[t]===e)return t;return-1},et="[\\x20\\t\\r\\n\\f]",tt="(?:\\\\.|[\\w-]|[^\\x00-\\xa0])+",nt=tt.replace("w","w#"),rt="([*^$|!~]?=)",it="\\["+et+"*("+tt+")"+et+"*(?:"+rt+et+"*(?:(['\"])((?:\\\\.|[^\\\\])*?)\\3|("+nt+")|)|)"+et+"*\\]",ot=":("+tt+")(?:\\(((['\"])((?:\\\\.|[^\\\\])*?)\\3|((?:\\\\.|[^\\\\()[\\]]|"+it.replace(3,8)+")*)|.*)\\)|)",at=RegExp("^"+et+"+|((?:^|[^\\\\])(?:\\\\.)*)"+et+"+$","g"),ut=RegExp("^"+et+"*,"+et+"*"),lt=RegExp("^"+et+"*([\\x20\\t\\r\\n\\f>+~])"+et+"*"),ct=RegExp(ot),ft=RegExp("^"+nt+"$"),pt={ID:RegExp("^#("+tt+")"),CLASS:RegExp("^\\.("+tt+")"),NAME:RegExp("^\\[name=['\"]?("+tt+")['\"]?\\]"),TAG:RegExp("^("+tt.replace("w","w*")+")"),ATTR:RegExp("^"+it),PSEUDO:RegExp("^"+ot),CHILD:RegExp("^:(only|first|last|nth|nth-last)-(child|of-type)(?:\\("+et+"*(even|odd|(([+-]|)(\\d*)n|)"+et+"*(?:([+-]|)"+et+"*(\\d+)|))"+et+"*\\)|)","i"),needsContext:RegExp("^"+et+"*[>+~]|:(even|odd|eq|gt|lt|nth|first|last)(?:\\("+et+"*((?:-\\d)?\\d*)"+et+"*\\)|)(?=[^-]|$)","i")},dt=/[\x20\t\r\n\f]*[+~]/,ht=/\{\s*\[native code\]\s*\}/,gt=/^(?:#([\w-]+)|(\w+)|\.([\w-]+))$/,mt=/^(?:input|select|textarea|button)$/i,yt=/^h\d$/i,vt=/'|\\/g,bt=/\=[\x20\t\r\n\f]*([^'"\]]*)[\x20\t\r\n\f]*\]/g,xt=/\\([\da-fA-F]{1,6}[\x20\t\r\n\f]?|.)/g,Tt=function(e,t){var n="0x"+t-65536;return n!==n?t:0>n?String.fromCharCode(n+65536):String.fromCharCode(55296|n>>10,56320|1023&n)};try{K.call(H.childNodes,0)[0].nodeType}catch(wt){K=function(e){for(var t,n=[];t=this[e];e++)n.push(t);return n}}E=a.isXML=function(e){var t=e&&(e.ownerDocument||e).documentElement;return t?"HTML"!==t.nodeName:!1},D=a.setDocument=function(e){var r=e?e.ownerDocument||e:R;return r!==L&&9===r.nodeType&&r.documentElement?(L=r,H=r.documentElement,M=E(r),W.tagNameNoComments=o(function(e){return e.appendChild(r.createComment("")),!e.getElementsByTagName("*").length}),W.attributes=o(function(e){e.innerHTML="<select></select>";var t=typeof e.lastChild.getAttribute("multiple");return"boolean"!==t&&"string"!==t}),W.getByClassName=o(function(e){return e.innerHTML="<div class='hidden e'></div><div class='hidden'></div>",e.getElementsByClassName&&e.getElementsByClassName("e").length?(e.lastChild.className="e",2===e.getElementsByClassName("e").length):!1}),W.getByName=o(function(e){e.id=P+0,e.innerHTML="<a name='"+P+"'></a><div name='"+P+"'></div>",H.insertBefore(e,H.firstChild);var t=r.getElementsByName&&r.getElementsByName(P).length===2+r.getElementsByName(P+0).length;return W.getIdNotName=!r.getElementById(P),H.removeChild(e),t}),C.attrHandle=o(function(e){return e.innerHTML="<a href='#'></a>",e.firstChild&&typeof e.firstChild.getAttribute!==V&&"#"===e.firstChild.getAttribute("href")})?{}:{href:function(e){return e.getAttribute("href",2)},type:function(e){return e.getAttribute("type")}},W.getIdNotName?(C.find.ID=function(e,t){if(typeof t.getElementById!==V&&!M){var n=t.getElementById(e);return n&&n.parentNode?[n]:[]}},C.filter.ID=function(e){var t=e.replace(xt,Tt);return function(e){return e.getAttribute("id")===t}}):(C.find.ID=function(e,n){if(typeof n.getElementById!==V&&!M){var r=n.getElementById(e);return r?r.id===e||typeof r.getAttributeNode!==V&&r.getAttributeNode("id").value===e?[r]:t:[]}},C.filter.ID=function(e){var t=e.replace(xt,Tt);return function(e){var n=typeof e.getAttributeNode!==V&&e.getAttributeNode("id");return n&&n.value===t}}),C.find.TAG=W.tagNameNoComments?function(e,n){return typeof n.getElementsByTagName!==V?n.getElementsByTagName(e):t}:function(e,t){var n,r=[],i=0,o=t.getElementsByTagName(e);if("*"===e){for(;n=o[i];i++)1===n.nodeType&&r.push(n);return r}return o},C.find.NAME=W.getByName&&function(e,n){return typeof n.getElementsByName!==V?n.getElementsByName(name):t},C.find.CLASS=W.getByClassName&&function(e,n){return typeof n.getElementsByClassName===V||M?t:n.getElementsByClassName(e)},_=[],q=[":focus"],(W.qsa=n(r.querySelectorAll))&&(o(function(e){e.innerHTML="<select><option selected=''></option></select>",e.querySelectorAll("[selected]").length||q.push("\\["+et+"*(?:checked|disabled|ismap|multiple|readonly|selected|value)"),e.querySelectorAll(":checked").length||q.push(":checked")}),o(function(e){e.innerHTML="<input type='hidden' i=''/>",e.querySelectorAll("[i^='']").length&&q.push("[*^$]="+et+"*(?:\"\"|'')"),e.querySelectorAll(":enabled").length||q.push(":enabled",":disabled"),e.querySelectorAll("*,:x"),q.push(",.*:")})),(W.matchesSelector=n(F=H.matchesSelector||H.mozMatchesSelector||H.webkitMatchesSelector||H.oMatchesSelector||H.msMatchesSelector))&&o(function(e){W.disconnectedMatch=F.call(e,"div"),F.call(e,"[s!='']:x"),_.push("!=",ot)}),q=RegExp(q.join("|")),_=RegExp(_.join("|")),O=n(H.contains)||H.compareDocumentPosition?function(e,t){var n=9===e.nodeType?e.documentElement:e,r=t&&t.parentNode;return e===r||!(!r||1!==r.nodeType||!(n.contains?n.contains(r):e.compareDocumentPosition&&16&e.compareDocumentPosition(r)))}:function(e,t){if(t)for(;t=t.parentNode;)if(t===e)return!0;return!1},B=H.compareDocumentPosition?function(e,t){var n;return e===t?(A=!0,0):(n=t.compareDocumentPosition&&e.compareDocumentPosition&&e.compareDocumentPosition(t))?1&n||e.parentNode&&11===e.parentNode.nodeType?e===r||O(R,e)?-1:t===r||O(R,t)?1:0:4&n?-1:1:e.compareDocumentPosition?-1:1}:function(e,t){var n,i=0,o=e.parentNode,a=t.parentNode,u=[e],l=[t];if(e===t)return A=!0,0;if(e.sourceIndex&&t.sourceIndex)return(~t.sourceIndex||Y)-(O(R,e)&&~e.sourceIndex||Y);if(!o||!a)return e===r?-1:t===r?1:o?-1:a?1:0;if(o===a)return s(e,t);for(n=e;n=n.parentNode;)u.unshift(n);for(n=t;n=n.parentNode;)l.unshift(n);for(;u[i]===l[i];)i++;return i?s(u[i],l[i]):u[i]===R?-1:l[i]===R?1:0},A=!1,[0,0].sort(B),W.detectDuplicates=A,L):L},a.matches=function(e,t){return a(e,null,null,t)},a.matchesSelector=function(e,t){if((e.ownerDocument||e)!==L&&D(e),t=t.replace(bt,"='$1']"),!(!W.matchesSelector||M||_&&_.test(t)||q.test(t)))try{var n=F.call(e,t);if(n||W.disconnectedMatch||e.document&&11!==e.document.nodeType)return n}catch(r){}return a(t,L,null,[e]).length>0},a.contains=function(e,t){return(e.ownerDocument||e)!==L&&D(e),O(e,t)},a.attr=function(e,t){var n;return(e.ownerDocument||e)!==L&&D(e),M||(t=t.toLowerCase()),(n=C.attrHandle[t])?n(e):M||W.attributes?e.getAttribute(t):((n=e.getAttributeNode(t))||e.getAttribute(t))&&e[t]===!0?t:n&&n.specified?n.value:null},a.error=function(e){throw Error("Syntax error, unrecognized expression: "+e)},a.uniqueSort=function(e){var t,n=[],r=1,i=0;if(A=!W.detectDuplicates,e.sort(B),A){for(;t=e[r];r++)t===e[r-1]&&(i=n.push(r));for(;i--;)e.splice(n[i],1)}return e},k=a.getText=function(e){var t,n="",r=0,i=e.nodeType;if(i){if(1===i||9===i||11===i){if("string"==typeof e.textContent)return e.textContent;for(e=e.firstChild;e;e=e.nextSibling)n+=k(e)}else if(3===i||4===i)return e.nodeValue}else for(;t=e[r];r++)n+=k(t);return n},C=a.selectors={cacheLength:50,createPseudo:i,match:pt,find:{},relative:{">":{dir:"parentNode",first:!0}," ":{dir:"parentNode"},"+":{dir:"previousSibling",first:!0},"~":{dir:"previousSibling"}},preFilter:{ATTR:function(e){return e[1]=e[1].replace(xt,Tt),e[3]=(e[4]||e[5]||"").replace(xt,Tt),"~="===e[2]&&(e[3]=" "+e[3]+" "),e.slice(0,4)},CHILD:function(e){return e[1]=e[1].toLowerCase(),"nth"===e[1].slice(0,3)?(e[3]||a.error(e[0]),e[4]=+(e[4]?e[5]+(e[6]||1):2*("even"===e[3]||"odd"===e[3])),e[5]=+(e[7]+e[8]||"odd"===e[3])):e[3]&&a.error(e[0]),e},PSEUDO:function(e){var t,n=!e[5]&&e[2];return pt.CHILD.test(e[0])?null:(e[4]?e[2]=e[4]:n&&ct.test(n)&&(t=f(n,!0))&&(t=n.indexOf(")",n.length-t)-n.length)&&(e[0]=e[0].slice(0,t),e[2]=n.slice(0,t)),e.slice(0,3))}},filter:{TAG:function(e){return"*"===e?function(){return!0}:(e=e.replace(xt,Tt).toLowerCase(),function(t){return t.nodeName&&t.nodeName.toLowerCase()===e})},CLASS:function(e){var t=z[e+" "];return t||(t=RegExp("(^|"+et+")"+e+"("+et+"|$)"))&&z(e,function(e){return t.test(e.className||typeof e.getAttribute!==V&&e.getAttribute("class")||"")})},ATTR:function(e,t,n){return function(r){var i=a.attr(r,e);return null==i?"!="===t:t?(i+="","="===t?i===n:"!="===t?i!==n:"^="===t?n&&0===i.indexOf(n):"*="===t?n&&i.indexOf(n)>-1:"$="===t?n&&i.substr(i.length-n.length)===n:"~="===t?(" "+i+" ").indexOf(n)>-1:"|="===t?i===n||i.substr(0,n.length+1)===n+"-":!1):!0}},CHILD:function(e,t,n,r,i){var o="nth"!==e.slice(0,3),a="last"!==e.slice(-4),s="of-type"===t;return 1===r&&0===i?function(e){return!!e.parentNode}:function(t,n,u){var l,c,f,p,d,h,g=o!==a?"nextSibling":"previousSibling",m=t.parentNode,y=s&&t.nodeName.toLowerCase(),v=!u&&!s;if(m){if(o){for(;g;){for(f=t;f=f[g];)if(s?f.nodeName.toLowerCase()===y:1===f.nodeType)return!1;h=g="only"===e&&!h&&"nextSibling"}return!0}if(h=[a?m.firstChild:m.lastChild],a&&v){for(c=m[P]||(m[P]={}),l=c[e]||[],d=l[0]===$&&l[1],p=l[0]===$&&l[2],f=d&&m.childNodes[d];f=++d&&f&&f[g]||(p=d=0)||h.pop();)if(1===f.nodeType&&++p&&f===t){c[e]=[$,d,p];break}}else if(v&&(l=(t[P]||(t[P]={}))[e])&&l[0]===$)p=l[1];else for(;(f=++d&&f&&f[g]||(p=d=0)||h.pop())&&((s?f.nodeName.toLowerCase()!==y:1!==f.nodeType)||!++p||(v&&((f[P]||(f[P]={}))[e]=[$,p]),f!==t)););return p-=i,p===r||0===p%r&&p/r>=0}}},PSEUDO:function(e,t){var n,r=C.pseudos[e]||C.setFilters[e.toLowerCase()]||a.error("unsupported pseudo: "+e);return r[P]?r(t):r.length>1?(n=[e,e,"",t],C.setFilters.hasOwnProperty(e.toLowerCase())?i(function(e,n){for(var i,o=r(e,t),a=o.length;a--;)i=Z.call(e,o[a]),e[i]=!(n[i]=o[a])}):function(e){return r(e,0,n)}):r}},pseudos:{not:i(function(e){var t=[],n=[],r=S(e.replace(at,"$1"));return r[P]?i(function(e,t,n,i){for(var o,a=r(e,null,i,[]),s=e.length;s--;)(o=a[s])&&(e[s]=!(t[s]=o))}):function(e,i,o){return t[0]=e,r(t,null,o,n),!n.pop()}}),has:i(function(e){return function(t){return a(e,t).length>0}}),contains:i(function(e){return function(t){return(t.textContent||t.innerText||k(t)).indexOf(e)>-1}}),lang:i(function(e){return ft.test(e||"")||a.error("unsupported lang: "+e),e=e.replace(xt,Tt).toLowerCase(),function(t){var n;do if(n=M?t.getAttribute("xml:lang")||t.getAttribute("lang"):t.lang)return n=n.toLowerCase(),n===e||0===n.indexOf(e+"-");while((t=t.parentNode)&&1===t.nodeType);return!1}}),target:function(t){var n=e.location&&e.location.hash;return n&&n.slice(1)===t.id},root:function(e){return e===H},focus:function(e){return e===L.activeElement&&(!L.hasFocus||L.hasFocus())&&!!(e.type||e.href||~e.tabIndex)},enabled:function(e){return e.disabled===!1},disabled:function(e){return e.disabled===!0},checked:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&!!e.checked||"option"===t&&!!e.selected},selected:function(e){return e.parentNode&&e.parentNode.selectedIndex,e.selected===!0},empty:function(e){for(e=e.firstChild;e;e=e.nextSibling)if(e.nodeName>"@"||3===e.nodeType||4===e.nodeType)return!1;return!0},parent:function(e){return!C.pseudos.empty(e)},header:function(e){return yt.test(e.nodeName)},input:function(e){return mt.test(e.nodeName)},button:function(e){var t=e.nodeName.toLowerCase();return"input"===t&&"button"===e.type||"button"===t},text:function(e){var t;return"input"===e.nodeName.toLowerCase()&&"text"===e.type&&(null==(t=e.getAttribute("type"))||t.toLowerCase()===e.type)},first:c(function(){return[0]}),last:c(function(e,t){return[t-1]}),eq:c(function(e,t,n){return[0>n?n+t:n]}),even:c(function(e,t){for(var n=0;t>n;n+=2)e.push(n);return e}),odd:c(function(e,t){for(var n=1;t>n;n+=2)e.push(n);return e}),lt:c(function(e,t,n){for(var r=0>n?n+t:n;--r>=0;)e.push(r);return e}),gt:c(function(e,t,n){for(var r=0>n?n+t:n;t>++r;)e.push(r);return e})}};for(w in{radio:!0,checkbox:!0,file:!0,password:!0,image:!0})C.pseudos[w]=u(w);for(w in{submit:!0,reset:!0})C.pseudos[w]=l(w);S=a.compile=function(e,t){var n,r=[],i=[],o=U[e+" "];if(!o){for(t||(t=f(e)),n=t.length;n--;)o=y(t[n]),o[P]?r.push(o):i.push(o);o=U(e,v(i,r))}return o},C.pseudos.nth=C.pseudos.eq,C.filters=T.prototype=C.pseudos,C.setFilters=new T,D(),a.attr=st.attr,st.find=a,st.expr=a.selectors,st.expr[":"]=st.expr.pseudos,st.unique=a.uniqueSort,st.text=a.getText,st.isXMLDoc=a.isXML,st.contains=a.contains}(e);var Pt=/Until$/,Rt=/^(?:parents|prev(?:Until|All))/,Wt=/^.[^:#\[\.,]*$/,$t=st.expr.match.needsContext,It={children:!0,contents:!0,next:!0,prev:!0};st.fn.extend({find:function(e){var t,n,r;if("string"!=typeof e)return r=this,this.pushStack(st(e).filter(function(){for(t=0;r.length>t;t++)if(st.contains(r[t],this))return!0}));for(n=[],t=0;this.length>t;t++)st.find(e,this[t],n);return n=this.pushStack(st.unique(n)),n.selector=(this.selector?this.selector+" ":"")+e,n},has:function(e){var t,n=st(e,this),r=n.length;return this.filter(function(){for(t=0;r>t;t++)if(st.contains(this,n[t]))return!0})},not:function(e){return this.pushStack(f(this,e,!1))},filter:function(e){return this.pushStack(f(this,e,!0))},is:function(e){return!!e&&("string"==typeof e?$t.test(e)?st(e,this.context).index(this[0])>=0:st.filter(e,this).length>0:this.filter(e).length>0)},closest:function(e,t){for(var n,r=0,i=this.length,o=[],a=$t.test(e)||"string"!=typeof e?st(e,t||this.context):0;i>r;r++)for(n=this[r];n&&n.ownerDocument&&n!==t&&11!==n.nodeType;){if(a?a.index(n)>-1:st.find.matchesSelector(n,e)){o.push(n);break}n=n.parentNode}return this.pushStack(o.length>1?st.unique(o):o)},index:function(e){return e?"string"==typeof e?st.inArray(this[0],st(e)):st.inArray(e.jquery?e[0]:e,this):this[0]&&this[0].parentNode?this.first().prevAll().length:-1},add:function(e,t){var n="string"==typeof e?st(e,t):st.makeArray(e&&e.nodeType?[e]:e),r=st.merge(this.get(),n);return this.pushStack(st.unique(r))},addBack:function(e){return this.add(null==e?this.prevObject:this.prevObject.filter(e))}}),st.fn.andSelf=st.fn.addBack,st.each({parent:function(e){var t=e.parentNode;return t&&11!==t.nodeType?t:null},parents:function(e){return st.dir(e,"parentNode")},parentsUntil:function(e,t,n){return st.dir(e,"parentNode",n)},next:function(e){return c(e,"nextSibling")},prev:function(e){return c(e,"previousSibling")
},nextAll:function(e){return st.dir(e,"nextSibling")},prevAll:function(e){return st.dir(e,"previousSibling")},nextUntil:function(e,t,n){return st.dir(e,"nextSibling",n)},prevUntil:function(e,t,n){return st.dir(e,"previousSibling",n)},siblings:function(e){return st.sibling((e.parentNode||{}).firstChild,e)},children:function(e){return st.sibling(e.firstChild)},contents:function(e){return st.nodeName(e,"iframe")?e.contentDocument||e.contentWindow.document:st.merge([],e.childNodes)}},function(e,t){st.fn[e]=function(n,r){var i=st.map(this,t,n);return Pt.test(e)||(r=n),r&&"string"==typeof r&&(i=st.filter(r,i)),i=this.length>1&&!It[e]?st.unique(i):i,this.length>1&&Rt.test(e)&&(i=i.reverse()),this.pushStack(i)}}),st.extend({filter:function(e,t,n){return n&&(e=":not("+e+")"),1===t.length?st.find.matchesSelector(t[0],e)?[t[0]]:[]:st.find.matches(e,t)},dir:function(e,n,r){for(var i=[],o=e[n];o&&9!==o.nodeType&&(r===t||1!==o.nodeType||!st(o).is(r));)1===o.nodeType&&i.push(o),o=o[n];return i},sibling:function(e,t){for(var n=[];e;e=e.nextSibling)1===e.nodeType&&e!==t&&n.push(e);return n}});var zt="abbr|article|aside|audio|bdi|canvas|data|datalist|details|figcaption|figure|footer|header|hgroup|mark|meter|nav|output|progress|section|summary|time|video",Xt=/ jQuery\d+="(?:null|\d+)"/g,Ut=RegExp("<(?:"+zt+")[\\s/>]","i"),Vt=/^\s+/,Yt=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,Jt=/<([\w:]+)/,Gt=/<tbody/i,Qt=/<|&#?\w+;/,Kt=/<(?:script|style|link)/i,Zt=/^(?:checkbox|radio)$/i,en=/checked\s*(?:[^=]|=\s*.checked.)/i,tn=/^$|\/(?:java|ecma)script/i,nn=/^true\/(.*)/,rn=/^\s*<!(?:\[CDATA\[|--)|(?:\]\]|--)>\s*$/g,on={option:[1,"<select multiple='multiple'>","</select>"],legend:[1,"<fieldset>","</fieldset>"],area:[1,"<map>","</map>"],param:[1,"<object>","</object>"],thead:[1,"<table>","</table>"],tr:[2,"<table><tbody>","</tbody></table>"],col:[2,"<table><tbody></tbody><colgroup>","</colgroup></table>"],td:[3,"<table><tbody><tr>","</tr></tbody></table>"],_default:st.support.htmlSerialize?[0,"",""]:[1,"X<div>","</div>"]},an=p(V),sn=an.appendChild(V.createElement("div"));on.optgroup=on.option,on.tbody=on.tfoot=on.colgroup=on.caption=on.thead,on.th=on.td,st.fn.extend({text:function(e){return st.access(this,function(e){return e===t?st.text(this):this.empty().append((this[0]&&this[0].ownerDocument||V).createTextNode(e))},null,e,arguments.length)},wrapAll:function(e){if(st.isFunction(e))return this.each(function(t){st(this).wrapAll(e.call(this,t))});if(this[0]){var t=st(e,this[0].ownerDocument).eq(0).clone(!0);this[0].parentNode&&t.insertBefore(this[0]),t.map(function(){for(var e=this;e.firstChild&&1===e.firstChild.nodeType;)e=e.firstChild;return e}).append(this)}return this},wrapInner:function(e){return st.isFunction(e)?this.each(function(t){st(this).wrapInner(e.call(this,t))}):this.each(function(){var t=st(this),n=t.contents();n.length?n.wrapAll(e):t.append(e)})},wrap:function(e){var t=st.isFunction(e);return this.each(function(n){st(this).wrapAll(t?e.call(this,n):e)})},unwrap:function(){return this.parent().each(function(){st.nodeName(this,"body")||st(this).replaceWith(this.childNodes)}).end()},append:function(){return this.domManip(arguments,!0,function(e){(1===this.nodeType||11===this.nodeType||9===this.nodeType)&&this.appendChild(e)})},prepend:function(){return this.domManip(arguments,!0,function(e){(1===this.nodeType||11===this.nodeType||9===this.nodeType)&&this.insertBefore(e,this.firstChild)})},before:function(){return this.domManip(arguments,!1,function(e){this.parentNode&&this.parentNode.insertBefore(e,this)})},after:function(){return this.domManip(arguments,!1,function(e){this.parentNode&&this.parentNode.insertBefore(e,this.nextSibling)})},remove:function(e,t){for(var n,r=0;null!=(n=this[r]);r++)(!e||st.filter(e,[n]).length>0)&&(t||1!==n.nodeType||st.cleanData(b(n)),n.parentNode&&(t&&st.contains(n.ownerDocument,n)&&m(b(n,"script")),n.parentNode.removeChild(n)));return this},empty:function(){for(var e,t=0;null!=(e=this[t]);t++){for(1===e.nodeType&&st.cleanData(b(e,!1));e.firstChild;)e.removeChild(e.firstChild);e.options&&st.nodeName(e,"select")&&(e.options.length=0)}return this},clone:function(e,t){return e=null==e?!1:e,t=null==t?e:t,this.map(function(){return st.clone(this,e,t)})},html:function(e){return st.access(this,function(e){var n=this[0]||{},r=0,i=this.length;if(e===t)return 1===n.nodeType?n.innerHTML.replace(Xt,""):t;if(!("string"!=typeof e||Kt.test(e)||!st.support.htmlSerialize&&Ut.test(e)||!st.support.leadingWhitespace&&Vt.test(e)||on[(Jt.exec(e)||["",""])[1].toLowerCase()])){e=e.replace(Yt,"<$1></$2>");try{for(;i>r;r++)n=this[r]||{},1===n.nodeType&&(st.cleanData(b(n,!1)),n.innerHTML=e);n=0}catch(o){}}n&&this.empty().append(e)},null,e,arguments.length)},replaceWith:function(e){var t=st.isFunction(e);return t||"string"==typeof e||(e=st(e).not(this).detach()),this.domManip([e],!0,function(e){var t=this.nextSibling,n=this.parentNode;(n&&1===this.nodeType||11===this.nodeType)&&(st(this).remove(),t?t.parentNode.insertBefore(e,t):n.appendChild(e))})},detach:function(e){return this.remove(e,!0)},domManip:function(e,n,r){e=et.apply([],e);var i,o,a,s,u,l,c=0,f=this.length,p=this,m=f-1,y=e[0],v=st.isFunction(y);if(v||!(1>=f||"string"!=typeof y||st.support.checkClone)&&en.test(y))return this.each(function(i){var o=p.eq(i);v&&(e[0]=y.call(this,i,n?o.html():t)),o.domManip(e,n,r)});if(f&&(i=st.buildFragment(e,this[0].ownerDocument,!1,this),o=i.firstChild,1===i.childNodes.length&&(i=o),o)){for(n=n&&st.nodeName(o,"tr"),a=st.map(b(i,"script"),h),s=a.length;f>c;c++)u=i,c!==m&&(u=st.clone(u,!0,!0),s&&st.merge(a,b(u,"script"))),r.call(n&&st.nodeName(this[c],"table")?d(this[c],"tbody"):this[c],u,c);if(s)for(l=a[a.length-1].ownerDocument,st.map(a,g),c=0;s>c;c++)u=a[c],tn.test(u.type||"")&&!st._data(u,"globalEval")&&st.contains(l,u)&&(u.src?st.ajax({url:u.src,type:"GET",dataType:"script",async:!1,global:!1,"throws":!0}):st.globalEval((u.text||u.textContent||u.innerHTML||"").replace(rn,"")));i=o=null}return this}}),st.each({appendTo:"append",prependTo:"prepend",insertBefore:"before",insertAfter:"after",replaceAll:"replaceWith"},function(e,t){st.fn[e]=function(e){for(var n,r=0,i=[],o=st(e),a=o.length-1;a>=r;r++)n=r===a?this:this.clone(!0),st(o[r])[t](n),tt.apply(i,n.get());return this.pushStack(i)}}),st.extend({clone:function(e,t,n){var r,i,o,a,s,u=st.contains(e.ownerDocument,e);if(st.support.html5Clone||st.isXMLDoc(e)||!Ut.test("<"+e.nodeName+">")?s=e.cloneNode(!0):(sn.innerHTML=e.outerHTML,sn.removeChild(s=sn.firstChild)),!(st.support.noCloneEvent&&st.support.noCloneChecked||1!==e.nodeType&&11!==e.nodeType||st.isXMLDoc(e)))for(r=b(s),i=b(e),a=0;null!=(o=i[a]);++a)r[a]&&v(o,r[a]);if(t)if(n)for(i=i||b(e),r=r||b(s),a=0;null!=(o=i[a]);a++)y(o,r[a]);else y(e,s);return r=b(s,"script"),r.length>0&&m(r,!u&&b(e,"script")),r=i=o=null,s},buildFragment:function(e,t,n,r){for(var i,o,a,s,u,l,c,f=e.length,d=p(t),h=[],g=0;f>g;g++)if(o=e[g],o||0===o)if("object"===st.type(o))st.merge(h,o.nodeType?[o]:o);else if(Qt.test(o)){for(s=s||d.appendChild(t.createElement("div")),a=(Jt.exec(o)||["",""])[1].toLowerCase(),u=on[a]||on._default,s.innerHTML=u[1]+o.replace(Yt,"<$1></$2>")+u[2],c=u[0];c--;)s=s.lastChild;if(!st.support.leadingWhitespace&&Vt.test(o)&&h.push(t.createTextNode(Vt.exec(o)[0])),!st.support.tbody)for(o="table"!==a||Gt.test(o)?"<table>"!==u[1]||Gt.test(o)?0:s:s.firstChild,c=o&&o.childNodes.length;c--;)st.nodeName(l=o.childNodes[c],"tbody")&&!l.childNodes.length&&o.removeChild(l);for(st.merge(h,s.childNodes),s.textContent="";s.firstChild;)s.removeChild(s.firstChild);s=d.lastChild}else h.push(t.createTextNode(o));for(s&&d.removeChild(s),st.support.appendChecked||st.grep(b(h,"input"),x),g=0;o=h[g++];)if((!r||-1===st.inArray(o,r))&&(i=st.contains(o.ownerDocument,o),s=b(d.appendChild(o),"script"),i&&m(s),n))for(c=0;o=s[c++];)tn.test(o.type||"")&&n.push(o);return s=null,d},cleanData:function(e,n){for(var r,i,o,a,s=0,u=st.expando,l=st.cache,c=st.support.deleteExpando,f=st.event.special;null!=(o=e[s]);s++)if((n||st.acceptData(o))&&(i=o[u],r=i&&l[i])){if(r.events)for(a in r.events)f[a]?st.event.remove(o,a):st.removeEvent(o,a,r.handle);l[i]&&(delete l[i],c?delete o[u]:o.removeAttribute!==t?o.removeAttribute(u):o[u]=null,K.push(i))}}});var un,ln,cn,fn=/alpha\([^)]*\)/i,pn=/opacity\s*=\s*([^)]*)/,dn=/^(top|right|bottom|left)$/,hn=/^(none|table(?!-c[ea]).+)/,gn=/^margin/,mn=RegExp("^("+ut+")(.*)$","i"),yn=RegExp("^("+ut+")(?!px)[a-z%]+$","i"),vn=RegExp("^([+-])=("+ut+")","i"),bn={BODY:"block"},xn={position:"absolute",visibility:"hidden",display:"block"},Tn={letterSpacing:0,fontWeight:400},wn=["Top","Right","Bottom","Left"],Nn=["Webkit","O","Moz","ms"];st.fn.extend({css:function(e,n){return st.access(this,function(e,n,r){var i,o,a={},s=0;if(st.isArray(n)){for(i=ln(e),o=n.length;o>s;s++)a[n[s]]=st.css(e,n[s],!1,i);return a}return r!==t?st.style(e,n,r):st.css(e,n)},e,n,arguments.length>1)},show:function(){return N(this,!0)},hide:function(){return N(this)},toggle:function(e){var t="boolean"==typeof e;return this.each(function(){(t?e:w(this))?st(this).show():st(this).hide()})}}),st.extend({cssHooks:{opacity:{get:function(e,t){if(t){var n=un(e,"opacity");return""===n?"1":n}}}},cssNumber:{columnCount:!0,fillOpacity:!0,fontWeight:!0,lineHeight:!0,opacity:!0,orphans:!0,widows:!0,zIndex:!0,zoom:!0},cssProps:{"float":st.support.cssFloat?"cssFloat":"styleFloat"},style:function(e,n,r,i){if(e&&3!==e.nodeType&&8!==e.nodeType&&e.style){var o,a,s,u=st.camelCase(n),l=e.style;if(n=st.cssProps[u]||(st.cssProps[u]=T(l,u)),s=st.cssHooks[n]||st.cssHooks[u],r===t)return s&&"get"in s&&(o=s.get(e,!1,i))!==t?o:l[n];if(a=typeof r,"string"===a&&(o=vn.exec(r))&&(r=(o[1]+1)*o[2]+parseFloat(st.css(e,n)),a="number"),!(null==r||"number"===a&&isNaN(r)||("number"!==a||st.cssNumber[u]||(r+="px"),st.support.clearCloneStyle||""!==r||0!==n.indexOf("background")||(l[n]="inherit"),s&&"set"in s&&(r=s.set(e,r,i))===t)))try{l[n]=r}catch(c){}}},css:function(e,n,r,i){var o,a,s,u=st.camelCase(n);return n=st.cssProps[u]||(st.cssProps[u]=T(e.style,u)),s=st.cssHooks[n]||st.cssHooks[u],s&&"get"in s&&(o=s.get(e,!0,r)),o===t&&(o=un(e,n,i)),"normal"===o&&n in Tn&&(o=Tn[n]),r?(a=parseFloat(o),r===!0||st.isNumeric(a)?a||0:o):o},swap:function(e,t,n,r){var i,o,a={};for(o in t)a[o]=e.style[o],e.style[o]=t[o];i=n.apply(e,r||[]);for(o in t)e.style[o]=a[o];return i}}),e.getComputedStyle?(ln=function(t){return e.getComputedStyle(t,null)},un=function(e,n,r){var i,o,a,s=r||ln(e),u=s?s.getPropertyValue(n)||s[n]:t,l=e.style;return s&&(""!==u||st.contains(e.ownerDocument,e)||(u=st.style(e,n)),yn.test(u)&&gn.test(n)&&(i=l.width,o=l.minWidth,a=l.maxWidth,l.minWidth=l.maxWidth=l.width=u,u=s.width,l.width=i,l.minWidth=o,l.maxWidth=a)),u}):V.documentElement.currentStyle&&(ln=function(e){return e.currentStyle},un=function(e,n,r){var i,o,a,s=r||ln(e),u=s?s[n]:t,l=e.style;return null==u&&l&&l[n]&&(u=l[n]),yn.test(u)&&!dn.test(n)&&(i=l.left,o=e.runtimeStyle,a=o&&o.left,a&&(o.left=e.currentStyle.left),l.left="fontSize"===n?"1em":u,u=l.pixelLeft+"px",l.left=i,a&&(o.left=a)),""===u?"auto":u}),st.each(["height","width"],function(e,n){st.cssHooks[n]={get:function(e,r,i){return r?0===e.offsetWidth&&hn.test(st.css(e,"display"))?st.swap(e,xn,function(){return E(e,n,i)}):E(e,n,i):t},set:function(e,t,r){var i=r&&ln(e);return C(e,t,r?k(e,n,r,st.support.boxSizing&&"border-box"===st.css(e,"boxSizing",!1,i),i):0)}}}),st.support.opacity||(st.cssHooks.opacity={get:function(e,t){return pn.test((t&&e.currentStyle?e.currentStyle.filter:e.style.filter)||"")?.01*parseFloat(RegExp.$1)+"":t?"1":""},set:function(e,t){var n=e.style,r=e.currentStyle,i=st.isNumeric(t)?"alpha(opacity="+100*t+")":"",o=r&&r.filter||n.filter||"";n.zoom=1,(t>=1||""===t)&&""===st.trim(o.replace(fn,""))&&n.removeAttribute&&(n.removeAttribute("filter"),""===t||r&&!r.filter)||(n.filter=fn.test(o)?o.replace(fn,i):o+" "+i)}}),st(function(){st.support.reliableMarginRight||(st.cssHooks.marginRight={get:function(e,n){return n?st.swap(e,{display:"inline-block"},un,[e,"marginRight"]):t}}),!st.support.pixelPosition&&st.fn.position&&st.each(["top","left"],function(e,n){st.cssHooks[n]={get:function(e,r){return r?(r=un(e,n),yn.test(r)?st(e).position()[n]+"px":r):t}}})}),st.expr&&st.expr.filters&&(st.expr.filters.hidden=function(e){return 0===e.offsetWidth&&0===e.offsetHeight||!st.support.reliableHiddenOffsets&&"none"===(e.style&&e.style.display||st.css(e,"display"))},st.expr.filters.visible=function(e){return!st.expr.filters.hidden(e)}),st.each({margin:"",padding:"",border:"Width"},function(e,t){st.cssHooks[e+t]={expand:function(n){for(var r=0,i={},o="string"==typeof n?n.split(" "):[n];4>r;r++)i[e+wn[r]+t]=o[r]||o[r-2]||o[0];return i}},gn.test(e)||(st.cssHooks[e+t].set=C)});var Cn=/%20/g,kn=/\[\]$/,En=/\r?\n/g,Sn=/^(?:submit|button|image|reset)$/i,An=/^(?:input|select|textarea|keygen)/i;st.fn.extend({serialize:function(){return st.param(this.serializeArray())},serializeArray:function(){return this.map(function(){var e=st.prop(this,"elements");return e?st.makeArray(e):this}).filter(function(){var e=this.type;return this.name&&!st(this).is(":disabled")&&An.test(this.nodeName)&&!Sn.test(e)&&(this.checked||!Zt.test(e))}).map(function(e,t){var n=st(this).val();return null==n?null:st.isArray(n)?st.map(n,function(e){return{name:t.name,value:e.replace(En,"\r\n")}}):{name:t.name,value:n.replace(En,"\r\n")}}).get()}}),st.param=function(e,n){var r,i=[],o=function(e,t){t=st.isFunction(t)?t():null==t?"":t,i[i.length]=encodeURIComponent(e)+"="+encodeURIComponent(t)};if(n===t&&(n=st.ajaxSettings&&st.ajaxSettings.traditional),st.isArray(e)||e.jquery&&!st.isPlainObject(e))st.each(e,function(){o(this.name,this.value)});else for(r in e)j(r,e[r],n,o);return i.join("&").replace(Cn,"+")};var jn,Dn,Ln=st.now(),Hn=/\?/,Mn=/#.*$/,qn=/([?&])_=[^&]*/,_n=/^(.*?):[ \t]*([^\r\n]*)\r?$/gm,Fn=/^(?:about|app|app-storage|.+-extension|file|res|widget):$/,On=/^(?:GET|HEAD)$/,Bn=/^\/\//,Pn=/^([\w.+-]+:)(?:\/\/([^\/?#:]*)(?::(\d+)|)|)/,Rn=st.fn.load,Wn={},$n={},In="*/".concat("*");try{Dn=Y.href}catch(zn){Dn=V.createElement("a"),Dn.href="",Dn=Dn.href}jn=Pn.exec(Dn.toLowerCase())||[],st.fn.load=function(e,n,r){if("string"!=typeof e&&Rn)return Rn.apply(this,arguments);var i,o,a,s=this,u=e.indexOf(" ");return u>=0&&(i=e.slice(u,e.length),e=e.slice(0,u)),st.isFunction(n)?(r=n,n=t):n&&"object"==typeof n&&(o="POST"),s.length>0&&st.ajax({url:e,type:o,dataType:"html",data:n}).done(function(e){a=arguments,s.html(i?st("<div>").append(st.parseHTML(e)).find(i):e)}).complete(r&&function(e,t){s.each(r,a||[e.responseText,t,e])}),this},st.each(["ajaxStart","ajaxStop","ajaxComplete","ajaxError","ajaxSuccess","ajaxSend"],function(e,t){st.fn[t]=function(e){return this.on(t,e)}}),st.each(["get","post"],function(e,n){st[n]=function(e,r,i,o){return st.isFunction(r)&&(o=o||i,i=r,r=t),st.ajax({url:e,type:n,dataType:o,data:r,success:i})}}),st.extend({active:0,lastModified:{},etag:{},ajaxSettings:{url:Dn,type:"GET",isLocal:Fn.test(jn[1]),global:!0,processData:!0,async:!0,contentType:"application/x-www-form-urlencoded; charset=UTF-8",accepts:{"*":In,text:"text/plain",html:"text/html",xml:"application/xml, text/xml",json:"application/json, text/javascript"},contents:{xml:/xml/,html:/html/,json:/json/},responseFields:{xml:"responseXML",text:"responseText"},converters:{"* text":e.String,"text html":!0,"text json":st.parseJSON,"text xml":st.parseXML},flatOptions:{url:!0,context:!0}},ajaxSetup:function(e,t){return t?H(H(e,st.ajaxSettings),t):H(st.ajaxSettings,e)},ajaxPrefilter:D(Wn),ajaxTransport:D($n),ajax:function(e,n){function r(e,n,r,s){var l,f,v,b,T,N=n;2!==x&&(x=2,u&&clearTimeout(u),i=t,a=s||"",w.readyState=e>0?4:0,r&&(b=M(p,w,r)),e>=200&&300>e||304===e?(p.ifModified&&(T=w.getResponseHeader("Last-Modified"),T&&(st.lastModified[o]=T),T=w.getResponseHeader("etag"),T&&(st.etag[o]=T)),304===e?(l=!0,N="notmodified"):(l=q(p,b),N=l.state,f=l.data,v=l.error,l=!v)):(v=N,(e||!N)&&(N="error",0>e&&(e=0))),w.status=e,w.statusText=(n||N)+"",l?g.resolveWith(d,[f,N,w]):g.rejectWith(d,[w,N,v]),w.statusCode(y),y=t,c&&h.trigger(l?"ajaxSuccess":"ajaxError",[w,p,l?f:v]),m.fireWith(d,[w,N]),c&&(h.trigger("ajaxComplete",[w,p]),--st.active||st.event.trigger("ajaxStop")))}"object"==typeof e&&(n=e,e=t),n=n||{};var i,o,a,s,u,l,c,f,p=st.ajaxSetup({},n),d=p.context||p,h=p.context&&(d.nodeType||d.jquery)?st(d):st.event,g=st.Deferred(),m=st.Callbacks("once memory"),y=p.statusCode||{},v={},b={},x=0,T="canceled",w={readyState:0,getResponseHeader:function(e){var t;if(2===x){if(!s)for(s={};t=_n.exec(a);)s[t[1].toLowerCase()]=t[2];t=s[e.toLowerCase()]}return null==t?null:t},getAllResponseHeaders:function(){return 2===x?a:null},setRequestHeader:function(e,t){var n=e.toLowerCase();return x||(e=b[n]=b[n]||e,v[e]=t),this},overrideMimeType:function(e){return x||(p.mimeType=e),this},statusCode:function(e){var t;if(e)if(2>x)for(t in e)y[t]=[y[t],e[t]];else w.always(e[w.status]);return this},abort:function(e){var t=e||T;return i&&i.abort(t),r(0,t),this}};if(g.promise(w).complete=m.add,w.success=w.done,w.error=w.fail,p.url=((e||p.url||Dn)+"").replace(Mn,"").replace(Bn,jn[1]+"//"),p.type=n.method||n.type||p.method||p.type,p.dataTypes=st.trim(p.dataType||"*").toLowerCase().match(lt)||[""],null==p.crossDomain&&(l=Pn.exec(p.url.toLowerCase()),p.crossDomain=!(!l||l[1]===jn[1]&&l[2]===jn[2]&&(l[3]||("http:"===l[1]?80:443))==(jn[3]||("http:"===jn[1]?80:443)))),p.data&&p.processData&&"string"!=typeof p.data&&(p.data=st.param(p.data,p.traditional)),L(Wn,p,n,w),2===x)return w;c=p.global,c&&0===st.active++&&st.event.trigger("ajaxStart"),p.type=p.type.toUpperCase(),p.hasContent=!On.test(p.type),o=p.url,p.hasContent||(p.data&&(o=p.url+=(Hn.test(o)?"&":"?")+p.data,delete p.data),p.cache===!1&&(p.url=qn.test(o)?o.replace(qn,"$1_="+Ln++):o+(Hn.test(o)?"&":"?")+"_="+Ln++)),p.ifModified&&(st.lastModified[o]&&w.setRequestHeader("If-Modified-Since",st.lastModified[o]),st.etag[o]&&w.setRequestHeader("If-None-Match",st.etag[o])),(p.data&&p.hasContent&&p.contentType!==!1||n.contentType)&&w.setRequestHeader("Content-Type",p.contentType),w.setRequestHeader("Accept",p.dataTypes[0]&&p.accepts[p.dataTypes[0]]?p.accepts[p.dataTypes[0]]+("*"!==p.dataTypes[0]?", "+In+"; q=0.01":""):p.accepts["*"]);for(f in p.headers)w.setRequestHeader(f,p.headers[f]);if(p.beforeSend&&(p.beforeSend.call(d,w,p)===!1||2===x))return w.abort();T="abort";for(f in{success:1,error:1,complete:1})w[f](p[f]);if(i=L($n,p,n,w)){w.readyState=1,c&&h.trigger("ajaxSend",[w,p]),p.async&&p.timeout>0&&(u=setTimeout(function(){w.abort("timeout")},p.timeout));try{x=1,i.send(v,r)}catch(N){if(!(2>x))throw N;r(-1,N)}}else r(-1,"No Transport");return w},getScript:function(e,n){return st.get(e,t,n,"script")},getJSON:function(e,t,n){return st.get(e,t,n,"json")}}),st.ajaxSetup({accepts:{script:"text/javascript, application/javascript, application/ecmascript, application/x-ecmascript"},contents:{script:/(?:java|ecma)script/},converters:{"text script":function(e){return st.globalEval(e),e}}}),st.ajaxPrefilter("script",function(e){e.cache===t&&(e.cache=!1),e.crossDomain&&(e.type="GET",e.global=!1)}),st.ajaxTransport("script",function(e){if(e.crossDomain){var n,r=V.head||st("head")[0]||V.documentElement;return{send:function(t,i){n=V.createElement("script"),n.async=!0,e.scriptCharset&&(n.charset=e.scriptCharset),n.src=e.url,n.onload=n.onreadystatechange=function(e,t){(t||!n.readyState||/loaded|complete/.test(n.readyState))&&(n.onload=n.onreadystatechange=null,n.parentNode&&n.parentNode.removeChild(n),n=null,t||i(200,"success"))},r.insertBefore(n,r.firstChild)},abort:function(){n&&n.onload(t,!0)}}}});var Xn=[],Un=/(=)\?(?=&|$)|\?\?/;st.ajaxSetup({jsonp:"callback",jsonpCallback:function(){var e=Xn.pop()||st.expando+"_"+Ln++;return this[e]=!0,e}}),st.ajaxPrefilter("json jsonp",function(n,r,i){var o,a,s,u=n.jsonp!==!1&&(Un.test(n.url)?"url":"string"==typeof n.data&&!(n.contentType||"").indexOf("application/x-www-form-urlencoded")&&Un.test(n.data)&&"data");return u||"jsonp"===n.dataTypes[0]?(o=n.jsonpCallback=st.isFunction(n.jsonpCallback)?n.jsonpCallback():n.jsonpCallback,u?n[u]=n[u].replace(Un,"$1"+o):n.jsonp!==!1&&(n.url+=(Hn.test(n.url)?"&":"?")+n.jsonp+"="+o),n.converters["script json"]=function(){return s||st.error(o+" was not called"),s[0]},n.dataTypes[0]="json",a=e[o],e[o]=function(){s=arguments},i.always(function(){e[o]=a,n[o]&&(n.jsonpCallback=r.jsonpCallback,Xn.push(o)),s&&st.isFunction(a)&&a(s[0]),s=a=t}),"script"):t});var Vn,Yn,Jn=0,Gn=e.ActiveXObject&&function(){var e;for(e in Vn)Vn[e](t,!0)};st.ajaxSettings.xhr=e.ActiveXObject?function(){return!this.isLocal&&_()||F()}:_,Yn=st.ajaxSettings.xhr(),st.support.cors=!!Yn&&"withCredentials"in Yn,Yn=st.support.ajax=!!Yn,Yn&&st.ajaxTransport(function(n){if(!n.crossDomain||st.support.cors){var r;return{send:function(i,o){var a,s,u=n.xhr();if(n.username?u.open(n.type,n.url,n.async,n.username,n.password):u.open(n.type,n.url,n.async),n.xhrFields)for(s in n.xhrFields)u[s]=n.xhrFields[s];n.mimeType&&u.overrideMimeType&&u.overrideMimeType(n.mimeType),n.crossDomain||i["X-Requested-With"]||(i["X-Requested-With"]="XMLHttpRequest");try{for(s in i)u.setRequestHeader(s,i[s])}catch(l){}u.send(n.hasContent&&n.data||null),r=function(e,i){var s,l,c,f,p;try{if(r&&(i||4===u.readyState))if(r=t,a&&(u.onreadystatechange=st.noop,Gn&&delete Vn[a]),i)4!==u.readyState&&u.abort();else{f={},s=u.status,p=u.responseXML,c=u.getAllResponseHeaders(),p&&p.documentElement&&(f.xml=p),"string"==typeof u.responseText&&(f.text=u.responseText);try{l=u.statusText}catch(d){l=""}s||!n.isLocal||n.crossDomain?1223===s&&(s=204):s=f.text?200:404}}catch(h){i||o(-1,h)}f&&o(s,l,f,c)},n.async?4===u.readyState?setTimeout(r):(a=++Jn,Gn&&(Vn||(Vn={},st(e).unload(Gn)),Vn[a]=r),u.onreadystatechange=r):r()},abort:function(){r&&r(t,!0)}}}});var Qn,Kn,Zn=/^(?:toggle|show|hide)$/,er=RegExp("^(?:([+-])=|)("+ut+")([a-z%]*)$","i"),tr=/queueHooks$/,nr=[W],rr={"*":[function(e,t){var n,r,i=this.createTween(e,t),o=er.exec(t),a=i.cur(),s=+a||0,u=1,l=20;if(o){if(n=+o[2],r=o[3]||(st.cssNumber[e]?"":"px"),"px"!==r&&s){s=st.css(i.elem,e,!0)||n||1;do u=u||".5",s/=u,st.style(i.elem,e,s+r);while(u!==(u=i.cur()/a)&&1!==u&&--l)}i.unit=r,i.start=s,i.end=o[1]?s+(o[1]+1)*n:n}return i}]};st.Animation=st.extend(P,{tweener:function(e,t){st.isFunction(e)?(t=e,e=["*"]):e=e.split(" ");for(var n,r=0,i=e.length;i>r;r++)n=e[r],rr[n]=rr[n]||[],rr[n].unshift(t)},prefilter:function(e,t){t?nr.unshift(e):nr.push(e)}}),st.Tween=$,$.prototype={constructor:$,init:function(e,t,n,r,i,o){this.elem=e,this.prop=n,this.easing=i||"swing",this.options=t,this.start=this.now=this.cur(),this.end=r,this.unit=o||(st.cssNumber[n]?"":"px")},cur:function(){var e=$.propHooks[this.prop];return e&&e.get?e.get(this):$.propHooks._default.get(this)},run:function(e){var t,n=$.propHooks[this.prop];return this.pos=t=this.options.duration?st.easing[this.easing](e,this.options.duration*e,0,1,this.options.duration):e,this.now=(this.end-this.start)*t+this.start,this.options.step&&this.options.step.call(this.elem,this.now,this),n&&n.set?n.set(this):$.propHooks._default.set(this),this}},$.prototype.init.prototype=$.prototype,$.propHooks={_default:{get:function(e){var t;return null==e.elem[e.prop]||e.elem.style&&null!=e.elem.style[e.prop]?(t=st.css(e.elem,e.prop,"auto"),t&&"auto"!==t?t:0):e.elem[e.prop]},set:function(e){st.fx.step[e.prop]?st.fx.step[e.prop](e):e.elem.style&&(null!=e.elem.style[st.cssProps[e.prop]]||st.cssHooks[e.prop])?st.style(e.elem,e.prop,e.now+e.unit):e.elem[e.prop]=e.now}}},$.propHooks.scrollTop=$.propHooks.scrollLeft={set:function(e){e.elem.nodeType&&e.elem.parentNode&&(e.elem[e.prop]=e.now)}},st.each(["toggle","show","hide"],function(e,t){var n=st.fn[t];st.fn[t]=function(e,r,i){return null==e||"boolean"==typeof e?n.apply(this,arguments):this.animate(I(t,!0),e,r,i)}}),st.fn.extend({fadeTo:function(e,t,n,r){return this.filter(w).css("opacity",0).show().end().animate({opacity:t},e,n,r)},animate:function(e,t,n,r){var i=st.isEmptyObject(e),o=st.speed(t,n,r),a=function(){var t=P(this,st.extend({},e),o);a.finish=function(){t.stop(!0)},(i||st._data(this,"finish"))&&t.stop(!0)};return a.finish=a,i||o.queue===!1?this.each(a):this.queue(o.queue,a)},stop:function(e,n,r){var i=function(e){var t=e.stop;delete e.stop,t(r)};return"string"!=typeof e&&(r=n,n=e,e=t),n&&e!==!1&&this.queue(e||"fx",[]),this.each(function(){var t=!0,n=null!=e&&e+"queueHooks",o=st.timers,a=st._data(this);if(n)a[n]&&a[n].stop&&i(a[n]);else for(n in a)a[n]&&a[n].stop&&tr.test(n)&&i(a[n]);for(n=o.length;n--;)o[n].elem!==this||null!=e&&o[n].queue!==e||(o[n].anim.stop(r),t=!1,o.splice(n,1));(t||!r)&&st.dequeue(this,e)})},finish:function(e){return e!==!1&&(e=e||"fx"),this.each(function(){var t,n=st._data(this),r=n[e+"queue"],i=n[e+"queueHooks"],o=st.timers,a=r?r.length:0;for(n.finish=!0,st.queue(this,e,[]),i&&i.cur&&i.cur.finish&&i.cur.finish.call(this),t=o.length;t--;)o[t].elem===this&&o[t].queue===e&&(o[t].anim.stop(!0),o.splice(t,1));for(t=0;a>t;t++)r[t]&&r[t].finish&&r[t].finish.call(this);delete n.finish})}}),st.each({slideDown:I("show"),slideUp:I("hide"),slideToggle:I("toggle"),fadeIn:{opacity:"show"},fadeOut:{opacity:"hide"},fadeToggle:{opacity:"toggle"}},function(e,t){st.fn[e]=function(e,n,r){return this.animate(t,e,n,r)}}),st.speed=function(e,t,n){var r=e&&"object"==typeof e?st.extend({},e):{complete:n||!n&&t||st.isFunction(e)&&e,duration:e,easing:n&&t||t&&!st.isFunction(t)&&t};return r.duration=st.fx.off?0:"number"==typeof r.duration?r.duration:r.duration in st.fx.speeds?st.fx.speeds[r.duration]:st.fx.speeds._default,(null==r.queue||r.queue===!0)&&(r.queue="fx"),r.old=r.complete,r.complete=function(){st.isFunction(r.old)&&r.old.call(this),r.queue&&st.dequeue(this,r.queue)},r},st.easing={linear:function(e){return e},swing:function(e){return.5-Math.cos(e*Math.PI)/2}},st.timers=[],st.fx=$.prototype.init,st.fx.tick=function(){var e,n=st.timers,r=0;for(Qn=st.now();n.length>r;r++)e=n[r],e()||n[r]!==e||n.splice(r--,1);n.length||st.fx.stop(),Qn=t},st.fx.timer=function(e){e()&&st.timers.push(e)&&st.fx.start()},st.fx.interval=13,st.fx.start=function(){Kn||(Kn=setInterval(st.fx.tick,st.fx.interval))},st.fx.stop=function(){clearInterval(Kn),Kn=null},st.fx.speeds={slow:600,fast:200,_default:400},st.fx.step={},st.expr&&st.expr.filters&&(st.expr.filters.animated=function(e){return st.grep(st.timers,function(t){return e===t.elem}).length}),st.fn.offset=function(e){if(arguments.length)return e===t?this:this.each(function(t){st.offset.setOffset(this,e,t)});var n,r,i={top:0,left:0},o=this[0],a=o&&o.ownerDocument;if(a)return n=a.documentElement,st.contains(n,o)?(o.getBoundingClientRect!==t&&(i=o.getBoundingClientRect()),r=z(a),{top:i.top+(r.pageYOffset||n.scrollTop)-(n.clientTop||0),left:i.left+(r.pageXOffset||n.scrollLeft)-(n.clientLeft||0)}):i},st.offset={setOffset:function(e,t,n){var r=st.css(e,"position");"static"===r&&(e.style.position="relative");var i,o,a=st(e),s=a.offset(),u=st.css(e,"top"),l=st.css(e,"left"),c=("absolute"===r||"fixed"===r)&&st.inArray("auto",[u,l])>-1,f={},p={};c?(p=a.position(),i=p.top,o=p.left):(i=parseFloat(u)||0,o=parseFloat(l)||0),st.isFunction(t)&&(t=t.call(e,n,s)),null!=t.top&&(f.top=t.top-s.top+i),null!=t.left&&(f.left=t.left-s.left+o),"using"in t?t.using.call(e,f):a.css(f)}},st.fn.extend({position:function(){if(this[0]){var e,t,n={top:0,left:0},r=this[0];return"fixed"===st.css(r,"position")?t=r.getBoundingClientRect():(e=this.offsetParent(),t=this.offset(),st.nodeName(e[0],"html")||(n=e.offset()),n.top+=st.css(e[0],"borderTopWidth",!0),n.left+=st.css(e[0],"borderLeftWidth",!0)),{top:t.top-n.top-st.css(r,"marginTop",!0),left:t.left-n.left-st.css(r,"marginLeft",!0)}}},offsetParent:function(){return this.map(function(){for(var e=this.offsetParent||V.documentElement;e&&!st.nodeName(e,"html")&&"static"===st.css(e,"position");)e=e.offsetParent;return e||V.documentElement})}}),st.each({scrollLeft:"pageXOffset",scrollTop:"pageYOffset"},function(e,n){var r=/Y/.test(n);st.fn[e]=function(i){return st.access(this,function(e,i,o){var a=z(e);return o===t?a?n in a?a[n]:a.document.documentElement[i]:e[i]:(a?a.scrollTo(r?st(a).scrollLeft():o,r?o:st(a).scrollTop()):e[i]=o,t)},e,i,arguments.length,null)}}),st.each({Height:"height",Width:"width"},function(e,n){st.each({padding:"inner"+e,content:n,"":"outer"+e},function(r,i){st.fn[i]=function(i,o){var a=arguments.length&&(r||"boolean"!=typeof i),s=r||(i===!0||o===!0?"margin":"border");return st.access(this,function(n,r,i){var o;return st.isWindow(n)?n.document.documentElement["client"+e]:9===n.nodeType?(o=n.documentElement,Math.max(n.body["scroll"+e],o["scroll"+e],n.body["offset"+e],o["offset"+e],o["client"+e])):i===t?st.css(n,r,s):st.style(n,r,i,s)},n,a?i:t,a,null)}})}),e.jQuery=e.$=st,"function"==typeof define&&define.amd&&define.amd.jQuery&&define("jquery",[],function(){return st})})(window);

/*! Copyright (c) 2011 Brandon Aaron (http://brandonaaron.net)
 * Licensed under the MIT License (LICENSE.txt).  * * Thanks to: http://adomas.org/javascript-mouse-wheel/ for some pointers.  * Thanks to: Mathias Bank(http://www.mathias-bank.de) for a scope bug fix.  * Thanks to: Seamus Leahy for adding deltaX and deltaY * * Version: 3.0.6 * * Requires: 1.2.2+
 */
(function($) { var types = ['DOMMouseScroll', 'mousewheel']; if ($.event.fixHooks) { for ( var i=types.length; i; ) { $.event.fixHooks[ types[--i] ] = $.event.mouseHooks; } } $.event.special.mousewheel = { setup: function() { if ( this.addEventListener ) { for ( var i=types.length; i; ) { this.addEventListener( types[--i], handler, false ); } } else { this.onmousewheel = handler; } }, teardown: function() { if ( this.removeEventListener ) { for ( var i=types.length; i; ) { this.removeEventListener( types[--i], handler, false ); } } else { this.onmousewheel = null; } } }; $.fn.extend({ mousewheel: function(fn) { return fn ? this.bind("mousewheel", fn) : this.trigger("mousewheel"); }, unmousewheel: function(fn) { return this.unbind("mousewheel", fn); } }); function handler(event) { var orgEvent = event || window.event, args = [].slice.call( arguments, 1 ), delta = 0, returnValue = true, deltaX = 0, deltaY = 0; event = $.event.fix(orgEvent); event.type = "mousewheel"; if ( orgEvent.wheelDelta ) { delta = orgEvent.wheelDelta/120; } if ( orgEvent.detail     ) { delta = -orgEvent.detail/3; } deltaY = delta; if ( orgEvent.axis !== undefined && orgEvent.axis === orgEvent.HORIZONTAL_AXIS ) { deltaY = 0; deltaX = -1*delta; } if ( orgEvent.wheelDeltaY !== undefined ) { deltaY = orgEvent.wheelDeltaY/120; } if ( orgEvent.wheelDeltaX !== undefined ) { deltaX = -1*orgEvent.wheelDeltaX/120; } args.unshift(event, delta, deltaX, deltaY); return ($.event.dispatch || $.event.handle).apply(this, args); } })(jQuery);

/*!
 * jQuery Form Plugin
 * version: 3.35.0-2013.05.23
 * @requires jQuery v1.5 or later
 * Copyright (c) 2013 M. Alsup
 * Examples and documentation at: http://malsup.com/jquery/form/
 * Project repository: https://github.com/malsup/form
 * Dual licensed under the MIT and GPL licenses.
 * https://github.com/malsup/form#copyright-and-license
 */
/*global ActiveXObject */
;(function($) {
"use strict";

/*
    Usage Note:
    -----------
    Do not use both ajaxSubmit and ajaxForm on the same form.  These
    functions are mutually exclusive.  Use ajaxSubmit if you want
    to bind your own submit handler to the form.  For example,

    $(document).ready(function() {
        $('#myForm').on('submit', function(e) {
            e.preventDefault(); // <-- important
            $(this).ajaxSubmit({
                target: '#output'
            });
        });
    });

    Use ajaxForm when you want the plugin to manage all the event binding
    for you.  For example,

    $(document).ready(function() {
        $('#myForm').ajaxForm({
            target: '#output'
        });
    });

    You can also use ajaxForm with delegation (requires jQuery v1.7+), so the
    form does not have to exist when you invoke ajaxForm:

    $('#myForm').ajaxForm({
        delegation: true,
        target: '#output'
    });

    When using ajaxForm, the ajaxSubmit function will be invoked for you
    at the appropriate time.
*/

/**
 * Feature detection
 */
var feature = {};
feature.fileapi = $("<input type='file'/>").get(0).files !== undefined;
feature.formdata = window.FormData !== undefined;

var hasProp = !!$.fn.prop;

// attr2 uses prop when it can but checks the return type for
// an expected string.  this accounts for the case where a form
// contains inputs with names like "action" or "method"; in those
// cases "prop" returns the element
$.fn.attr2 = function() {
    if ( ! hasProp )
        return this.attr.apply(this, arguments);
    var val = this.prop.apply(this, arguments);
    if ( ( val && val.jquery ) || typeof val === 'string' )
        return val;
    return this.attr.apply(this, arguments);
};

/**
 * ajaxSubmit() provides a mechanism for immediately submitting
 * an HTML form using AJAX.
 */
$.fn.ajaxSubmit = function(options) {
    /*jshint scripturl:true */

    // fast fail if nothing selected (http://dev.jquery.com/ticket/2752)
    if (!this.length) {
        log('ajaxSubmit: skipping submit process - no element selected');
        return this;
    }

    var method, action, url, $form = this;

    if (typeof options == 'function') {
        options = { success: options };
    }

    method = options.type || this.attr2('method');
    action = options.url  || this.attr2('action');

    url = (typeof action === 'string') ? $.trim(action) : '';
    url = url || window.location.href || '';
    if (url) {
        // clean url (don't include hash vaue)
        url = (url.match(/^([^#]+)/)||[])[1];
    }

    options = $.extend(true, {
        url:  url,
        success: $.ajaxSettings.success,
        type: method || 'GET',
        iframeSrc: /^https/i.test(window.location.href || '') ? 'javascript:false' : 'about:blank'
    }, options);

    // hook for manipulating the form data before it is extracted;
    // convenient for use with rich editors like tinyMCE or FCKEditor
    var veto = {};
    this.trigger('form-pre-serialize', [this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-pre-serialize trigger');
        return this;
    }

    // provide opportunity to alter form data before it is serialized
    if (options.beforeSerialize && options.beforeSerialize(this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSerialize callback');
        return this;
    }

    var traditional = options.traditional;
    if ( traditional === undefined ) {
        traditional = $.ajaxSettings.traditional;
    }

    var elements = [];
    var qx, a = this.formToArray(options.semantic, elements);
    if (options.data) {
        options.extraData = options.data;
        qx = $.param(options.data, traditional);
    }

    // give pre-submit callback an opportunity to abort the submit
    if (options.beforeSubmit && options.beforeSubmit(a, this, options) === false) {
        log('ajaxSubmit: submit aborted via beforeSubmit callback');
        return this;
    }

    // fire vetoable 'validate' event
    this.trigger('form-submit-validate', [a, this, options, veto]);
    if (veto.veto) {
        log('ajaxSubmit: submit vetoed via form-submit-validate trigger');
        return this;
    }

    var q = $.param(a, traditional);
    if (qx) {
        q = ( q ? (q + '&' + qx) : qx );
    }
    if (options.type.toUpperCase() == 'GET') {
        options.url += (options.url.indexOf('?') >= 0 ? '&' : '?') + q;
        options.data = null;  // data is null for 'get'
    }
    else {
        options.data = q; // data is the query string for 'post'
    }

    var callbacks = [];
    if (options.resetForm) {
        callbacks.push(function() { $form.resetForm(); });
    }
    if (options.clearForm) {
        callbacks.push(function() { $form.clearForm(options.includeHidden); });
    }

    // perform a load on the target only if dataType is not provided
    if (!options.dataType && options.target) {
        var oldSuccess = options.success || function(){};
        callbacks.push(function(data) {
            var fn = options.replaceTarget ? 'replaceWith' : 'html';
            $(options.target)[fn](data).each(oldSuccess, arguments);
        });
    }
    else if (options.success) {
        callbacks.push(options.success);
    }

    options.success = function(data, status, xhr) { // jQuery 1.4+ passes xhr as 3rd arg
        var context = options.context || this ;    // jQuery 1.4+ supports scope context
        for (var i=0, max=callbacks.length; i < max; i++) {
            callbacks[i].apply(context, [data, status, xhr || $form, $form]);
        }
    };

    if (options.error) {
        var oldError = options.error;
        options.error = function(xhr, status, error) {
            var context = options.context || this;
            oldError.apply(context, [xhr, status, error, $form]);
        };
    }

     if (options.complete) {
        var oldComplete = options.complete;
        options.complete = function(xhr, status) {
            var context = options.context || this;
            oldComplete.apply(context, [xhr, status, $form]);
        };
    }

    // are there files to upload?

    // [value] (issue #113), also see comment:
    // https://github.com/malsup/form/commit/588306aedba1de01388032d5f42a60159eea9228#commitcomment-2180219
    var fileInputs = $('input[type=file]:enabled[value!=""]', this);

    var hasFileInputs = fileInputs.length > 0;
    var mp = 'multipart/form-data';
    var multipart = ($form.attr('enctype') == mp || $form.attr('encoding') == mp);

    var fileAPI = feature.fileapi && feature.formdata;
    log("fileAPI :" + fileAPI);
    var shouldUseFrame = (hasFileInputs || multipart) && !fileAPI;

    var jqxhr;

    // options.iframe allows user to force iframe mode
    // 06-NOV-09: now defaulting to iframe mode if file input is detected
    if (options.iframe !== false && (options.iframe || shouldUseFrame)) {
        // hack to fix Safari hang (thanks to Tim Molendijk for this)
        // see:  http://groups.google.com/group/jquery-dev/browse_thread/thread/36395b7ab510dd5d
        if (options.closeKeepAlive) {
            $.get(options.closeKeepAlive, function() {
                jqxhr = fileUploadIframe(a);
            });
        }
        else {
            jqxhr = fileUploadIframe(a);
        }
    }
    else if ((hasFileInputs || multipart) && fileAPI) {
        jqxhr = fileUploadXhr(a);
    }
    else {
        jqxhr = $.ajax(options);
    }

    $form.removeData('jqxhr').data('jqxhr', jqxhr);

    // clear element array
    for (var k=0; k < elements.length; k++)
        elements[k] = null;

    // fire 'notify' event
    this.trigger('form-submit-notify', [this, options]);
    return this;

    // utility fn for deep serialization
    function deepSerialize(extraData){
        var serialized = $.param(extraData, options.traditional).split('&');
        var len = serialized.length;
        var result = [];
        var i, part;
        for (i=0; i < len; i++) {
            // #252; undo param space replacement
            serialized[i] = serialized[i].replace(/\+/g,' ');
            part = serialized[i].split('=');
            // #278; use array instead of object storage, favoring array serializations
            result.push([decodeURIComponent(part[0]), decodeURIComponent(part[1])]);
        }
        return result;
    }

     // XMLHttpRequest Level 2 file uploads (big hat tip to francois2metz)
    function fileUploadXhr(a) {
        var formdata = new FormData();

        for (var i=0; i < a.length; i++) {
            formdata.append(a[i].name, a[i].value);
        }

        if (options.extraData) {
            var serializedData = deepSerialize(options.extraData);
            for (i=0; i < serializedData.length; i++)
                if (serializedData[i])
                    formdata.append(serializedData[i][0], serializedData[i][1]);
        }

        options.data = null;

        var s = $.extend(true, {}, $.ajaxSettings, options, {
            contentType: false,
            processData: false,
            cache: false,
            type: method || 'POST'
        });

        if (options.uploadProgress) {
            // workaround because jqXHR does not expose upload property
            s.xhr = function() {
                var xhr = jQuery.ajaxSettings.xhr();
                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', function(event) {
                        var percent = 0;
                        var position = event.loaded || event.position; /*event.position is deprecated*/
                        var total = event.total;
                        if (event.lengthComputable) {
                            percent = Math.ceil(position / total * 100);
                        }
                        options.uploadProgress(event, position, total, percent);
                    }, false);
                }
                return xhr;
            };
        }

        s.data = null;
            var beforeSend = s.beforeSend;
            s.beforeSend = function(xhr, o) {
                o.data = formdata;
                if(beforeSend)
                    beforeSend.call(this, xhr, o);
        };
        return $.ajax(s);
    }

    // private function for handling file uploads (hat tip to YAHOO!)
    function fileUploadIframe(a) {
        var form = $form[0], el, i, s, g, id, $io, io, xhr, sub, n, timedOut, timeoutHandle;
        var deferred = $.Deferred();

        if (a) {
            // ensure that every serialized input is still enabled
            for (i=0; i < elements.length; i++) {
                el = $(elements[i]);
                if ( hasProp )
                    el.prop('disabled', false);
                else
                    el.removeAttr('disabled');
            }
        }

        s = $.extend(true, {}, $.ajaxSettings, options);
        s.context = s.context || s;
        id = 'jqFormIO' + (new Date().getTime());
        if (s.iframeTarget) {
            $io = $(s.iframeTarget);
            n = $io.attr2('name');
            if (!n)
                 $io.attr2('name', id);
            else
                id = n;
        }
        else {
            $io = $('<iframe name="' + id + '" src="'+ s.iframeSrc +'" />');
            $io.css({ position: 'absolute', top: '-1000px', left: '-1000px' });
        }
        io = $io[0];


        xhr = { // mock object
            aborted: 0,
            responseText: null,
            responseXML: null,
            status: 0,
            statusText: 'n/a',
            getAllResponseHeaders: function() {},
            getResponseHeader: function() {},
            setRequestHeader: function() {},
            abort: function(status) {
                var e = (status === 'timeout' ? 'timeout' : 'aborted');
                log('aborting upload... ' + e);
                this.aborted = 1;

                try { // #214, #257
                    if (io.contentWindow.document.execCommand) {
                        io.contentWindow.document.execCommand('Stop');
                    }
                }
                catch(ignore) {}

                $io.attr('src', s.iframeSrc); // abort op in progress
                xhr.error = e;
                if (s.error)
                    s.error.call(s.context, xhr, e, status);
                if (g)
                    $.event.trigger("ajaxError", [xhr, s, e]);
                if (s.complete)
                    s.complete.call(s.context, xhr, e);
            }
        };

        g = s.global;
        // trigger ajax global events so that activity/block indicators work like normal
        if (g && 0 === $.active++) {
            $.event.trigger("ajaxStart");
        }
        if (g) {
            $.event.trigger("ajaxSend", [xhr, s]);
        }

        if (s.beforeSend && s.beforeSend.call(s.context, xhr, s) === false) {
            if (s.global) {
                $.active--;
            }
            deferred.reject();
            return deferred;
        }
        if (xhr.aborted) {
            deferred.reject();
            return deferred;
        }

        // add submitting element to data if we know it
        sub = form.clk;
        if (sub) {
            n = sub.name;
            if (n && !sub.disabled) {
                s.extraData = s.extraData || {};
                s.extraData[n] = sub.value;
                if (sub.type == "image") {
                    s.extraData[n+'.x'] = form.clk_x;
                    s.extraData[n+'.y'] = form.clk_y;
                }
            }
        }

        var CLIENT_TIMEOUT_ABORT = 1;
        var SERVER_ABORT = 2;

        function getDoc(frame) {
            /* it looks like contentWindow or contentDocument do not
             * carry the protocol property in ie8, when running under ssl
             * frame.document is the only valid response document, since
             * the protocol is know but not on the other two objects. strange?
             * "Same origin policy" http://en.wikipedia.org/wiki/Same_origin_policy
             */

            var doc = null;

            // IE8 cascading access check
            try {
                if (frame.contentWindow) {
                    doc = frame.contentWindow.document;
                }
            } catch(err) {
                // IE8 access denied under ssl & missing protocol
                log('cannot get iframe.contentWindow document: ' + err);
            }

            if (doc) { // successful getting content
                return doc;
            }

            try { // simply checking may throw in ie8 under ssl or mismatched protocol
                doc = frame.contentDocument ? frame.contentDocument : frame.document;
            } catch(err) {
                // last attempt
                log('cannot get iframe.contentDocument: ' + err);
                doc = frame.document;
            }
            return doc;
        }

        // Rails CSRF hack (thanks to Yvan Barthelemy)
        var csrf_token = $('meta[name=csrf-token]').attr('content');
        var csrf_param = $('meta[name=csrf-param]').attr('content');
        if (csrf_param && csrf_token) {
            s.extraData = s.extraData || {};
            s.extraData[csrf_param] = csrf_token;
        }

        // take a breath so that pending repaints get some cpu time before the upload starts
        function doSubmit() {
            // make sure form attrs are set
            var t = $form.attr2('target'), a = $form.attr2('action');

            // update form attrs in IE friendly way
            form.setAttribute('target',id);
            if (!method) {
                form.setAttribute('method', 'POST');
            }
            if (a != s.url) {
                form.setAttribute('action', s.url);
            }

            // ie borks in some cases when setting encoding
            if (! s.skipEncodingOverride && (!method || /post/i.test(method))) {
                $form.attr({
                    encoding: 'multipart/form-data',
                    enctype:  'multipart/form-data'
                });
            }

            // support timout
            if (s.timeout) {
                timeoutHandle = setTimeout(function() { timedOut = true; cb(CLIENT_TIMEOUT_ABORT); }, s.timeout);
            }

            // look for server aborts
            function checkState() {
                try {
                    var state = getDoc(io).readyState;
                    log('state = ' + state);
                    if (state && state.toLowerCase() == 'uninitialized')
                        setTimeout(checkState,50);
                }
                catch(e) {
                    log('Server abort: ' , e, ' (', e.name, ')');
                    cb(SERVER_ABORT);
                    if (timeoutHandle)
                        clearTimeout(timeoutHandle);
                    timeoutHandle = undefined;
                }
            }

            // add "extra" data to form if provided in options
            var extraInputs = [];
            try {
                if (s.extraData) {
                    for (var n in s.extraData) {
                        if (s.extraData.hasOwnProperty(n)) {
                           // if using the $.param format that allows for multiple values with the same name
                           if($.isPlainObject(s.extraData[n]) && s.extraData[n].hasOwnProperty('name') && s.extraData[n].hasOwnProperty('value')) {
                               extraInputs.push(
                               $('<input type="hidden" name="'+s.extraData[n].name+'">').val(s.extraData[n].value)
                                   .appendTo(form)[0]);
                           } else {
                               extraInputs.push(
                               $('<input type="hidden" name="'+n+'">').val(s.extraData[n])
                                   .appendTo(form)[0]);
                           }
                        }
                    }
                }

                if (!s.iframeTarget) {
                    // add iframe to doc and submit the form
                    $io.appendTo('body');
                    if (io.attachEvent)
                        io.attachEvent('onload', cb);
                    else
                        io.addEventListener('load', cb, false);
                }
                setTimeout(checkState,15);

                try {
                    form.submit();
                } catch(err) {
                    // just in case form has element with name/id of 'submit'
                    var submitFn = document.createElement('form').submit;
                    submitFn.apply(form);
                }
            }
            finally {
                // reset attrs and remove "extra" input elements
                form.setAttribute('action',a);
                if(t) {
                    form.setAttribute('target', t);
                } else {
                    $form.removeAttr('target');
                }
                $(extraInputs).remove();
            }
        }

        if (s.forceSync) {
            doSubmit();
        }
        else {
            setTimeout(doSubmit, 10); // this lets dom updates render
        }

        var data, doc, domCheckCount = 50, callbackProcessed;

        function cb(e) {
            if (xhr.aborted || callbackProcessed) {
                return;
            }

            doc = getDoc(io);
            if(!doc) {
                log('cannot access response document');
                e = SERVER_ABORT;
            }
            if (e === CLIENT_TIMEOUT_ABORT && xhr) {
                xhr.abort('timeout');
                deferred.reject(xhr, 'timeout');
                return;
            }
            else if (e == SERVER_ABORT && xhr) {
                xhr.abort('server abort');
                deferred.reject(xhr, 'error', 'server abort');
                return;
            }

            if (!doc || doc.location.href == s.iframeSrc) {
                // response not received yet
                if (!timedOut)
                    return;
            }
            if (io.detachEvent)
                io.detachEvent('onload', cb);
            else
                io.removeEventListener('load', cb, false);

            var status = 'success', errMsg;
            try {
                if (timedOut) {
                    throw 'timeout';
                }

                var isXml = s.dataType == 'xml' || doc.XMLDocument || $.isXMLDoc(doc);
                log('isXml='+isXml);
                if (!isXml && window.opera && (doc.body === null || !doc.body.innerHTML)) {
                    if (--domCheckCount) {
                        // in some browsers (Opera) the iframe DOM is not always traversable when
                        // the onload callback fires, so we loop a bit to accommodate
                        log('requeing onLoad callback, DOM not available');
                        setTimeout(cb, 250);
                        return;
                    }
                    // let this fall through because server response could be an empty document
                    //log('Could not access iframe DOM after mutiple tries.');
                    //throw 'DOMException: not available';
                }

                //log('response detected');
                var docRoot = doc.body ? doc.body : doc.documentElement;
                xhr.responseText = docRoot ? docRoot.innerHTML : null;
                xhr.responseXML = doc.XMLDocument ? doc.XMLDocument : doc;
                if (isXml)
                    s.dataType = 'xml';
                xhr.getResponseHeader = function(header){
                    var headers = {'content-type': s.dataType};
                    return headers[header];
                };
                // support for XHR 'status' & 'statusText' emulation :
                if (docRoot) {
                    xhr.status = Number( docRoot.getAttribute('status') ) || xhr.status;
                    xhr.statusText = docRoot.getAttribute('statusText') || xhr.statusText;
                }

                var dt = (s.dataType || '').toLowerCase();
                var scr = /(json|script|text)/.test(dt);
                if (scr || s.textarea) {
                    // see if user embedded response in textarea
                    var ta = doc.getElementsByTagName('textarea')[0];
                    if (ta) {
                        xhr.responseText = ta.value;
                        // support for XHR 'status' & 'statusText' emulation :
                        xhr.status = Number( ta.getAttribute('status') ) || xhr.status;
                        xhr.statusText = ta.getAttribute('statusText') || xhr.statusText;
                    }
                    else if (scr) {
                        // account for browsers injecting pre around json response
                        var pre = doc.getElementsByTagName('pre')[0];
                        var b = doc.getElementsByTagName('body')[0];
                        if (pre) {
                            xhr.responseText = pre.textContent ? pre.textContent : pre.innerText;
                        }
                        else if (b) {
                            xhr.responseText = b.textContent ? b.textContent : b.innerText;
                        }
                    }
                }
                else if (dt == 'xml' && !xhr.responseXML && xhr.responseText) {
                    xhr.responseXML = toXml(xhr.responseText);
                }

                try {
                    data = httpData(xhr, dt, s);
                }
                catch (err) {
                    status = 'parsererror';
                    xhr.error = errMsg = (err || status);
                }
            }
            catch (err) {
                log('error caught: ',err);
                status = 'error';
                xhr.error = errMsg = (err || status);
            }

            if (xhr.aborted) {
                log('upload aborted');
                status = null;
            }

            if (xhr.status) { // we've set xhr.status
                status = (xhr.status >= 200 && xhr.status < 300 || xhr.status === 304) ? 'success' : 'error';
            }

            // ordering of these callbacks/triggers is odd, but that's how $.ajax does it
            if (status === 'success') {
                if (s.success)
                    s.success.call(s.context, data, 'success', xhr);
                deferred.resolve(xhr.responseText, 'success', xhr);
                if (g)
                    $.event.trigger("ajaxSuccess", [xhr, s]);
            }
            else if (status) {
                if (errMsg === undefined)
                    errMsg = xhr.statusText;
                if (s.error)
                    s.error.call(s.context, xhr, status, errMsg);
                deferred.reject(xhr, 'error', errMsg);
                if (g)
                    $.event.trigger("ajaxError", [xhr, s, errMsg]);
            }

            if (g)
                $.event.trigger("ajaxComplete", [xhr, s]);

            if (g && ! --$.active) {
                $.event.trigger("ajaxStop");
            }

            if (s.complete)
                s.complete.call(s.context, xhr, status);

            callbackProcessed = true;
            if (s.timeout)
                clearTimeout(timeoutHandle);

            // clean up
            setTimeout(function() {
                if (!s.iframeTarget)
                    $io.remove();
                xhr.responseXML = null;
            }, 100);
        }

        var toXml = $.parseXML || function(s, doc) { // use parseXML if available (jQuery 1.5+)
            if (window.ActiveXObject) {
                doc = new ActiveXObject('Microsoft.XMLDOM');
                doc.async = 'false';
                doc.loadXML(s);
            }
            else {
                doc = (new DOMParser()).parseFromString(s, 'text/xml');
            }
            return (doc && doc.documentElement && doc.documentElement.nodeName != 'parsererror') ? doc : null;
        };
        var parseJSON = $.parseJSON || function(s) {
            /*jslint evil:true */
            return window['eval']('(' + s + ')');
        };

        var httpData = function( xhr, type, s ) { // mostly lifted from jq1.4.4

            var ct = xhr.getResponseHeader('content-type') || '',
                xml = type === 'xml' || !type && ct.indexOf('xml') >= 0,
                data = xml ? xhr.responseXML : xhr.responseText;

            if (xml && data.documentElement.nodeName === 'parsererror') {
                if ($.error)
                    $.error('parsererror');
            }
            if (s && s.dataFilter) {
                data = s.dataFilter(data, type);
            }
            if (typeof data === 'string') {
                if (type === 'json' || !type && ct.indexOf('json') >= 0) {
                    data = parseJSON(data);
                } else if (type === "script" || !type && ct.indexOf("javascript") >= 0) {
                    $.globalEval(data);
                }
            }
            return data;
        };

        return deferred;
    }
};

/**
 * ajaxForm() provides a mechanism for fully automating form submission.
 *
 * The advantages of using this method instead of ajaxSubmit() are:
 *
 * 1: This method will include coordinates for <input type="image" /> elements (if the element
 *    is used to submit the form).
 * 2. This method will include the submit element's name/value data (for the element that was
 *    used to submit the form).
 * 3. This method binds the submit() method to the form for you.
 *
 * The options argument for ajaxForm works exactly as it does for ajaxSubmit.  ajaxForm merely
 * passes the options argument along after properly binding events for submit elements and
 * the form itself.
 */
$.fn.ajaxForm = function(options) {
    options = options || {};
    options.delegation = options.delegation && $.isFunction($.fn.on);

    // in jQuery 1.3+ we can fix mistakes with the ready state
    if (!options.delegation && this.length === 0) {
        var o = { s: this.selector, c: this.context };
        if (!$.isReady && o.s) {
            log('DOM not ready, queuing ajaxForm');
            $(function() {
                $(o.s,o.c).ajaxForm(options);
            });
            return this;
        }
        // is your DOM ready?  http://docs.jquery.com/Tutorials:Introducing_$(document).ready()
        log('terminating; zero elements found by selector' + ($.isReady ? '' : ' (DOM not ready)'));
        return this;
    }

    if ( options.delegation ) {
        $(document)
            .off('submit.form-plugin', this.selector, doAjaxSubmit)
            .off('click.form-plugin', this.selector, captureSubmittingElement)
            .on('submit.form-plugin', this.selector, options, doAjaxSubmit)
            .on('click.form-plugin', this.selector, options, captureSubmittingElement);
        return this;
    }

    return this.ajaxFormUnbind()
        .bind('submit.form-plugin', options, doAjaxSubmit)
        .bind('click.form-plugin', options, captureSubmittingElement);
};

// private event handlers
function doAjaxSubmit(e) {
    /*jshint validthis:true */
    var options = e.data;
    if (!e.isDefaultPrevented()) { // if event has been canceled, don't proceed
        e.preventDefault();
        $(this).ajaxSubmit(options);
    }
}

function captureSubmittingElement(e) {
    /*jshint validthis:true */
    var target = e.target;
    var $el = $(target);
    if (!($el.is("[type=submit],[type=image]"))) {
        // is this a child element of the submit el?  (ex: a span within a button)
        var t = $el.closest('[type=submit]');
        if (t.length === 0) {
            return;
        }
        target = t[0];
    }
    var form = this;
    form.clk = target;
    if (target.type == 'image') {
        if (e.offsetX !== undefined) {
            form.clk_x = e.offsetX;
            form.clk_y = e.offsetY;
        } else if (typeof $.fn.offset == 'function') {
            var offset = $el.offset();
            form.clk_x = e.pageX - offset.left;
            form.clk_y = e.pageY - offset.top;
        } else {
            form.clk_x = e.pageX - target.offsetLeft;
            form.clk_y = e.pageY - target.offsetTop;
        }
    }
    // clear form vars
    setTimeout(function() { form.clk = form.clk_x = form.clk_y = null; }, 100);
}


// ajaxFormUnbind unbinds the event handlers that were bound by ajaxForm
$.fn.ajaxFormUnbind = function() {
    return this.unbind('submit.form-plugin click.form-plugin');
};

/**
 * formToArray() gathers form element data into an array of objects that can
 * be passed to any of the following ajax functions: $.get, $.post, or load.
 * Each object in the array has both a 'name' and 'value' property.  An example of
 * an array for a simple login form might be:
 *
 * [ { name: 'username', value: 'jresig' }, { name: 'password', value: 'secret' } ]
 *
 * It is this array that is passed to pre-submit callback functions provided to the
 * ajaxSubmit() and ajaxForm() methods.
 */
$.fn.formToArray = function(semantic, elements) {
    var a = [];
    if (this.length === 0) {
        return a;
    }

    var form = this[0];
    var els = semantic ? form.getElementsByTagName('*') : form.elements;
    if (!els) {
        return a;
    }

    var i,j,n,v,el,max,jmax;
    for(i=0, max=els.length; i < max; i++) {
        el = els[i];
        n = el.name;
        if (!n || el.disabled) {
            continue;
        }

        if (semantic && form.clk && el.type == "image") {
            // handle image inputs on the fly when semantic == true
            if(form.clk == el) {
                a.push({name: n, value: $(el).val(), type: el.type });
                a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
            }
            continue;
        }

        v = $.fieldValue(el, true);
        if (v && v.constructor == Array) {
            if (elements)
                elements.push(el);
            for(j=0, jmax=v.length; j < jmax; j++) {
                a.push({name: n, value: v[j]});
            }
        }
        else if (feature.fileapi && el.type == 'file') {
            if (elements)
                elements.push(el);
            var files = el.files;
            if (files.length) {
                for (j=0; j < files.length; j++) {
                    a.push({name: n, value: files[j], type: el.type});
                }
            }
            else {
                // #180
                a.push({ name: n, value: '', type: el.type });
            }
        }
        else if (v !== null && typeof v != 'undefined') {
            if (elements)
                elements.push(el);
            a.push({name: n, value: v, type: el.type, required: el.required});
        }
    }

    if (!semantic && form.clk) {
        // input type=='image' are not found in elements array! handle it here
        var $input = $(form.clk), input = $input[0];
        n = input.name;
        if (n && !input.disabled && input.type == 'image') {
            a.push({name: n, value: $input.val()});
            a.push({name: n+'.x', value: form.clk_x}, {name: n+'.y', value: form.clk_y});
        }
    }
    return a;
};

/**
 * Serializes form data into a 'submittable' string. This method will return a string
 * in the format: name1=value1&amp;name2=value2
 */
$.fn.formSerialize = function(semantic) {
    //hand off to jQuery.param for proper encoding
    return $.param(this.formToArray(semantic));
};

/**
 * Serializes all field elements in the jQuery object into a query string.
 * This method will return a string in the format: name1=value1&amp;name2=value2
 */
$.fn.fieldSerialize = function(successful) {
    var a = [];
    this.each(function() {
        var n = this.name;
        if (!n) {
            return;
        }
        var v = $.fieldValue(this, successful);
        if (v && v.constructor == Array) {
            for (var i=0,max=v.length; i < max; i++) {
                a.push({name: n, value: v[i]});
            }
        }
        else if (v !== null && typeof v != 'undefined') {
            a.push({name: this.name, value: v});
        }
    });
    //hand off to jQuery.param for proper encoding
    return $.param(a);
};

/**
 * Returns the value(s) of the element in the matched set.  For example, consider the following form:
 *
 *  <form><fieldset>
 *      <input name="A" type="text" />
 *      <input name="A" type="text" />
 *      <input name="B" type="checkbox" value="B1" />
 *      <input name="B" type="checkbox" value="B2"/>
 *      <input name="C" type="radio" value="C1" />
 *      <input name="C" type="radio" value="C2" />
 *  </fieldset></form>
 *
 *  var v = $('input[type=text]').fieldValue();
 *  // if no values are entered into the text inputs
 *  v == ['','']
 *  // if values entered into the text inputs are 'foo' and 'bar'
 *  v == ['foo','bar']
 *
 *  var v = $('input[type=checkbox]').fieldValue();
 *  // if neither checkbox is checked
 *  v === undefined
 *  // if both checkboxes are checked
 *  v == ['B1', 'B2']
 *
 *  var v = $('input[type=radio]').fieldValue();
 *  // if neither radio is checked
 *  v === undefined
 *  // if first radio is checked
 *  v == ['C1']
 *
 * The successful argument controls whether or not the field element must be 'successful'
 * (per http://www.w3.org/TR/html4/interact/forms.html#successful-controls).
 * The default value of the successful argument is true.  If this value is false the value(s)
 * for each element is returned.
 *
 * Note: This method *always* returns an array.  If no valid value can be determined the
 *    array will be empty, otherwise it will contain one or more values.
 */
$.fn.fieldValue = function(successful) {
    for (var val=[], i=0, max=this.length; i < max; i++) {
        var el = this[i];
        var v = $.fieldValue(el, successful);
        if (v === null || typeof v == 'undefined' || (v.constructor == Array && !v.length)) {
            continue;
        }
        if (v.constructor == Array)
            $.merge(val, v);
        else
            val.push(v);
    }
    return val;
};

/**
 * Returns the value of the field element.
 */
$.fieldValue = function(el, successful) {
    var n = el.name, t = el.type, tag = el.tagName.toLowerCase();
    if (successful === undefined) {
        successful = true;
    }

    if (successful && (!n || el.disabled || t == 'reset' || t == 'button' ||
        (t == 'checkbox' || t == 'radio') && !el.checked ||
        (t == 'submit' || t == 'image') && el.form && el.form.clk != el ||
        tag == 'select' && el.selectedIndex == -1)) {
            return null;
    }

    if (tag == 'select') {
        var index = el.selectedIndex;
        if (index < 0) {
            return null;
        }
        var a = [], ops = el.options;
        var one = (t == 'select-one');
        var max = (one ? index+1 : ops.length);
        for(var i=(one ? index : 0); i < max; i++) {
            var op = ops[i];
            if (op.selected) {
                var v = op.value;
                if (!v) { // extra pain for IE...
                    v = (op.attributes && op.attributes['value'] && !(op.attributes['value'].specified)) ? op.text : op.value;
                }
                if (one) {
                    return v;
                }
                a.push(v);
            }
        }
        return a;
    }
    return $(el).val();
};

/**
 * Clears the form data.  Takes the following actions on the form's input fields:
 *  - input text fields will have their 'value' property set to the empty string
 *  - select elements will have their 'selectedIndex' property set to -1
 *  - checkbox and radio inputs will have their 'checked' property set to false
 *  - inputs of type submit, button, reset, and hidden will *not* be effected
 *  - button elements will *not* be effected
 */
$.fn.clearForm = function(includeHidden) {
    return this.each(function() {
        $('input,select,textarea', this).clearFields(includeHidden);
    });
};

/**
 * Clears the selected form elements.
 */
$.fn.clearFields = $.fn.clearInputs = function(includeHidden) {
    var re = /^(?:color|date|datetime|email|month|number|password|range|search|tel|text|time|url|week)$/i; // 'hidden' is not in this list
    return this.each(function() {
        var t = this.type, tag = this.tagName.toLowerCase();
        if (re.test(t) || tag == 'textarea') {
            this.value = '';
        }
        else if (t == 'checkbox' || t == 'radio') {
            this.checked = false;
        }
        else if (tag == 'select') {
            this.selectedIndex = -1;
        }
		else if (t == "file") {
			if (/MSIE/.test(navigator.userAgent)) {
				$(this).replaceWith($(this).clone(true));
			} else {
				$(this).val('');
			}
		}
        else if (includeHidden) {
            // includeHidden can be the value true, or it can be a selector string
            // indicating a special test; for example:
            //  $('#myForm').clearForm('.special:hidden')
            // the above would clean hidden inputs that have the class of 'special'
            if ( (includeHidden === true && /hidden/.test(t)) ||
                 (typeof includeHidden == 'string' && $(this).is(includeHidden)) )
                this.value = '';
        }
    });
};

/**
 * Resets the form data.  Causes all form elements to be reset to their original value.
 */
$.fn.resetForm = function() {
    return this.each(function() {
        // guard against an input with the name of 'reset'
        // note that IE reports the reset function as an 'object'
        if (typeof this.reset == 'function' || (typeof this.reset == 'object' && !this.reset.nodeType)) {
            this.reset();
        }
    });
};

/**
 * Enables or disables any matching elements.
 */
$.fn.enable = function(b) {
    if (b === undefined) {
        b = true;
    }
    return this.each(function() {
        this.disabled = !b;
    });
};

/**
 * Checks/unchecks any matching checkboxes or radio buttons and
 * selects/deselects and matching option elements.
 */
$.fn.selected = function(select) {
    if (select === undefined) {
        select = true;
    }
    return this.each(function() {
        var t = this.type;
        if (t == 'checkbox' || t == 'radio') {
            this.checked = select;
        }
        else if (this.tagName.toLowerCase() == 'option') {
            var $sel = $(this).parent('select');
            if (select && $sel[0] && $sel[0].type == 'select-one') {
                // deselect all other options
                $sel.find('option').selected(false);
            }
            this.selected = select;
        }
    });
};

// expose debug var
$.fn.ajaxSubmit.debug = false;

// helper fn for console logging
function log() {
    if (!$.fn.ajaxSubmit.debug)
        return;
    var msg = '[jquery.form] ' + Array.prototype.join.call(arguments,'');
    if (window.console && window.console.log) {
        window.console.log(msg);
    }
    else if (window.opera && window.opera.postError) {
        window.opera.postError(msg);
    }
}

})(jQuery);

/**
 * Tap Event for jQuery
 */
;(function($){'use strict';var A='ontouchstart'in document,B={C:A?'touchstart':'mousedown',D:A?'touchend':'mouseup'};$.fn.tap=function(f){return this[f?'bind':'trigger']('tap',f)};$.event.special.tap={setup:function(){$(this).bind(B.C+' '+B.D,function(e){B.E=A?e.originalEvent.changedTouches[0]:e}).bind(B.C,function(e){if(e.which&&e.which!==1)return;B.target=e.target;B.time=new Date().getTime();B.X=B.E.pageX;B.Y=B.E.pageY;}).bind(B.D,function(e){if(B.target==e.target&&((new Date().getTime()-B.time)<750)&&(B.X==B.E.pageX&&B.Y==B.E.pageY)){var t=$(this);e.preventDefault=function(){t.bind('click',!1)};e.type='tap';e.pageX=B.E.pageX;e.pageY=B.E.pageY;$.event.dispatch.call(this,e)}})},remove:function(){$(this).unbind(B.C,!1).unbind(B.D,!1)}}})(jQuery);

/*
 * animate backgroundPosition plugin
 */
(function($) {if(!document.defaultView || !document.defaultView.getComputedStyle){var oldCurCSS = $.curCSS;$.curCSS = function(elem, name, force){if(name === 'background-position'){name = 'backgroundPosition';}if(name !== 'backgroundPosition' || !elem.currentStyle || elem.currentStyle[ name ]){return oldCurCSS.apply(this, arguments);}var style = elem.style;if ( !force && style && style[ name ] ){return style[ name ];}return oldCurCSS(elem, 'backgroundPositionX', force) +' '+ oldCurCSS(elem, 'backgroundPositionY', force);};}var oldAnim = $.fn.animate;$.fn.animate = function(prop){if('background-position' in prop){prop.backgroundPosition = prop['background-position'];delete prop['background-position'];}if('backgroundPosition' in prop){prop.backgroundPosition = '('+ prop.backgroundPosition;}return oldAnim.apply(this, arguments);};function toArray(strg){strg = strg.replace(/left|top/g,'0px');strg = strg.replace(/right|bottom/g,'100%');strg = strg.replace(/([0-9\.]+)(\s|\)|$)/g,"$1px$2");var res = strg.match(/(-?[0-9\.]+)(px|\%|em|pt)\s(-?[0-9\.]+)(px|\%|em|pt)/);return [parseFloat(res[1],10),res[2],parseFloat(res[3],10),res[4]];}$.fx.step. backgroundPosition = function(fx) {if (!fx.bgPosReady) {var start = $.curCSS(fx.elem,'backgroundPosition');if(!start){start = '0px 0px';}start = toArray(start);fx.start = [start[0],start[2]];var end = toArray(fx.end);fx.end = [end[0],end[2]];fx.unit = [end[1],end[3]];fx.bgPosReady = true;}var nowPosX = [];nowPosX[0] = ((fx.end[0] - fx.start[0]) * fx.pos) + fx.start[0] + fx.unit[0];nowPosX[1] = ((fx.end[1] - fx.start[1]) * fx.pos) + fx.start[1] + fx.unit[1];fx.elem.style.backgroundPosition = nowPosX[0]+' '+nowPosX[1];};})(jQuery);


/* lazy load xt v1.0.5 */
(function($,window,document,undefined){var lazyLoadXT='lazyLoadXT',dataLazied='lazied',load_error='load error',classLazyHidden='lazy-hidden',docElement=document.documentElement||document.body,forceLoad=(window.onscroll===undefined||!!window.operamini||!docElement.getBoundingClientRect),options={autoInit:true,selector:'img[data-src]',blankImage:'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',throttle:99,forceLoad:forceLoad,loadEvent:'pageshow',updateEvent:'load orientationchange resize scroll touchmove focus',forceEvent:'',oninit:{removeClass:'lazy'},onshow:{addClass:classLazyHidden},onload:{removeClass:classLazyHidden,addClass:'lazy-loaded'},onerror:{removeClass:classLazyHidden},checkDuplicates:true},elementOptions={srcAttr:'data-src',edgeX:0,edgeY:0,visibleOnly:true},$window=$(window),$isFunction=$.isFunction,$extend=$.extend,$data=$.data||function(el,name){return $(el).data(name)},$contains=$.contains||function(parent,el){while(el=el.parentNode){if(el===parent){return true}};return false},elements=[],topLazy=0,waitingMode=0;$[lazyLoadXT]=$extend(options,elementOptions,$[lazyLoadXT]);function getOrDef(obj,prop){return obj[prop]===undefined?options[prop]:obj[prop]};function scrollTop(){var scroll=window.pageYOffset;return(scroll===undefined)?docElement.scrollTop:scroll};$.fn[lazyLoadXT]=function(overrides){overrides=overrides||{};var blankImage=getOrDef(overrides,'blankImage'),checkDuplicates=getOrDef(overrides,'checkDuplicates'),scrollContainer=getOrDef(overrides,'scrollContainer'),elementOptionsOverrides={},prop;$(scrollContainer).on('scroll',queueCheckLazyElements);for(prop in elementOptions){elementOptionsOverrides[prop]=getOrDef(overrides,prop)};return this.each(function(index,el){if(el===window){$(options.selector).lazyLoadXT(overrides)}else{if(checkDuplicates&&$data(el,dataLazied)){return};var $el=$(el).data(dataLazied,1);if(blankImage&&el.tagName==='IMG'&&!el.src){el.src=blankImage};$el[lazyLoadXT]=$extend({},elementOptionsOverrides);triggerEvent('init',$el);elements.push($el)}})};function triggerEvent(event,$el){var handler=options['on'+event];if(handler){if($isFunction(handler)){handler.call($el[0])}else{if(handler.addClass){$el.addClass(handler.addClass)};if(handler.removeClass){$el.removeClass(handler.removeClass)}}};$el.trigger('lazy'+event,[$el]);queueCheckLazyElements()};function triggerLoadOrError(e){triggerEvent(e.type,$(this).off(load_error,triggerLoadOrError))};function checkLazyElements(force){if(!elements.length){return};force=force||options.forceLoad;topLazy=Infinity;var viewportTop=scrollTop(),viewportHeight=window.innerHeight||docElement.clientHeight,viewportWidth=window.innerWidth||docElement.clientWidth,i,length;for(i=0,length=elements.length;i<length;i++){var $el=elements[i],el=$el[0],objData=$el[lazyLoadXT],removeNode=false,visible=force,topEdge;if(!$contains(docElement,el)){removeNode=true}else if(force||!objData.visibleOnly||el.offsetWidth||el.offsetHeight){if(!visible){var elPos=el.getBoundingClientRect(),edgeX=objData.edgeX,edgeY=objData.edgeY;topEdge=(elPos.top+viewportTop-edgeY)-viewportHeight;visible=(topEdge<=viewportTop&&elPos.bottom>-edgeY&&elPos.left<=viewportWidth+edgeX&&elPos.right>-edgeX)};if(visible){triggerEvent('show',$el);var srcAttr=objData.srcAttr,src=$isFunction(srcAttr)?srcAttr($el):el.getAttribute(srcAttr);if(src){$el.on(load_error,triggerLoadOrError);el.src=src};removeNode=true}else{if(topEdge<topLazy){topLazy=topEdge}}};if(removeNode){elements.splice(i--,1);length--}};if(!length){triggerEvent('complete',$(docElement))}};function timeoutLazyElements(){if(waitingMode>1){waitingMode=1;checkLazyElements();setTimeout(timeoutLazyElements,options.throttle)}else{waitingMode=0}};function queueCheckLazyElements(e){if(!elements.length){return};if(e&&e.type==='scroll'&&e.currentTarget===window){if(topLazy>=scrollTop()){return}};if(!waitingMode){setTimeout(timeoutLazyElements,0)};waitingMode=2};function initLazyElements(){$window.lazyLoadXT()};function forceLoadAll(){checkLazyElements(true)};$(document).ready(function(){triggerEvent('start',$window);$window.on(options.loadEvent,initLazyElements).on(options.updateEvent,queueCheckLazyElements).on(options.forceEvent,forceLoadAll);$(document).on(options.updateEvent,queueCheckLazyElements);if(options.autoInit){initLazyElements();}})})(window.jQuery||window.Zepto||window.$,window,document);

;define('lib/template.min', [], function(require, exports, module) {});
/*!artTemplate - Template Engine*/var template=function(a,b){return template["object"==typeof b?"render":"compile"].apply(template,arguments)};(function(a,b){"use strict";a.version="2.0.1",a.openTag="<%",a.closeTag="%>",a.isEscape=!0,a.isCompress=!1,a.parser=null,a.render=function(a,b){var c=d(a);return void 0===c?e({id:a,name:"Render Error",message:"No Template"}):c(b)},a.compile=function(b,d){function l(c){try{return new j(c)+""}catch(f){return h?(f.id=b||d,f.name="Render Error",f.source=d,e(f)):a.compile(b,d,!0)(c)}}var g=arguments,h=g[2],i="anonymous";"string"!=typeof d&&(h=g[1],d=g[0],b=i);try{var j=f(d,h)}catch(k){return k.id=b||d,k.name="Syntax Error",e(k)}return l.prototype=j.prototype,l.toString=function(){return""+j},b!==i&&(c[b]=l),l},a.helper=function(b,c){a.prototype[b]=c},a.onerror=function(a){var c="[template]:\n"+a.id+"\n\n[name]:\n"+a.name;a.message&&(c+="\n\n[message]:\n"+a.message),a.line&&(c+="\n\n[line]:\n"+a.line,c+="\n\n[source]:\n"+a.source.split(/\n/)[a.line-1].replace(/^[\s\t]+/,"")),a.temp&&(c+="\n\n[temp]:\n"+a.temp),b.console&&console.error(c)};var c={},d=function(d){var e=c[d];if(void 0===e&&"document"in b){var f=document.getElementById(d);if(f){var g=f.value||f.innerHTML;return a.compile(d,g.replace(/^\s*|\s*$/g,""))}}else if(c.hasOwnProperty(d))return e},e=function(b){function c(){return c+""}return a.onerror(b),c.toString=function(){return"{Template Error}"},c},f=function(){a.prototype={$render:a.render,$escape:function(a){return"string"==typeof a?a.replace(/&(?![\w#]+;)|[<>"']/g,function(a){return{"<":"&#60;",">":"&#62;",'"':"&#34;","'":"&#39;","&":"&#38;"}[a]}):a},$string:function(a){return"string"==typeof a||"number"==typeof a?a:"function"==typeof a?a():""}};var b=Array.prototype.forEach||function(a,b){for(var c=this.length>>>0,d=0;c>d;d++)d in this&&a.call(b,this[d],d,this)},c=function(a,c){b.call(a,c)},d="break,case,catch,continue,debugger,default,delete,do,else,false,finally,for,function,if,in,instanceof,new,null,return,switch,this,throw,true,try,typeof,var,void,while,with,abstract,boolean,byte,char,class,const,double,enum,export,extends,final,float,goto,implements,import,int,interface,long,native,package,private,protected,public,short,static,super,synchronized,throws,transient,volatile,arguments,let,yield,undefined",e=/\/\*(?:.|\n)*?\*\/|\/\/[^\n]*\n|\/\/[^\n]*$|'[^']*'|"[^"]*"|[\s\t\n]*\.[\s\t\n]*[$\w\.]+/g,f=/[^\w$]+/g,g=RegExp(["\\b"+d.replace(/,/g,"\\b|\\b")+"\\b"].join("|"),"g"),h=/\b\d[^,]*/g,i=/^,+|,+$/g,j=function(a){return a=a.replace(e,"").replace(f,",").replace(g,"").replace(h,"").replace(i,""),a=a?a.split(/,+/):[]};return function(b,d){function w(b){return k+=b.split(/\n/).length-1,a.isCompress&&(b=b.replace(/[\n\r\t\s]+/g," ")),b=b.replace(/('|\\)/g,"\\$1").replace(/\r/g,"\\r").replace(/\n/g,"\\n"),b=q[1]+"'"+b+"'"+q[2],b+"\n"}function x(b){var c=k;if(g?b=g(b):d&&(b=b.replace(/\n/g,function(){return k++,"$line="+k+";"})),0===b.indexOf("=")){var e=0!==b.indexOf("==");if(b=b.replace(/^=*|[\s;]*$/g,""),e&&a.isEscape){var f=b.replace(/\s*\([^\)]+\)/,"");m.hasOwnProperty(f)||/^(include|print)$/.test(f)||(b="$escape($string("+b+"))")}else b="$string("+b+")";b=q[1]+b+q[2]}return d&&(b="$line="+c+";"+b),y(b),b+"\n"}function y(a){a=j(a),c(a,function(a){l.hasOwnProperty(a)||(z(a),l[a]=!0)})}function z(a){var b;"print"===a?b=s:"include"===a?(n.$render=m.$render,b=t):(b="$data."+a,m.hasOwnProperty(a)&&(n[a]=m[a],b=0===a.indexOf("$")?"$helpers."+a:b+"===undefined?$helpers."+a+":"+b)),o+=a+"="+b+","}var e=a.openTag,f=a.closeTag,g=a.parser,h=b,i="",k=1,l={$data:!0,$helpers:!0,$out:!0,$line:!0},m=a.prototype,n={},o="var $helpers=this,"+(d?"$line=0,":""),p="".trim,q=p?["$out='';","$out+=",";","$out"]:["$out=[];","$out.push(",");","$out.join('')"],r=p?"if(content!==undefined){$out+=content;return content}":"$out.push(content);",s="function(content){"+r+"}",t="function(id,data){if(data===undefined){data=$data}var content=$helpers.$render(id,data);"+r+"}";c(h.split(e),function(a){a=a.split(f);var c=a[0],d=a[1];1===a.length?i+=w(c):(i+=x(c),d&&(i+=w(d)))}),h=i,d&&(h="try{"+h+"}catch(e){"+"e.line=$line;"+"throw e"+"}"),h="'use strict';"+o+q[0]+h+"return new String("+q[3]+")";try{var u=Function("$data",h);return u.prototype=n,u}catch(v){throw v.temp="function anonymous($data) {"+h+"}",v}}}()})(template,this),"function"==typeof define?define(function(a,b,c){c.exports=template}):"undefined"!=typeof exports&&(module.exports=template);

template.helper('isDOMExist', function (id) {
    if (jq('#' + id)[0]) {
        return true;
    } else {
        return false;
    }
});
template.helper('getWinParams', function (name) {
    return window[name];
});
template.helper('isObjEmpty', function (obj) {
    if (jq.isEmptyObject(obj)) {
        return true;
    } else {
        return false;
    }
});

;define('lib/fastclick',[],function(require,exports,module){});function FastClick(layer){'use strict';var oldOnClick,self=this;this.trackingClick=false;this.trackingClickStart=0;this.targetElement=null;this.touchStartX=0;this.touchStartY=0;this.lastTouchIdentifier=0;this.touchBoundary=10;this.layer=layer;if(!layer||!layer.nodeType){throw new TypeError('Layer must be a document node');}
this.onClick=function(){return FastClick.prototype.onClick.apply(self,arguments);};this.onMouse=function(){return FastClick.prototype.onMouse.apply(self,arguments);};this.onTouchStart=function(){return FastClick.prototype.onTouchStart.apply(self,arguments);};this.onTouchMove=function(){return FastClick.prototype.onTouchMove.apply(self,arguments);};this.onTouchEnd=function(){return FastClick.prototype.onTouchEnd.apply(self,arguments);};this.onTouchCancel=function(){return FastClick.prototype.onTouchCancel.apply(self,arguments);};if(FastClick.notNeeded(layer)){return;}
if(this.deviceIsAndroid){layer.addEventListener('mouseover',this.onMouse,true);layer.addEventListener('mousedown',this.onMouse,true);layer.addEventListener('mouseup',this.onMouse,true);}
layer.addEventListener('click',this.onClick,true);layer.addEventListener('touchstart',this.onTouchStart,false);layer.addEventListener('touchmove',this.onTouchMove,false);layer.addEventListener('touchend',this.onTouchEnd,false);layer.addEventListener('touchcancel',this.onTouchCancel,false);if(!Event.prototype.stopImmediatePropagation){layer.removeEventListener=function(type,callback,capture){var rmv=Node.prototype.removeEventListener;if(type==='click'){rmv.call(layer,type,callback.hijacked||callback,capture);}else{rmv.call(layer,type,callback,capture);}};layer.addEventListener=function(type,callback,capture){var adv=Node.prototype.addEventListener;if(type==='click'){adv.call(layer,type,callback.hijacked||(callback.hijacked=function(event){if(!event.propagationStopped){callback(event);}}),capture);}else{adv.call(layer,type,callback,capture);}};}
if(typeof layer.onclick==='function'){oldOnClick=layer.onclick;layer.addEventListener('click',function(event){oldOnClick(event);},false);layer.onclick=null;}}
FastClick.prototype.deviceIsAndroid=navigator.userAgent.indexOf('Android')>0;FastClick.prototype.deviceIsIOS=/iP(ad|hone|od)/.test(navigator.userAgent);FastClick.prototype.deviceIsIOS4=FastClick.prototype.deviceIsIOS&&(/OS 4_\d(_\d)?/).test(navigator.userAgent);FastClick.prototype.deviceIsIOSWithBadTarget=FastClick.prototype.deviceIsIOS&&(/OS ([6-9]|\d{2})_\d/).test(navigator.userAgent);FastClick.prototype.needsClick=function(target){'use strict';switch(target.nodeName.toLowerCase()){case'button':case'select':case'textarea':if(target.disabled){return true;}
break;case'input':if((this.deviceIsIOS&&target.type==='file')||target.disabled){return true;}
break;case'label':case'video':return true;}
return(/\bneedsclick\b/).test(target.className);};FastClick.prototype.needsFocus=function(target){'use strict';switch(target.nodeName.toLowerCase()){case'textarea':return true;case'select':return!this.deviceIsAndroid;case'input':switch(target.type){case'button':case'checkbox':case'file':case'image':case'radio':case'submit':return false;}
return!target.disabled&&!target.readOnly;default:return(/\bneedsfocus\b/).test(target.className);}};FastClick.prototype.sendClick=function(targetElement,event){'use strict';var clickEvent,touch;if(document.activeElement&&document.activeElement!==targetElement){document.activeElement.blur();}
touch=event.changedTouches[0];clickEvent=document.createEvent('MouseEvents');clickEvent.initMouseEvent(this.determineEventType(targetElement),true,true,window,1,touch.screenX,touch.screenY,touch.clientX,touch.clientY,false,false,false,false,0,null);clickEvent.forwardedTouchEvent=true;targetElement.dispatchEvent(clickEvent);};FastClick.prototype.determineEventType=function(targetElement){'use strict';if(this.deviceIsAndroid&&targetElement.tagName.toLowerCase()==='select'){return'mousedown';}
return'click';};FastClick.prototype.focus=function(targetElement){'use strict';var length;if(this.deviceIsIOS&&targetElement.setSelectionRange&&targetElement.type.indexOf('date')!==0&&targetElement.type!=='time'){length=targetElement.value.length;targetElement.setSelectionRange(length,length);}else{targetElement.focus();}};FastClick.prototype.updateScrollParent=function(targetElement){'use strict';var scrollParent,parentElement;scrollParent=targetElement.fastClickScrollParent;if(!scrollParent||!scrollParent.contains(targetElement)){parentElement=targetElement;do{if(parentElement.scrollHeight>parentElement.offsetHeight){scrollParent=parentElement;targetElement.fastClickScrollParent=parentElement;break;}
parentElement=parentElement.parentElement;}while(parentElement);}
if(scrollParent){scrollParent.fastClickLastScrollTop=scrollParent.scrollTop;}};FastClick.prototype.getTargetElementFromEventTarget=function(eventTarget){'use strict';if(eventTarget.nodeType===Node.TEXT_NODE){return eventTarget.parentNode;}
return eventTarget;};FastClick.prototype.onTouchStart=function(event){'use strict';var targetElement,touch,selection;if(event.targetTouches.length>1){return true;}
targetElement=this.getTargetElementFromEventTarget(event.target);touch=event.targetTouches[0];if(this.deviceIsIOS){selection=window.getSelection();if(selection.rangeCount&&!selection.isCollapsed){return true;}
if(!this.deviceIsIOS4){if(touch.identifier===this.lastTouchIdentifier){event.preventDefault();return false;}
this.lastTouchIdentifier=touch.identifier;this.updateScrollParent(targetElement);}}
this.trackingClick=true;this.trackingClickStart=event.timeStamp;this.targetElement=targetElement;this.touchStartX=touch.pageX;this.touchStartY=touch.pageY;if((event.timeStamp-this.lastClickTime)<200){event.preventDefault();}
return true;};FastClick.prototype.touchHasMoved=function(event){'use strict';var touch=event.changedTouches[0],boundary=this.touchBoundary;if(Math.abs(touch.pageX-this.touchStartX)>boundary||Math.abs(touch.pageY-this.touchStartY)>boundary){return true;}
return false;};FastClick.prototype.onTouchMove=function(event){'use strict';if(!this.trackingClick){return true;}
if(this.targetElement!==this.getTargetElementFromEventTarget(event.target)||this.touchHasMoved(event)){this.trackingClick=false;this.targetElement=null;}
return true;};FastClick.prototype.findControl=function(labelElement){'use strict';if(labelElement.control!==undefined){return labelElement.control;}
if(labelElement.htmlFor){return document.getElementById(labelElement.htmlFor);}
return labelElement.querySelector('button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea');};FastClick.prototype.onTouchEnd=function(event){'use strict';var forElement,trackingClickStart,targetTagName,scrollParent,touch,targetElement=this.targetElement;if(!this.trackingClick){return true;}
if((event.timeStamp-this.lastClickTime)<200){this.cancelNextClick=true;return true;}
this.cancelNextClick=false;this.lastClickTime=event.timeStamp;trackingClickStart=this.trackingClickStart;this.trackingClick=false;this.trackingClickStart=0;if(this.deviceIsIOSWithBadTarget){touch=event.changedTouches[0];targetElement=document.elementFromPoint(touch.pageX-window.pageXOffset,touch.pageY-window.pageYOffset)||targetElement;targetElement.fastClickScrollParent=this.targetElement.fastClickScrollParent;}
targetTagName=targetElement.tagName.toLowerCase();if(targetTagName==='label'){forElement=this.findControl(targetElement);if(forElement){this.focus(targetElement);if(this.deviceIsAndroid){return false;}
targetElement=forElement;}}else if(this.needsFocus(targetElement)){if((event.timeStamp-trackingClickStart)>100||(this.deviceIsIOS&&window.top!==window&&targetTagName==='input')){this.targetElement=null;return false;}
this.focus(targetElement);if(!this.deviceIsIOS4||targetTagName!=='select'){this.targetElement=null;event.preventDefault();}
return false;}
if(this.deviceIsIOS&&!this.deviceIsIOS4){scrollParent=targetElement.fastClickScrollParent;if(scrollParent&&scrollParent.fastClickLastScrollTop!==scrollParent.scrollTop){return true;}}
if(!this.needsClick(targetElement)){event.preventDefault();this.sendClick(targetElement,event);}
return false;};FastClick.prototype.onTouchCancel=function(){'use strict';this.trackingClick=false;this.targetElement=null;};FastClick.prototype.onMouse=function(event){'use strict';if(!this.targetElement){return true;}
if(event.forwardedTouchEvent){return true;}
if(!event.cancelable){return true;}
if(!this.needsClick(this.targetElement)||this.cancelNextClick){if(event.stopImmediatePropagation){event.stopImmediatePropagation();}else{event.propagationStopped=true;}
event.stopPropagation();event.preventDefault();return false;}
return true;};FastClick.prototype.onClick=function(event){'use strict';var permitted;if(this.trackingClick){this.targetElement=null;this.trackingClick=false;return true;}
if(event.target.type==='submit'&&event.detail===0){return true;}
permitted=this.onMouse(event);if(!permitted){this.targetElement=null;}
return permitted;};FastClick.prototype.destroy=function(){'use strict';var layer=this.layer;if(this.deviceIsAndroid){layer.removeEventListener('mouseover',this.onMouse,true);layer.removeEventListener('mousedown',this.onMouse,true);layer.removeEventListener('mouseup',this.onMouse,true);}
layer.removeEventListener('click',this.onClick,true);layer.removeEventListener('touchstart',this.onTouchStart,false);layer.removeEventListener('touchmove',this.onTouchMove,false);layer.removeEventListener('touchend',this.onTouchEnd,false);layer.removeEventListener('touchcancel',this.onTouchCancel,false);};FastClick.notNeeded=function(layer){'use strict';var metaViewport;var chromeVersion;if(typeof window.ontouchstart==='undefined'){return true;}
chromeVersion=+(/Chrome\/([0-9]+)/.exec(navigator.userAgent)||[,0])[1];if(chromeVersion){if(FastClick.prototype.deviceIsAndroid){metaViewport=document.querySelector('meta[name=viewport]');if(metaViewport){if(metaViewport.content.indexOf('user-scalable=no')!==-1){return true;}
if(chromeVersion>31&&window.innerWidth<=window.screen.width){return true;}}}else{return true;}}
if(layer.style.msTouchAction==='none'){return true;}
return false;};FastClick.attach=function(layer){'use strict';return new FastClick(layer);};if(typeof define!=='undefined'&&define.amd){define(function(){'use strict';return FastClick;});}else if(typeof module!=='undefined'&&module.exports){module.exports=FastClick.attach;module.exports.FastClick=FastClick;}else{window.FastClick=FastClick;}
FastClick.attach(document.body);;define('lib/global',['module/netType','lib/jquery.min'],function(require,exports,module){window.jq=jQuery.noConflict();var netType=require('module/netType');try{netType.init();}catch(e){console.log('get netType error'+e);}
jQuery.extend(jQuery.ajax,{_requestCache:{}});jQuery.extend({DIC:{charset:'utf-8',getcookie:function(name){var cookies=document.cookie.split('; ');var len=cookies.length;for(var i=0;i<len;i++){var cookie=cookies[i].split('=');if(cookie[0]==name){return unescape(cookie[1]);}}
return'';},in_array:function(needle,haystack){if(typeof needle=='string'||typeof needle=='number'){for(var i in haystack){if(haystack[i]==needle){return true;}}}
return false;},setcookie:function(cookieName,cookieValue,seconds,path,domain,secure){var expires=new Date();expires.setTime(expires.getTime()+seconds);document.cookie=escape(cookieName)+'='+escape(cookieValue)
+(expires?'; expires='+expires.toGMTString():'')
+(path?'; path='+path:'/')
+(domain?'; domain='+domain:'')
+(secure?'; secure':'');},parseStr:function(str,key){var params=str.split('&');var query={};var q=[];var name='';for(i=0;i<params.length;i++){q=params[i].split('=');name=decodeURIComponent(q[0]);if(!name){continue;}
if(name.substr(-2)=='[]'){if(!query[name]){query[name]=[];}
query[name].push(q[1]);}else{query[name]=q[1];}}
if(key){if(query[key]){return query[key];}
return null;}else{return query;}},getQuery:function(key){var search=window.location.search;if(search.indexOf('?')!=-1){var str=search.substr(1);return jQuery.DIC.parseStr(str,key);}},reload:function(href,timeout){href=href||'';timeout=timeout||1;re=/^http(s)?:\/\/(([^\/\.]+\.)*)?(qq\.com)(\/.*)*$/;re1=/^http(s)?:\/\//;setTimeout(function(){if(re.test(href)){window.location.href=href;}else if(href&&!re1.test(href)){window.location.href=window.DOMAIN.substr(0,window.DOMAIN.length-1)+href;}else{window.location.reload();}},timeout);},open:function(href){re1=/^http(s)?:\/\//;if(href&&!re1.test(href)){href=window.DOMAIN.substr(0,window.DOMAIN.length-1)+href;}
if(typeof mqq!=='undefined'&&mqq.version&&mqq.device.isMobileQQ()){href=href.indexOf('?')===-1?href+'?':href+'&';href=href.replace(/\&webview\=[^\&]+/g,'')+'webview=new';mqq.ui.openUrl({url:href,target:1,style:0});return true;}
jQuery.DIC.reload(href);},goBack:function(url){var url=url||'';if(mqq&&mqq.version&&mqq.device.isMobileQQ()){if(/(\?|\&)webview=new/.test(window.location.href)){mqq.ui.popBack();return true;}}
if(url){jq.DIC.reload(url);return true;}
history.go(-1);},trim:function(str){return str.replace(/(^\s*)|(\s*$)/g,'');},isObjectEmpty:function(obj){for(i in obj){return false;}
return true;},strlen:function(str){return(/msie/.test(navigator.userAgent.toLowerCase())&&str.indexOf('\n')!==-1)?str.replace(/\r?\n/g,'_').length:str.length;},mb_strlen:function(str){var len=0;for(var i=0;i<str.length;i++){len+=str.charCodeAt(i)<0||str.charCodeAt(i)>255?(jQuery.DIC.charset.toLowerCase()==='utf-8'?3:2):1;}
return len;},mb_cutstr:function(str,maxlen,dot){var len=0;var ret='';var dot=!dot&&dot!==''?'...':dot;maxlen=maxlen-dot.length;for(var i=0;i<str.length;i++){len+=str.charCodeAt(i)<0||str.charCodeAt(i)>255?(jQuery.DIC.charset.toLowerCase()==='utf-8'?3:2):1;if(len>maxlen){ret+=dot;break;}
ret+=str.substr(i,1);}
return ret;},strLenCalc:function(obj,showId,maxlen){var v=obj.value,maxlen=!maxlen?200:maxlen,curlen=maxlen,len=jQuery.DIC.strlen(v);for(var i=0;i<v.length;i++){if(v.charCodeAt(i)<0||v.charCodeAt(i)>127){curlen-=2;}else{curlen-=1;}}
jQuery('#'+showId).html(Math.floor(curlen/2));},dialog:function(opts){setTimeout(function(){jQuery.DIC._dialog(opts);},5);},_dialog:function(opts){var opts=opts||{};var dId=opts.id||'tips';var dialogId='fwin_dialog_'+dId;var maskId='fwin_mask_'+dId;if(!opts.content){document.ontouchmove=function(e){return true;}
jQuery('#'+dialogId).remove();jQuery('#'+maskId).remove();return false;}
var title=opts.title||'提示信息';var content=opts.content||'';var btnOk=opts.okValue||false;var btnCancel=opts.cancelValue||false;var isShowMask=opts.isMask||false;var existDialogCount=jq('div[id^="fwin_dialog_"]').length||0;var maskZIndex=10000+existDialogCount*10;var dialogZIndex=maskZIndex+1;var maskStyle='position:absolute;top:-0px;left:-0px;width:'+jQuery(document).width()+'px;height:'+jQuery(document).height()+'px;background:#000;filter:alpha(opacity=60);opacity:0.5; z-index:'+maskZIndex+';';var isHtml=opts.isHtml||false;var autoClose=opts.autoClose||false;var isConfirm=opts.isConfirm||false;var iconClass='';switch(opts.icon){case'success':iconClass='icon_success';break;case'none':iconClass='';break;case'error':default:iconClass='g-layer-tips';break;}
var dialogHtmlArr=[];if(isShowMask){var dialogMaskHtmlArr=[];dialogMaskHtmlArr.push('<div id='+maskId+' class="g-mask" style="'+maskStyle+'"></div>');var dialogMaskHtml=dialogMaskHtmlArr.join('');jQuery(dialogMaskHtml).appendTo('body');document.ontouchmove=function(e){e.preventDefault();}}
if(isHtml){dialogHtmlArr.push('<div style="width:100%;position:fixed;z-index:'+dialogZIndex+';" id="'+dialogId+'">'+content+'</div>');}else{if(!opts.title&&!btnOk&&!btnCancel){dialogHtmlArr.push('<div style="position:fixed;z-index:'+dialogZIndex+';" id="'+dialogId+'"><div class="tips br5">');if(dId=='loading'){dialogHtmlArr.push('<div class="loadicon tipL" style="vertical-align: -5px;"><span class="blockG" id="rotateG_01"></span><span class="blockG" id="rotateG_02"></span><span class="blockG" id="rotateG_03"></span><span class="blockG" id="rotateG_04"></span><span class="blockG" id="rotateG_05"></span><span class="blockG" id="rotateG_06"></span><span class="blockG" id="rotateG_07"></span><span class="blockG" id="rotateG_08"></span></div> ');}
dialogHtmlArr.push(content+'</div></div>');}else if(isConfirm){if(confirm(content)){if(typeof opts.ok=='function'){opts.ok();}}else{if(typeof opts.cancel=='function'){opts.cancel();}}
return true;}else{dialogHtmlArr.push('<div style="min-width:350px;position:fixed;z-index:'+dialogZIndex+';" id="'+dialogId+'"><span class="close"></span>');dialogHtmlArr.push('<div class="popLayer pSpace" style="width:80%">');dialogHtmlArr.push('<p class="editTCon">'+content+'</p>');dialogHtmlArr.push('<div class="editArea">');dialogHtmlArr.push(btnOk?'<a href="javascript:;" class="editBtn1 db" title="">'+btnOk+'</a>':'');dialogHtmlArr.push(btnCancel?'<a href="javascript:;" class="editBtn2 db" title="">'+btnCancel+'</a>':'');dialogHtmlArr.push('</div></div>');}}
var dialogHtml=dialogHtmlArr.join('');if(jQuery('#'+dialogId)[0]){jQuery('#'+dialogId).remove();jQuery('#'+maskId).remove();}
jQuery(dialogHtml).appendTo('body');var clientWidth=jQuery(window).width();var clientHeight=jQuery(window).height();dialogLeft=(clientWidth-jQuery('#'+dialogId).outerWidth())/2;dialogTop=(clientHeight-jQuery('#'+dialogId).height())*0.382;var dialogLeft=opts.left||dialogLeft;var dialogTop=typeof opts.top==='undefined'?dialogTop:opts.top;jQuery("#"+dialogId).css({"top":dialogTop+"px","left":dialogLeft+"px"});jQuery('#'+dialogId+' .close').click(function(){if(isShowMask){document.ontouchmove=function(e){return true;}}
var closeCBResult=true;if(typeof opts.close=='function'){closeCBResult=opts.close();}
if(closeCBResult){jQuery('#'+maskId).hide();jQuery('#'+maskId).remove();jQuery('#'+dialogId).hide();jQuery('#'+dialogId).remove();}});if(typeof opts.callback=='function'){if(isShowMask){document.ontouchmove=function(e){e.preventDefault();}}
opts.callback();}
if(typeof opts.ok=='function'){jQuery('#'+dialogId+' .editBtn1').click(function(){opts.ok();});}
if(typeof opts.cancel=='function'){jQuery('#'+dialogId+' .editBtn2').click(function(){opts.cancel();});}
if(jQuery('#'+dialogId+' .editBtn1')[0]){jQuery('#'+dialogId+' .editBtn1').click(function(){jQuery('#'+dialogId+' .close').click()});}
if(jQuery('#'+dialogId+' .editBtn2')[0]){jQuery('#'+dialogId+' .editBtn2').click(function(){jQuery('#'+dialogId+' .close').click()});}
if(!opts.title&&!btnOk&&!btnCancel&&autoClose){autoClose=autoClose>1?autoClose:1000;setTimeout(function(){jQuery('#'+dialogId).fadeOut('slow',function(){jQuery('#'+maskId).hide();jQuery('#'+maskId).remove();jQuery('#'+dialogId).hide();jQuery('#'+dialogId).remove();if(typeof opts.close=='function'){opts.close();}});},autoClose);}},timerId:false,initTouch:function(opts){var obj=opts.obj||document;var startX,startY,endX,endY,moveTouch;function touchStart(event){var touch=event.touches[0];startY=touch.pageY;startX=touch.pageX;endX=touch.pageX;endY=touch.pageY;if(typeof opts.start=='function'){opts.start(event);}}
function touchMove(event){window.clearInterval(jq.DIC.timerId);touch=event.touches[0];endX=touch.pageX;endY=touch.pageY;if(document.body.scrollTop<=0&&(startY-endY)<=0&&jQuery.os.ios){event.preventDefault();}
if(typeof opts.move=='function'){var offset={x:startX-endX,y:startY-endY};opts.move(event,offset);}
if(!jQuery.os.ios){jq.DIC.timerId=window.setTimeout(function(){touchEnd();},50);}}
function touchEnd(event){if(typeof opts.end=='function'){var offset={x:startX-endX,y:startY-endY};opts.end(event,offset);}}
obj.addEventListener('touchstart',touchStart,false);obj.addEventListener('touchmove',touchMove,false);if(jQuery.os.ios){obj.addEventListener('touchend',touchEnd,false);}},showLoading:function(display,waiting,autoClose){var display=display||'block';var autoClose=autoClose||false;waiting=waiting||'正在加载...';if(display=='block'){jQuery.DIC.dialog({id:'loading',content:waiting,noMask:true,autoClose:autoClose});}else{jQuery.DIC.dialog({id:'loading'});}},ajax:function(url,data,opts){var opts=opts||{};var loadingTimer='';url=url.indexOf('?')===-1?url+'?':url+'&';url=url.replace(/\&resType\=[^\&]+/g,'')+'resType=json';url=url.replace(/\&isAjax\=1/g,'')+'&isAjax=1';var ajaxOpts={url:url,data:data,cache:opts.cache||false,processData:opts.isUpload,contentType:opts.isUpload?false:'application/x-www-form-urlencoded',type:data?'POST':'GET',dataType:opts.dataType||'json',timeout:opts.timeout||30000,jsonp:opts.dataType==='jsonp'?'callback':null,jsonpCallback:opts.dataType==='jsonp'?opts.success:null,beforeSend:function(XHR,option){if(opts.requestIndex){if(opts.requestMode=='block'){if(jQuery.ajax._requestCache[opts.requestIndex]){return false;}}else if(opts.requestMode=='abort'){if(jQuery.ajax._requestCache[opts.requestIndex]){jQuery.ajax._requestCache[opts.requestIndex].abort();}}}
var result=true;if(typeof opts.beforeSend=='function'){result=opts.beforeSend(XHR,option);}
if(result){jQuery.ajax._requestCache[opts.requestIndex]=XHR;if(!opts.noShowLoading){loadingTimer=setTimeout(function(){jQuery.DIC.showLoading();},100);}}
return result;},complete:function(XHR,status){if(jQuery.ajax._requestCache[opts.requestIndex]){jQuery.ajax._requestCache[opts.requestIndex]=null;}
if(typeof opts.complete=='function'){opts.complete(XHR,status);}},success:function(result,textStatus,c){clearTimeout(loadingTimer);jQuery.DIC.showLoading('none');if(result==null&&!opts.noMsg){jQuery.DIC.dialog({content:'您的网络有些问题，请稍后再试 [code:1]',autoClose:true});}
if(typeof result!=='object'){result=jQuery.parseJSON(result);}
if(typeof opts.success=='function'){opts.success(result,textStatus,opts);}
if(result.errCode==0){if(!opts.noMsg){jQuery.DIC.dialog({content:result.message,icon:'success',autoClose:true});}
if(!opts.noJump&&result.jumpURL){var locationTime=result.locationTime||2000;jQuery.DIC.reload(result.jumpURL,locationTime);}}else if(result.errCode){if(!opts.noMsg){var msg=result.message+'<span style="display:none;">'+result.errCode+'</span>';jQuery.DIC.dialog({content:msg,autoClose:true});}}else{if(!opts.noMsg){jQuery.DIC.dialog({content:'数据解析失败，请稍后再试 [code:2]',autoClose:true});}}},error:function(XHR,info,errorThrown){clearTimeout(loadingTimer);jQuery.DIC.showLoading('none');if(XHR.readyState==0||XHR.status==0){if(!opts.noMsg){var msg='您的网络有些问题，请稍后再试[code:4]';jQuery.DIC.dialog({content:msg,autoClose:true});}}else if(info!='abort'&&!opts.noMsg){if(!opts.noMsg){var msg='';switch(info){case'timeout':msg='对不起，请求服务器网络超时';break;case'error':msg='网络出现异常，请求服务器错误';break;case'parsererror':msg='网络出现异常，服务器返回错误';break;case'notmodified':default:msg='您的网络有些问题，请稍后再试[code:3]';}
jQuery.DIC.dialog({content:msg,autoClose:true});}}
if(typeof opts.error=='function'){opts.error();}}};jQuery.ajax(ajaxOpts);return false;},ajaxForm:function(formId,opt,isSubmit){var opt=opt||{};var loadingTimer='';var url=opt.url||jQuery('#'+formId).prop('action');url=url.indexOf('?')===-1?url+'?':url+'&';url=url.replace(/\&resType\=[^\&]+/g,'')+'resType=json';url=url.replace(/\&isAjax\=1/g,'')+'&isAjax=1';var formOpt={beforeSubmit:function(formData,jqForm,options){if(jQuery.ajax._requestCache[formId]==1){return false;}
var result=true;if(typeof opt.beforeSubmit=='function'){result=opt.beforeSubmit(formData,jqForm,options,opt);}
if(result){jQuery.ajax._requestCache[formId]=1;if(!opt.noShowLoading){loadingTimer=setTimeout(function(){jQuery.DIC.showLoading();},100);}}
return result;},success:function(result,statusText){jQuery.ajax._requestCache[formId]=null;clearTimeout(loadingTimer);jQuery.DIC.showLoading('none');if(result==null&&!opt.noMsg){jQuery.DIC.dialog({content:'您的网络有些问题，请稍后再试 [code:1]',autoClose:true});}
if(typeof result!=='object'){result=jQuery.parseJSON(result);}
if(typeof opt.success=='function'){opt.success(result,statusText,opt);}
if(result.errCode==0){if(!opt.noMsg){jQuery.DIC.dialog({content:result.message,icon:'success',autoClose:true});}
if(!opt.noJump&&result.jumpURL){var locationTime=result.locationTime||2000;jQuery.DIC.reload(result.jumpURL,locationTime);}}else if(result.errCode){if(!opt.noMsg){var msg=result.message+'<span style="display:none;">'+result.errCode+'</span>';jQuery.DIC.dialog({content:msg,autoClose:true});}}else{if(!opt.noMsg){jQuery.DIC.dialog({content:'数据解析失败，请稍后再试 [code:2]',autoClose:true});}}},url:url,cache:opt.cache||false,clearForm:opt.clearForm,resetForm:opt.resetForm,timeout:opt.timeout||15000,dataType:opt.dataType||'json',error:function(XHR,info,errorThrown){jQuery.ajax._requestCache[formId]=null;clearTimeout(loadingTimer);jQuery.DIC.showLoading('none');if(!opt.noMsg){var msg='';switch(info){case'timeout':msg='对不起，请求服务器超时';break;case'error':msg='网络出现异常，请求服务器错误';break;case'parsererror':msg='网络出现异常，服务器返回错误';break;case'notmodified':default:msg='您的网络有些问题，请稍后再试[code:3]';}
jQuery.DIC.dialog({content:msg,autoClose:true});}
if(typeof opt.error=='function'){opt.error(info);}}};isSubmit=isSubmit||false;if(isSubmit){jQuery('#'+formId).ajaxSubmit(formOpt);}else{jQuery('#'+formId).ajaxForm(formOpt);}
return false;},touchState:function(rule,style,containerRule){style=style||'commBg';containerRule=containerRule||'';if(containerRule){jq(containerRule).on('tap',rule,function(){obj=jq(this);obj.addClass(style);setTimeout(function(){obj.removeClass(style);},100);});}else{if(typeof rule=='object'){var touchObj=rule;}else{var touchObj=jq(rule);}
touchObj.on('tap',function(){obj=jq(this);obj.addClass(style);setTimeout(function(){obj.removeClass(style);},100);});}},touchStateNow:function(obj,style){style=style||'commBg';obj.addClass(style);setTimeout(function(){obj.removeClass(style);},100);},handleLink:function(rule,containerRule){containerRule=containerRule||'';if(containerRule){jq(containerRule).on('tap',rule,function(e){e.preventDefault();var link=jq(this).attr('_href');jQuery.DIC.reload(link);});}else{jq(rule).on('tap',function(e){e.preventDefault();var link=jq(this).attr('_href');jQuery.DIC.reload(link);});}},likeTips:function(obj){if(!jq('.praiseCon')[0]){var praiseCon='<span class="praiseCon db" style="opacity:0;height:30px"><i class="praisePop db">明日再赞哦~</i></span>';jq(praiseCon).appendTo('body');}
var otop=obj.offset().top-20;var offTop=otop-15;jq('.praiseCon').css({top:otop,right:jq(window).width()-obj.offset().left-obj.width()}).animate({top:offTop+'px',opacity:1},'normal',function(){}).animate({opacity:0},'normal',function(){});}}});jQuery.extend({os:{ios:false,android:false,version:false}});(function(){var ua=navigator.userAgent;var browser={},weixin=ua.match(/MicroMessenger\/([^\s]+)/),webkit=ua.match(/WebKit\/([\d.]+)/),android=ua.match(/(Android)\s+([\d.]+)/),ipad=ua.match(/(iPad).*OS\s([\d_]+)/),ipod=ua.match(/(iPod).*OS\s([\d_]+)/),iphone=!ipod&&!ipad&&ua.match(/(iPhone\sOS)\s([\d_]+)/),webos=ua.match(/(webOS|hpwOS)[\s\/]([\d.]+)/),touchpad=webos&&ua.match(/TouchPad/),kindle=ua.match(/Kindle\/([\d.]+)/),silk=ua.match(/Silk\/([\d._]+)/),blackberry=ua.match(/(BlackBerry).*Version\/([\d.]+)/),mqqbrowser=ua.match(/MQQBrowser\/([\d.]+)/),chrome=ua.match(/CriOS\/([\d.]+)/),opera=ua.match(/Opera\/([\d.]+)/),safari=ua.match(/Safari\/([\d.]+)/);if(weixin){jQuery.os.wx=true;jQuery.os.wxVersion=weixin[1];}
if(android){jQuery.os.android=true;jQuery.os.version=android[2];}
if(iphone){jQuery.os.ios=jQuery.os.iphone=true;jQuery.os.version=iphone[2].replace(/_/g,'.');}
if(ipad){jQuery.os.ios=jQuery.os.ipad=true;jQuery.os.version=ipad[2].replace(/_/g,'.');}
if(ipod){jQuery.os.ios=jQuery.os.ipod=true;jQuery.os.version=ipod[2].replace(/_/g,'.');}
window.htmlEncode=function(text){return text.replace(/&/g,'&amp').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
window.htmlDecode=function(text){return text.replace(/&amp;/g,'&').replace(/&quot;/g,'/"').replace(/&lt;/g,'<').replace(/&gt;/g,'>');}
window.NETTYPE=0;window.NETTYPE_FAIL=-1;window.NETTYPE_WIFI=1;window.NETTYPE_EDGE=2;window.NETTYPE_3G=3;window.NETTYPE_DEFAULT=0;})();window.parseJSON=function(data){return(new Function('','return '+data))()||{};}
window.initLazyload=function(container,conf){conf=conf||{};var defaultConf={visibleOnly:false,srcAttr:'data-original',selector:''};jq.extend(jq.lazyLoadXT,defaultConf);jq(container).lazyLoadXT(conf);jq(window).ajaxComplete(function(){setTimeout(function(){jq(container).lazyLoadXT(conf);},50);});}});;define('module/netType',[],function(require,exports,module){"require:nomunge,exports:nomunge,module:nomunge";module.exports={key:'wsq_nettype',init:function(){if(isWX){if(typeof WeixinJSBridge!='undefined'){module.exports.wxNetType();}else{jq(document).bind('WeixinJSBridgeReady',function(){module.exports.wxNetType();});}}else if(typeof mqq!=='undefined'&&mqq.version&&mqq.device.isMobileQQ()){module.exports.mqqNetType();}
window.NETTYPE=localStorage.getItem(module.exports.key)||window.NETTYPE_DEFAULT;},wxNetType:function(){WeixinJSBridge.invoke('getNetworkType',{},function(e){switch(e.err_msg){case'network_type:wifi':localStorage.setItem(module.exports.key,window.NETTYPE_WIFI);break;case'network_type:edge':localStorage.setItem(module.exports.key,window.NETTYPE_EDGE);break;case'network_type:wwan':localStorage.setItem(module.exports.key,window.NETTYPE_EDGE);break;case'network_type:fail':localStorage.setItem(module.exports.key,window.NETTYPE_FAIL);break;default:break;}});},mqqNetType:function(){mqq.device.getNetworkType(function(result){switch(result){case 0:localStorage.setItem(module.exports.key,window.NETTYPE_FAIL);break;case 1:localStorage.setItem(module.exports.key,window.NETTYPE_WIFI);break;case 2:localStorage.setItem(module.exports.key,window.NETTYPE_EDGE);break;case 3:localStorage.setItem(module.exports.key,window.NETTYPE_3G);break;default:break;}});}};});;define('lib/stat',[],function(require,exports,module){"require:nomunge,exports:nomunge,module:nomunge";window.QZFL=window.QZFL||{};QZFL.pingSender=function(url,t,opts){var _s=QZFL.pingSender,iid,img;if(!url){return;}
opts=opts||{};iid="sndImg_"+_s._sndCount++;img=_s._sndPool[iid]=new Image();img.iid=iid;img.onload=img.onerror=img.ontimeout=(function(t){return function(evt){evt=evt||window.event||{type:'timeout'};void(typeof(opts[evt.type])=='function'?setTimeout((function(et,ti){return function(){opts[et]({'type':et,'duration':((new Date()).getTime()-ti)});};})(evt.type,t._s_),0):0);QZFL.pingSender._clearFn(evt,t);};})(img);(typeof(opts.timeout)=='function')&&setTimeout(function(){img.ontimeout&&img.ontimeout({type:'timeout'});},(typeof(opts.timeoutValue)=='number'?Math.max(100,opts.timeoutValue):5000));void((typeof(t)=='number')?setTimeout(function(){img._s_=(new Date()).getTime();img.src=url;},(t=Math.max(0,t))):(img.src=url));};QZFL.pingSender._sndPool={};QZFL.pingSender._sndCount=0;QZFL.pingSender._clearFn=function(evt,ref){var _s=QZFL.pingSender;if(ref){_s._sndPool[ref.iid]=ref.onload=ref.onerror=ref.ontimeout=ref._s_=null;delete _s._sndPool[ref.iid];_s._sndCount--;ref=null;}};if(typeof(window.TCISD)=="undefined"){window.TCISD={};}
TCISD.pv=function(sDomain,path,opts){setTimeout(function(){TCISD.pv.send(sDomain,path,opts);},0);};(function(){var items=[],timer=null,unloadHandler,noDelay=false;var pvSender={send:function(domain,url,rDomain,rUrl,flashVersion,timeout){items.push({dm:domain,url:url,rdm:rDomain||"",rurl:rUrl||"",flashVersion:flashVersion});if(!timer){timer=setTimeout(function(){pvSender.doSend(timeout);},timeout);}
if(!unloadHandler){unloadHandler=pvSender.onUnload;if(window.attachEvent){window.attachEvent("onbeforeunload",unloadHandler);window.attachEvent("onunload",unloadHandler);}else if(window.addEventListener){window.addEventListener("beforeunload",unloadHandler,false);window.addEventListener("unload",unloadHandler,false);}}},onUnload:function(){noDelay=true;pvSender.doSend();setTimeout(function(){},1000);},doSend:function(timeout){timer=null;if(items.length){var url;for(var i=0;i<items.length;i++){url=pvSender.getUrl(items.slice(0,items.length-i));if(url.length<2000){break;}}
items=items.slice(Math.max(items.length-i,1));QZFL.pingSender(url);if(i>0){noDelay?pvSender.doSend():(timer=setTimeout(pvSender.doSend,(typeof timeout=='undefined'?5000:timeout)));}}},getUrl:function(list){var item=list[0];var data={dm:escape(item.dm),url:escape(item.url),rdm:escape(item.rdm),rurl:escape(item.rurl),flash:item.flashVersion,pgv_pvid:pvSender.getId(),sds:Math.random()};var ext=[];for(var i=1;i<list.length;i++){var p=list[i];ext.push([escape(p.dm),escape(p.url),escape(p.rdm),escape(p.rurl)].join(":"));}
if(ext.length){data.ex_dm=ext.join(";")}
var param=[];for(var p in data){param.push(p+"="+data[p]);}
var url=[TCISD.pv.config.webServerInterfaceURL,"?cc=-&ct=-&java=1&lang=-&pf=-&scl=-&scr=-&tt=-&tz=-8&vs=3.3&",param.join("&")].join("");return url;},getId:function(){var t,d,h,f;t=document.cookie.match(TCISD.pv._cookieP);if(t&&t.length&&t.length>1){d=t[1];}else{d=(Math.round(Math.random()*2147483647)*(new Date().getUTCMilliseconds()))%10000000000;document.cookie="pgv_pvid="+d+"; path=/; domain=qq.com; expires=Sun, 18 Jan 2038 00:00:00 GMT;";}
h=document.cookie.match(TCISD.pv._cookieSSID);if(!h){f=(Math.round(Math.random()*2147483647)*(new Date().getUTCMilliseconds()))%10000000000;document.cookie="pgv_info=ssid=s"+f+"; path=/; domain=qq.com;";}
return d;}};TCISD.pv.send=function(sDomain,path,opts){sDomain=sDomain||location.hostname||"-";path=path||location.pathname;opts=opts||{};opts.referURL=opts.referURL||document.referrer;var t,d,r;t=opts.referURL.split(TCISD.pv._urlSpliter);t=t[0];t=t.split("/");d=t[2]||"-";r="/"+t.slice(3).join("/");opts.referDomain=opts.referDomain||d;opts.referPath=opts.referPath||r;opts.timeout=typeof opts.timeout=='undefined'?5000:opts.timeout;pvSender.send(sDomain,path,opts.referDomain,opts.referPath,(opts.flashVersion||""),opts.timeout);};})();TCISD.pv._urlSpliter=/[\?\#]/;TCISD.pv._cookieP=/(?:^|;+|\s+)pgv_pvid=([^;]*)/i;TCISD.pv._cookieSSID=/(?:^|;+|\s+)pgv_info=([^;]*)/i;TCISD.pv.config={webServerInterfaceURL:"http://pingfore.qq.com/pingd"};window.TCISD=window.TCISD||{};TCISD.createTimeStat=function(statName,flagArr,standardData){var _s=TCISD.TimeStat,t,instance;flagArr=flagArr||_s.config.defaultFlagArray;t=flagArr.join("_");statName=statName||t;if(instance=_s._instances[statName]){return instance;}else{return(new _s(statName,t,standardData));}};TCISD.markTime=function(timeStampSeq,statName,flagArr,timeObj){var ins=TCISD.createTimeStat(statName,flagArr);ins.mark(timeStampSeq,timeObj);return ins;};TCISD.TimeStat=function(statName,flags,standardData){var _s=TCISD.TimeStat;this.sName=statName;this.flagStr=flags;this.timeStamps=[null];this.zero=_s.config.zero;if(standardData){this.standard=standardData;}
_s._instances[statName]=this;_s._count++;};TCISD.TimeStat.prototype.getData=function(seq){var r={},t,d;if(seq&&(t=this.timeStamps[seq])){d=new Date();d.setTime(this.zero.getTime());r.zero=d;d=new Date();d.setTime(t.getTime());r.time=d;r.duration=t-this.zero;if(this.standard&&(d=this.standard.timeStamps[seq])){r.delayRate=(r.duration-d)/d;}}else{r.timeStamps=TCISD.TimeStat._cloneData(this.timeStamps);}
return r;};TCISD.TimeStat._cloneData=function(obj){if((typeof obj)=='object'){var res=obj.sort?[]:{};for(var i in obj){res[i]=TCISD.TimeStat._cloneData(obj[i]);}
return res;}else if((typeof obj)=='function'){return Object;}
return obj;};TCISD.TimeStat.prototype.mark=function(seq,timeObj){seq=seq||this.timeStamps.length;this.timeStamps[Math.min(Math.abs(seq),99)]=timeObj||(new Date());return this;};TCISD.TimeStat.prototype.merge=function(baseTimeStat){var x,y;if(baseTimeStat&&(typeof(baseTimeStat.timeStamps)=="object")&&baseTimeStat.timeStamps.length){this.timeStamps=baseTimeStat.timeStamps.concat(this.timeStamps.slice(1));}else{return this;}
if(baseTimeStat.standard&&(x=baseTimeStat.standard.timeStamps)){if(!this.standard){this.standard={};}
if(!(y=this.standard.timeStamps)){y=this.standard.timeStamps={};}
for(var key in x){if(!y[key]){y[key]=x[key];}}}
return this;};TCISD.TimeStat.prototype.setZero=function(od){if(typeof(od)!="object"||typeof(od.getTime)!="function"){od=new Date();}
this.zero=od;return this;};TCISD.TimeStat.prototype.report=function(baseURL){var _s=TCISD.TimeStat,url=[],t,z;if((t=this.timeStamps).length<1){return this;}
url.push((baseURL&&baseURL.split("?")[0])||_s.config.webServerInterfaceURL);url.push("?");z=this.zero;for(var i=1,len=t.length;i<len;++i){if(t[i]){url.push(i,"=",t[i].getTime?(t[i]-z):t[i],"&");}}
t=this.flagStr.split("_");for(var i=0,len=_s.config.maxFlagArrayLength;i<len;++i){if(t[i]){url.push("flag",i+1,"=",t[i],"&");}}
if(_s.pluginList&&_s.pluginList.length){for(var i=0,len=_s.pluginList.length;i<len;++i){(typeof(_s.pluginList[i])=='function')&&_s.pluginList[i](url);}}
url.push("sds=",Math.random());QZFL.pingSender&&QZFL.pingSender(url.join(""));return this;};TCISD.TimeStat._instances={};TCISD.TimeStat._count=0;TCISD.TimeStat.config={webServerInterfaceURL:"http://isdspeed.qq.com/cgi-bin/r.cgi",defaultFlagArray:[175,115,1],maxFlagArrayLength:6,zero:window._s_||(new Date())};window.TCISD=window.TCISD||{};TCISD.valueStat=function(statId,resultType,returnValue,opts){setTimeout(function(){TCISD.valueStat.send(statId,resultType,returnValue,opts);},0);};TCISD.valueStat.send=function(statId,resultType,returnValue,opts){var _s=TCISD.valueStat,_c=_s.config,t=_c.defaultParams,p,url=[];statId=statId||t.statId;resultType=resultType||t.resultType;returnValue=returnValue||t.returnValue;opts=opts||t;if(typeof(opts.reportRate)!="number"){opts.reportRate=1;}
opts.reportRate=Math.round(Math.max(opts.reportRate,1));if(!opts.fixReportRateOnly&&!TCISD.valueStat.config.reportAll&&(opts.reportRate>1&&(Math.random()*opts.reportRate)>1)){return;}
url.push((opts.reportURL||_c.webServerInterfaceURL),"?");url.push("flag1=",statId,"&","flag2=",resultType,"&","flag3=",returnValue,"&","1=",(TCISD.valueStat.config.reportAll?1:opts.reportRate),"&","2=",opts.duration,"&");if(typeof opts.extendField!='undefined'){url.push("4=",opts.extendField,"&");}
if(_s.pluginList&&_s.pluginList.length){for(var i=0,len=_s.pluginList.length;i<len;++i){(typeof(_s.pluginList[i])=='function')&&_s.pluginList[i](url);}}
url.push("sds=",Math.random());QZFL.pingSender(url.join(""));};TCISD.valueStat.config={webServerInterfaceURL:"http://isdspeed.qq.com/cgi-bin/v.cgi",defaultParams:{statId:1,resultType:1,returnValue:11,reportRate:1,duration:1000},reportAll:false};if(typeof(window.TCISD)=="undefined"){window.TCISD={};};TCISD.hotClick=function(tag,domain,url,opt){TCISD.hotClick.send(tag,domain,url,opt);};TCISD.hotClick.send=function(tag,domain,url,opt){opt=opt||{};var _s=TCISD.hotClick,x=opt.x||9999,y=opt.y||9999,doc=opt.doc||document,w=doc.parentWindow||doc.defaultView,p=w._hotClick_params||{};url=url||p.url||w.location.pathname||"-";domain=domain||p.domain||w.location.hostname||"-";if(!opt.abs){if(!_s.isReport()){return;}}
url=[_s.config.webServerInterfaceURL,"?dm=",domain+".hot","&url=",escape(url),"&tt=-","&hottag=",tag,"&hotx=",x,"&hoty=",y,"&rand=",Math.random()];QZFL.pingSender(url.join(""));};TCISD.hotClick._arrSend=function(arr,doc){for(var i=0,len=arr.length;i<len;i++){TCISD.hotClick.send(arr[i].tag,arr[i].domain,arr[i].url,{doc:doc});}};TCISD.hotClick.click=function(event,doc){var _s=TCISD.hotClick,tags=_s.getTags(QZFL.event.getTarget(event),doc);_s._arrSend(tags,doc);};TCISD.hotClick.getTags=function(dom,doc){var _s=TCISD.hotClick,tags=[],w=doc.parentWindow||doc.defaultView,rules=w._hotClick_params.rules,t;for(var i=0,len=rules.length;i<len;i++){if(t=rules[i](dom)){tags.push(t);}}
return tags;};TCISD.hotClick.defaultRule=function(dom){var tag,domain,t;tag=dom.getAttribute("hottag");if(tag&&tag.indexOf("|")>-1){t=tag.split("|");tag=t[0];domain=t[1];}
if(tag){return{tag:tag,domain:domain};}
return null;};TCISD.hotClick.config=TCISD.hotClick.config||{webServerInterfaceURL:"http://pinghot.qq.com/pingd",reportRate:1,domain:null,url:null};TCISD.hotClick._reportRate=typeof TCISD.hotClick._reportRate=='undefined'?-1:TCISD.hotClick._reportRate;TCISD.hotClick.isReport=function(){var _s=TCISD.hotClick,rate;if(_s._reportRate!=-1){return _s._reportRate;}
rate=Math.round(_s.config.reportRate);if(rate>1&&(Math.random()*rate)>1){return(_s._reportRate=0);}
return(_s._reportRate=1);};TCISD.hotClick.setConfig=function(opt){opt=opt||{};var _sc=TCISD.hotClick.config,doc=opt.doc||document,w=doc.parentWindow||doc.defaultView;if(opt.domain){w._hotClick_params.domain=opt.domain;}
if(opt.url){w._hotClick_params.url=opt.url;}
if(opt.reportRate){w._hotClick_params.reportRate=opt.reportRate;}};TCISD.hotAddRule=function(handler,opt){opt=opt||{};var _s=TCISD.hotClick,doc=opt.doc||document,w=doc.parentWindow||doc.defaultView;if(!w._hotClick_params){return;}
w._hotClick_params.rules.push(handler);return w._hotClick_params.rules;};TCISD.hotClickWatch=function(opt){opt=opt||{};var _s=TCISD.hotClick,w,l,doc;doc=opt.doc=opt.doc||document;w=doc.parentWindow||doc.defaultView;if(l=doc._hotClick_init){return;}
l=true;if(!w._hotClick_params){w._hotClick_params={};w._hotClick_params.rules=[_s.defaultRule];}
_s.setConfig(opt);w.QZFL.event.addEvent(doc,"click",_s.click,[doc]);};if(typeof(window.TCISD)=='undefined'){window.TCISD={};}
TCISD.stringStat=function(dataId,hashValue,opts){setTimeout(function(){TCISD.stringStat.send(dataId,hashValue,opts);},0);};TCISD.stringStat.send=function(dataId,hashValue,opts){var _s=TCISD.stringStat,_c=_s.config,t=_c.defaultParams,url=[],isPost=false,htmlParam,sd;dataId=dataId||t.dataId;opts=opts||t;isPost=(opts.method&&opts.method=='post')?true:false;if(typeof(hashValue)!="object"){return;}
for(var i in hashValue){if(hashValue[i].length&&hashValue[i].length>1024){hashValue[i]=hashValue[i].substring(0,1024);}}
if(typeof(opts.reportRate)!='number'){opts.reportRate=1;}
opts.reportRate=Math.round(Math.max(opts.reportRate,1));if(opts.reportRate>1&&(Math.random()*opts.reportRate)>1){return;}
if(isPost&&QZFL.FormSender){hashValue.dataId=dataId;hashValue.sds=Math.random();var sd=new QZFL.FormSender(_c.webServerInterfaceURL,'post',hashValue,'UTF-8');sd.send();}else{htmlParam=TCISD.stringStat.genHttpParamString(hashValue);url.push(_c.webServerInterfaceURL,'?');url.push('dataId=',dataId);url.push('&',htmlParam,'&');url.push('ted=',Math.random());QZFL.pingSender(url.join(''));}};TCISD.stringStat.config={webServerInterfaceURL:'http://s.isdspeed.qq.com/cgi-bin/s.fcg',defaultParams:{dataId:1,reportRate:1,method:'get'}};TCISD.stringStat.genHttpParamString=function(o){var res=[];for(var k in o){res.push(k+'='+window.encodeURIComponent(o[k]));}
return res.join('&');};module.exports={reportMap:{1:'css_start',2:'css_end',3:'js_start',4:'js_end',5:'domready',6:'domload',7:'body_view',8:'seajs'},id:[7834,6],init:function(){if(window.debug||!window.statConf||!window.g_ts){return false;}
var idArr=module.exports.id;idArr.push(window.statConf.id);var stat=TCISD.createTimeStat('page',idArr);stat.setZero(g_tsBase);var name='';for(var i in module.exports.reportMap){name=module.exports.reportMap[i];if(typeof g_ts[name]==='object'){stat.mark(i,g_ts[name]);}}
if(!module.exports.isObjectEmpty(window.statConf.map)){for(var i in window.statConf.map){if(i<=8){continue;}
if(typeof g_ts[window.statConf.map[i]]==='object'){stat.mark(i,g_ts[window.statConf.map[i]]);}}}
stat.report();},isObjectEmpty:function(obj){for(i in obj){return false;}
return true;},reportPoint:function(name,id,timeObj,zero){if(window.debug){return false;}
var idArr=module.exports.id;idArr.push(window.statConf.id);var stat=TCISD.createTimeStat(name,idArr);var zero=zero||window.g_tsBase||new Date;var timeObj=timeObj||new Date;stat.setZero(zero);stat.mark(id,timeObj);stat.report();}};});;define('module/topBar',['module/followSite'],function(require,exports,module){"require:nomunge,exports:nomunge,module:nomunge";var followSite=require('module/followSite');module.exports={init:function(){jq('#followButton, .followButton').on('click',function(){var thisObj=jq(this);followSite.followSite.call(thisObj,'site_index');});var MQQBrowser=navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);if(MQQBrowser&&MQQBrowser[1]>='5.2'){require.async('lib/QQBrowser',function(qb){if(typeof window.x5!=='undefined'){window.x5.getAppShowType(function(re){if(re.isLight&&!document.referrer){jq('#goback').hide();}},'');}});}
var re=/^http(s)?:\/\/((mq|wx)\.wsq\.qq\.com)(\/.*)*/;var qqReg=/^http(s)?:\/\/(([^\/\.]+\.)*)?(qq|qzone)\.com(\/.*)*$/;jq('#goback').on('click',function(){var _referer=jq.DIC.getQuery('_referer');if(_referer){jq.DIC.reload(decodeURIComponent(_referer));return false;}
if(isForceReload==1){if(!sId&&isWX){return false;}
if(!sId&&isMQ){jq.DIC.showLoading(null,null,true);jq.DIC.reload('/my/sites');return false;}
jq.DIC.showLoading(null,null,true);jq.DIC.reload('/'+sId);}else{if(document.referrer){history.go(-1);}else{if(!sId){return false;}
if(isMQ){jq.DIC.showLoading(null,null,true);jq.DIC.reload('/portal');return false;}
jq.DIC.showLoading(null,null,true);jq.DIC.reload('/'+sId);}}});jq('.topBar').on('click','.qPublish',function(event){var $this=jq(this);if(jq.DIC.getQuery('filterType')){var filerType=jq.DIC.getQuery('filterType');if($this.hasClass('qPublish')){event.preventDefault();window.location.href=$this.attr('href')+'?filterType='+filerType;};}});jq('#qPublish').on('click',function(){var sId=jq(this).attr('sId');if(!sId)return;var url=jq(this).attr('href');if(jq.DIC.getQuery('filterType')){var filerType=jq.DIC.getQuery('filterType');url=url+'?filterType='+filerType;}
jq.DIC.reload(url);return false;});}};module.exports.init();});;define('module/forumdisplay',['lib/scroll','module/thread','module/followSite','module/wxFollow'],function(require,exports,module){"require:nomunge,exports:nomunge,module:nomunge";var libScroll=require('lib/scroll');var thread=require('module/thread');var followSite=require('module/followSite');var wxFollow=require('module/wxFollow');var stat=require('lib/stat');module.exports={popTId:0,isLoadingNew:true,isLoading:false,getThreadList:function(action,nextStart){var start=0;if(typeof nextStart=='undefined'){start=window.nextStart;}
var query='';if(window.location.search.indexOf('?')!==-1){query=window.location.search.replace(/\?/g,'&').replace(/&?start=[^d]+/g,'');}
module.exports.isLoading=true;var url='/'+sId+'?start='+start+query;if(isLive){var url='/'+sId+'?start='+start+'&live=1'+query;}
var opts={'beforeSend':function(){switch(action){case'pull':jq('#showAll').hide();module.exports.isLoadingNew=true;break;case'drag':module.exports.isLoadingNew=true;break;default:jq.DIC.showLoading();}
module.exports.isLoadingNew=true;},'complete':function(){jq('#waitForLoad').hide();jq('#refreshWait').slideUp();jq('#loadNext').slideUp();},'success':function(re){var status=parseInt(re.errCode);if(status!==0){module.exports.isLoading=false;return false;}
re.data.isLive=isLive||0;re.data.uId=uId||0;re.data.isFriendSite=isFriendSite||0;re.data.tlNodeId='tl_'+(new Date).getTime();re.data.isWX=isWX;var allThreadListObj=jq('#threadList');var zero=new Date;if(action=='pull'){allThreadListObj.html('');var tmpl=template.render('tmpl_thread',re.data);allThreadListObj.html(tmpl);}else{var tmpl=template.render('tmpl_thread',re.data);if(tmpl=='{Template Error}'){tmpl='';}
jq('.infobox').hide();allThreadListObj.append(tmpl);if(jq.DIC.isObjectEmpty(re.data.threadList)){module.exports.isLoadingNew=false;jq('#loadNext').stop(true,true).hide();jq('#showAll').show();}}
stat.reportPoint('listRender',10,new Date,zero);window.nextStart=nextStart=re.data.nextStart;if(re.data.newMsgCount>0){newMsgCount=re.data.newMsgCount;if(re.data.newMsgCount>99){jq('#navMsgNum').html('').addClass('redP');}else{jq('#navMsgNum').removeClass('redP').html(re.data.newMsgCount);}
var date=new Date();localStorage.setItem('lastNewTime',date.getTime());jq('#navMsgNum').show();}else{jq('#newMsgCount').html(0);jq('#navMsgNum').hide();}
if(re.data.threadCount>=0){jq('#threadCount').html(re.data.threadCount);}
if(re.data.sitePV>=1){jq('#sitePV').html(re.data.sitePV);}
thread.initScrollImage('#'+re.data.tlNodeId);module.exports.isLoading=false;jq('#refreshWait').hide();},error:function(){module.exports.isLoading=false;}};jq.DIC.ajax(url,'',opts);},showCarousel:function(obj,o){o=jq.extend({btnPrev:null,btnNext:null,btnGo:null,mouseWheel:false,auto:null,speed:200,easing:null,vertical:false,circular:true,visible:3,start:0,scroll:1,beforeStart:null,afterEnd:null},o||{});return obj.each(function(){var running=false,animCss=o.vertical?"top":"left",sizeCss=o.vertical?"height":"width";var div=obj,ul=jq(o.childUl,div),tLi=jq(o.childLi,ul),tl=tLi.length,v=o.visible;if(o.circular){ul.prepend(tLi.slice(tl-v-1+1).clone()).append(tLi.slice(0,v).clone());o.start+=v;}
var li=jq(o.childLi,ul),itemLength=li.length,curr=o.start;div.css("visibility","visible");li.css({width:obj.width(),height:'auto'});li.css({overflow:"hidden",float:o.vertical?"none":"left"});ul.css({margin:"0",padding:"0",position:"relative","list-style-type":"none","z-index":"1"});div.css({overflow:"hidden",position:"relative","z-index":"2",left:"0px"});var liSize=o.vertical?height(li):width(li);var ulSize=liSize*itemLength;var divSize=liSize*v;ul.css(sizeCss,ulSize+"px").css(animCss,-(curr*liSize));div.css(sizeCss,divSize+"px");if(o.btnPrev)
jq(o.btnPrev).click(function(){return go(curr-o.scroll);});if(o.btnNext)
jq(document).on('click',o.btnNext,function(){return go(curr+o.scroll);});if(o.btnGo)
jq.each(o.btnGo,function(i,val){jq(val).click(function(){return go(o.circular?o.visible+i:i);});});if(o.mouseWheel&&div.mousewheel)
div.mousewheel(function(e,d){return d>0?go(curr-o.scroll):go(curr+o.scroll);});if(o.auto)
setInterval(function(){go(curr+o.scroll);},o.auto+o.speed);function vis(){return li.slice(curr).slice(0,v);};function go(to){if(!running){if(o.beforeStart)
o.beforeStart.call(this,vis());if(o.circular){if(to<=o.start-v-1){ul.css(animCss,-((itemLength-(v*2))*liSize)+"px");curr=to==o.start-v-1?itemLength-(v*2)-1:itemLength-(v*2)-o.scroll;}else if(to>=itemLength-v+1){ul.css(animCss,-((v)*liSize)+"px");curr=to==itemLength-v+1?v+1:v+o.scroll;}else curr=to;}else{if(to<0||to>itemLength-v)return;else curr=to;}
running=true;ul.animate(animCss=="left"?{left:-(curr*liSize)}:{top:-(curr*liSize)},o.speed,o.easing,function(){if(o.afterEnd)
o.afterEnd.call(this,vis());running=false;});if(!o.circular){jq(o.btnPrev+","+o.btnNext).removeClass("disabled");jq((curr-o.scroll<0&&o.btnPrev)||(curr+o.scroll>itemLength-v&&o.btnNext)||[]).addClass("disabled");}}
return false;};});function css(el,prop){return parseInt(jq.css(el[0],prop))||0;};function width(el){return el[0].offsetWidth+css(el,'marginLeft')+css(el,'marginRight');};function height(el){return el[0].offsetHeight+css(el,'marginTop')+css(el,'marginBottom');};},init:function(){if(window.isBindQQ){jQuery.DIC.dialog({content:'绑定成功',autoClose:true});}
var source=jq.DIC.getQuery('source');if(source=='follow_qrcode'&&!isWX&&!window.isLiked){followSite.followSite.call({},'nothing',{'sId':sId});}
var jsonData=parseJSON(window.jsonData);jsonData.showEmptyTip=true;jsonData.isWX=isWX;var tmpl=template.render('tmpl_thread',jsonData);var allThreadListObj=jq('#threadList');if(tmpl=='{Template Error}'){jq('#threadList').find('.infobox i').removeClass('iconSuccess').addClass('iconPrompt');jq('#threadList').find('.infobox p').html('好像出了点问题，请联系管理员');}else{allThreadListObj.html(tmpl);}
g_ts.first_render_end=new Date();thread.initScrollImage();libScroll.initScroll({ulSelector:'.slideBox ul',isFlip:false,cSelector:'.container'});initLazyload('.warp img');require.async('module/imageviewCommon',function(imageviewCommon){imageviewCommon.init('.slideBox li');});if(jq('#showCarousel .sCLi')[0]){jq('#showCarousel .sCLi').show();module.exports.showCarousel(jq('#showCarousel'),{afterEnd:function(e){var isInitlazyload=jq(e).data('initlzl')||false;if(!isInitlazyload){jq(e).data('initlzl',true);initLazyload('#showCarousel img',{checkDuplicates:false});}},btnNext:'.sCNext',childUl:'.sCUl',childLi:'.sCLi',circular:true,scroll:1,visible:1});jq('#showCarousel').on('click','.customImg, .customNotice li, .topicSelection li, .cuTopicImg a',function(){var link=jq(this).attr('data-link')||'';if(link){jq.DIC.open(link);}
return false;});}
var query='';if(window.location.search.indexOf('?')!==-1){query=window.location.search.replace(/\?/g,'&');}
var level=/Android 4.0/.test(window.navigator.userAgent)?-10:-100;if(/MQQBrowser/.test(window.navigator.userAgent)){level=-10;}
var loadingObj=jq('#loadNext');var loadingPos=jq('#loadNextPos');var x,y,endX,endY,offsetY,loadingAction;jq('.warp').on('touchstart',function(e){x=endX=e.originalEvent.touches[0].pageX;y=endY=e.originalEvent.touches[0].pageY;}).on('touchmove',function(e){endX=e.originalEvent.touches[0].pageX;endY=e.originalEvent.touches[0].pageY;offsetY=endY-y;if(offsetY>10&&!module.exports.isLoading&&document.body.scrollTop<=1){module.exports.isLoading=true;jq('#refreshWait').stop(true,true).show();module.exports.getThreadList('pull',0);}});var scrollPosition=jq(window).scrollTop();jq(window).scroll(function(){if(scrollPosition<jq(window).scrollTop()){if(!module.exports.isLoading&&module.exports.isLoadingNew){var loadingObjTop=loadingPos.offset().top-document.body.scrollTop-window.screen.availHeight;if(loadingObjTop<=100){module.exports.isLoading=true;jq('#loadNext').stop(true,true).slideDown('fast');module.exports.getThreadList('drag');}}}
scrollPosition=jq(window).scrollTop();});jq('.warp').on('click','.topicBox',function(e){var that=jq(this);var tId=that.attr('tId')||0;var link=that.attr('data-link')||'';if(tId){jq.DIC.open('/'+sId+'/t/'+tId);}else if(link){jq.DIC.open(link);}
return false;}).on('click','.avatar',function(e){e.stopPropagation(e);var uId=jq(this).attr('uId')||0;if(uId){jq.DIC.open('/profile/'+uId);}
return false;}).on('click','.topBtn, .showBtn',function(e){e.stopPropagation(e);var link=jq(this).attr('data-link')||'';if(link){jq.DIC.open(link);}
return false;}).on('click','.videoArea',function(e){e.stopPropagation(e);var that=jq(this);var tId=that.attr('tId')||0;if(tId){jq.DIC.open('/'+sId+'/t/'+tId+'?video=1');}
return false;}).on('click','.operation',function(e){e.stopPropagation(e);return false;}).on('click','.sourceApp a',function(e){e.stopPropagation(e);var that=jq(this);jq.DIC.touchStateNow(that);var appId=that.attr('appId')||0;if(appId){jq.DIC.open('http://m.wsq.qq.com/app?sId='+sId+'&appId='+appId);}
return false;}).on('click','.like',function(e){e.stopPropagation(e);var that=jq(this);jq.DIC.touchStateNow(that);var reapp=/qqdownloader\/([^\s]+)/i;if(authUrl&&reapp.test(navigator.userAgent)){return false;}
if(authUrl){jq.DIC.reload(authUrl);return false;}
var likeObj=that.find('i');if(likeObj.hasClass('iconPraise')){return false;}
var tId=that.parent().attr('tId');var opts={'success':function(re){var status=parseInt(re.errCode);if(status!==0||!re.data||!re.data.likeNumber){return false;}
likeObj.removeClass('iconNoPraise').addClass('iconPraise');that.find('.likeNum').html(re.data.likeNumber);if(window.isFriendSite){jq('#t_'+tId+'_0_0').find('.blur').removeClass();jq('#t_'+tId+'_0_0').find('.slideText').css('display','none');}
if(isWX&&isWeixinLink&&jq.DIC.getQuery('source')){wxFollow.wxFollowTips();}},'noShowLoading':true,'noMsg':true}
jq.DIC.ajax('/'+sId+'/like',{'tId':tId,'CSRFToken':CSRFToken},opts);return false;}).on('click','.threadReply',function(e){e.stopPropagation(e);var that=jq(this);jq.DIC.touchStateNow(that);var tId=that.parent().attr('tId');var nodeId='t_'+tId+'_0_0';thread.reply(sId,tId,0,0,0,'',false,nodeId,true);return false;}).on('click','.moreReply',function(){try{pgvSendClick({hottag:'QUAN.SITE.LIST.ALL'});}catch(e){}}).on('click','.attendPic',function(e){e.stopPropagation(e);var that=jq(this);jq.DIC.touchStateNow(that);var parentId=that.attr('parentId')||0;jq.DIC.reload('/'+sId+'/t/new?parentId='+parentId);return false;}).on('click','.evtShowAllPic',function(e){e.stopPropagation(e);var that=jq(this),showAll=that.attr('showAll')?1:0,tId=that.attr('tId')||0;if(tId){jq.DIC.open('/'+sId+'/v/'+tId+'?showAll='+showAll);}
return false;}).on('click','header .evtSiteRank',function(){var link=jq(this).attr('data-link')||'';if(link){jq.DIC.open(link);}}).on('click','header .logo',function(){if(sId){jq.DIC.open('/'+sId);}
return false;});thread.publicEvent();thread.initPopBtn();jq('.inviteQQ').on('click',function(){if(inviteUrl.length>1){inviteUrl=inviteUrl.replace(/&amp;/g,'&');jq.DIC.reload(inviteUrl);}
return false;});}};module.exports.init();});;define('module/common',['module/followSite'],function(require,exports,module){"require:nomunge,exports:nomunge,module:nomunge";require('lib/fastclick');var followSite=require('module/followSite');module.exports={showSide:function(data){data.sId=sId;data.isLiked=window.isLiked;var tmpl=template.render('tmpl_sideBar',data);jq.DIC.dialog({content:tmpl,id:'sideBar',isMask:true,isHtml:true,callback:function(){jq('#fwin_dialog_sideBar').css({top:'0px',left:'',height:'100%',width:'190px',right:'0px'});var sideBar=jq('.sideBar');if(!sideBar.is(':visible')){sideBar.show();}
jq('#fwin_mask_sideBar').on('click',function(){jq.DIC.dialog({id:'sideBar'});});jq('#sideBarCon').on('click','.filter',function(){jq.DIC.showLoading();thisObj=jq(this);setTimeout(function(){var labelId=thisObj.attr('labelid')||'';var url='/'+sId;if(labelId){url+='?filterType='+labelId;}
jq.DIC.reload(url);},10);});module.exports.showCustomTag(data.filterType);jq('#sideProfile').on('click',function(){var url='/profile/'+uId;if(isWX&&sId){url+='?sId='+sId;}
jq.DIC.open(url);return false;});jq('#sideUnfollow').on('click',function(){var thisObj=jq(this);followSite.unfollowSite.call(thisObj,'site_index');return false;});setTimeout(function(){document.ontouchmove=function(e){e.preventDefault();};},10);}});},showCustomTag:function(filterType){if(jq.isEmptyObject(module.exports.labelData)){var url='/'+sId+'/label';var opts={'beforeSend':function(){jq('#customTagWait').show();},'complete':function(){jq('#customTagWait').hide();},'success':function(re){var status=parseInt(re.errCode);if(status!=0){return false;}
re.data.filterType=filterType;module.exports.labelData=re.data
var tmpl=template.render('tmpl_customTag',module.exports.labelData);jq('#customTag').html(tmpl);},'noShowLoading':true,'noMsg':true};jq.DIC.ajax(url,'',opts);}else{jq('#customTagWait').hide();var tmpl=template.render('tmpl_customTag',module.exports.labelData);jq('#customTag').html(tmpl);}},labelData:{},init:function(){if(!jq.DIC.in_array('module/myMsg',window.g_module)){localStorage.removeItem('seeMsgTime');}
setInterval(function(){if(window.pageYOffset>500&&!window.isNoShowToTop){jq('#goTop').show();}else{jq('#goTop').hide();}
var lastNewTime=localStorage.getItem('lastNewTime');var seeMsgTime=localStorage.getItem('seeMsgTime');if(seeMsgTime>lastNewTime){window.newMsgCount=0;jq('#newMsgCount').html(0);jq('#navMsgNum').hide();jq('#sideMsgNum').hide();jq('.topicRank .numP').hide();}},200);jq('.upBtn').on('click',function(){jq('#goTop').hide();scroll(0,0);});if(isNullNick){jq.DIC.dialog({content:'对不起，暂不支持纯表情昵称登录，请调整微信昵称后登录',autoClose:false});}
jq('#mqOption').on('click',function(){var thisObj=jq(this);var isSite=thisObj.attr('isSite')||2;if(isSite==1){var filterType=jq.DIC.getQuery('filterType');filterType=filterType=='undefined'?'':filterType;var data={'filterType':filterType,'newMsgCount':newMsgCount};module.exports.showSide(data);if(window.newMsgCount>0){jq('#sideMsgNum').html(window.newMsgCount).show();}}else{jq.DIC.open('/profile/'+uId);}
return false;});jq.DIC.touchState('#mqOption');}};module.exports.init();});;define('module/share',[],function(require,exports,module){"require:nomunge,exports:nomunge,module:nomunge";module.exports={init:function(){var shareDesc=window.shareDesc;var shareTitle=window.shareTitle||jq(document).attr('title');var shareImgUrl=window.shareImgUrl||window.siteLogo;if(window.tId>0&&window.parentId<1){var content=jq.DIC.trim(jq('.dCon').text())||jq.DIC.trim(jq('.detailShow').text());if(content){shareDesc=content.substr(0,content.indexOf('。')+1);if(jq.DIC.mb_strlen(shareDesc)<60||jq.DIC.mb_strlen(shareDesc)>105){shareDesc=jq.DIC.mb_cutstr(content,105);}}
var imgObj=jq('.dImg');if(imgObj){shareImgUrl=jq('.dImg').first().attr('data-original')||jq('.dImg').first().attr('src');;}}
if(isWX){require.async('module/wxshare',function(wxshare){wxshare.initWXShare({'sId':sId,'tId':tId,'img':shareImgUrl,'desc':shareDesc,'title':shareTitle,'callback':function(re){}});});}else if(isMQ){if(typeof(mqq.data.setShareInfo)!='undefined'){mqq.data.setShareInfo({'share_url':window.shareUrl,'title':shareTitle,'desc':shareDesc,'image_url':shareImgUrl},function(){});}}
jq('.warp, #bottomBar').on('click','.shareBtn',function(e){e.stopPropagation(e);var that=jq(this);module.exports.shareBind.call(that);return false;});},shareBind:function(){var that=jq(this);var MQQBrowser=navigator.userAgent.match(/MQQBrowser\/([^\s]+)/);if(!isMQ&&!isWX&&MQQBrowser&&MQQBrowser[1]>='5.2'){require.async('lib/QQBrowser',function(qb){if(typeof window.x5!=='undefined'){window.x5.getAppShowType(function(re){var shareUrl=that.attr('sUrl')||window.shareUrl,shareTitle=that.attr('sTitle')||window.shareTitle;shareDesc=that.attr('sDesc')||window.shareDesc,shareImgUrl=that.attr('sImg')||window.shareImgUrl;window.x5.share({'url':shareUrl,'title':shareTitle,'description':shareDesc,'img_url':shareImgUrl,'img_title':''},'','');return false;},'');}});return false;}
module.exports.shareJump.call(that);},shareJump:function(){var that=jq(this);if(!isMQ&&!isWX){var qqShareLink=that.attr('_qq');var qzoneShareLink=that.attr('_qzone');if(qqShareLink||qzoneShareLink){if(qqShareLink&&qzoneShareLink){var html=template.render('tmpl_share',{qqShareLink:qqShareLink,qzoneShareLink:qzoneShareLink});jq.DIC.dialog({id:'share',content:html,isHtml:true,isMask:true,callback:function(){jq('#fwin_mask_share, #cancleShare, .shareLayer a').on('click',function(){jq.DIC.dialog({id:'share'});});}});}else{var jumpUrl=qqShareLink||qzoneShareLink;jq.DIC.reload(jumpUrl);}
return false;}}
var link=that.attr('data-link')||'';if(link){jq.DIC.reload(link);return false;}else{var tmpl=template.render('tmpl_pageTip',{'msg':'喜欢这个页面，请点击右上角图标分享'});jq.DIC.dialog({id:'shareMask',top:0,content:tmpl,isHtml:true,isMask:true,callback:function(){jq('.g-mask').on('click',function(){jq.DIC.dialog({id:'shareMask'});jq('#showShare').hide();return false;});}});jq('#showShare').show();scroll(0,0);}
return false;}};module.exports.init();});