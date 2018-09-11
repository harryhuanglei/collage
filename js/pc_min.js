(function(){var n=this,t=n._,r={},e=Array.prototype,u=Object.prototype,i=Function.prototype,a=e.push,o=e.slice,c=e.concat,l=u.toString,f=u.hasOwnProperty,s=e.forEach,p=e.map,h=e.reduce,v=e.reduceRight,d=e.filter,g=e.every,m=e.some,y=e.indexOf,b=e.lastIndexOf,x=Array.isArray,_=Object.keys,j=i.bind,w=function(n){return n instanceof w?n:this instanceof w?(this._wrapped=n,void 0):new w(n)};"undefined"!=typeof exports?("undefined"!=typeof module&&module.exports&&(exports=module.exports=w),exports._=w):n._=w,w.VERSION="1.4.4";var A=w.each=w.forEach=function(n,t,e){if(null!=n)if(s&&n.forEach===s)n.forEach(t,e);else if(n.length===+n.length){for(var u=0,i=n.length;i>u;u++)if(t.call(e,n[u],u,n)===r)return}else for(var a in n)if(w.has(n,a)&&t.call(e,n[a],a,n)===r)return};w.map=w.collect=function(n,t,r){var e=[];return null==n?e:p&&n.map===p?n.map(t,r):(A(n,function(n,u,i){e[e.length]=t.call(r,n,u,i)}),e)};var O="Reduce of empty array with no initial value";w.reduce=w.foldl=w.inject=function(n,t,r,e){var u=arguments.length>2;if(null==n&&(n=[]),h&&n.reduce===h)return e&&(t=w.bind(t,e)),u?n.reduce(t,r):n.reduce(t);if(A(n,function(n,i,a){u?r=t.call(e,r,n,i,a):(r=n,u=!0)}),!u)throw new TypeError(O);return r},w.reduceRight=w.foldr=function(n,t,r,e){var u=arguments.length>2;if(null==n&&(n=[]),v&&n.reduceRight===v)return e&&(t=w.bind(t,e)),u?n.reduceRight(t,r):n.reduceRight(t);var i=n.length;if(i!==+i){var a=w.keys(n);i=a.length}if(A(n,function(o,c,l){c=a?a[--i]:--i,u?r=t.call(e,r,n[c],c,l):(r=n[c],u=!0)}),!u)throw new TypeError(O);return r},w.find=w.detect=function(n,t,r){var e;return E(n,function(n,u,i){return t.call(r,n,u,i)?(e=n,!0):void 0}),e},w.filter=w.select=function(n,t,r){var e=[];return null==n?e:d&&n.filter===d?n.filter(t,r):(A(n,function(n,u,i){t.call(r,n,u,i)&&(e[e.length]=n)}),e)},w.reject=function(n,t,r){return w.filter(n,function(n,e,u){return!t.call(r,n,e,u)},r)},w.every=w.all=function(n,t,e){t||(t=w.identity);var u=!0;return null==n?u:g&&n.every===g?n.every(t,e):(A(n,function(n,i,a){return(u=u&&t.call(e,n,i,a))?void 0:r}),!!u)};var E=w.some=w.any=function(n,t,e){t||(t=w.identity);var u=!1;return null==n?u:m&&n.some===m?n.some(t,e):(A(n,function(n,i,a){return u||(u=t.call(e,n,i,a))?r:void 0}),!!u)};w.contains=w.include=function(n,t){return null==n?!1:y&&n.indexOf===y?n.indexOf(t)!=-1:E(n,function(n){return n===t})},w.invoke=function(n,t){var r=o.call(arguments,2),e=w.isFunction(t);return w.map(n,function(n){return(e?t:n[t]).apply(n,r)})},w.pluck=function(n,t){return w.map(n,function(n){return n[t]})},w.where=function(n,t,r){return w.isEmpty(t)?r?null:[]:w[r?"find":"filter"](n,function(n){for(var r in t)if(t[r]!==n[r])return!1;return!0})},w.findWhere=function(n,t){return w.where(n,t,!0)},w.max=function(n,t,r){if(!t&&w.isArray(n)&&n[0]===+n[0]&&65535>n.length)return Math.max.apply(Math,n);if(!t&&w.isEmpty(n))return-1/0;var e={computed:-1/0,value:-1/0};return A(n,function(n,u,i){var a=t?t.call(r,n,u,i):n;a>=e.computed&&(e={value:n,computed:a})}),e.value},w.min=function(n,t,r){if(!t&&w.isArray(n)&&n[0]===+n[0]&&65535>n.length)return Math.min.apply(Math,n);if(!t&&w.isEmpty(n))return 1/0;var e={computed:1/0,value:1/0};return A(n,function(n,u,i){var a=t?t.call(r,n,u,i):n;e.computed>a&&(e={value:n,computed:a})}),e.value},w.shuffle=function(n){var t,r=0,e=[];return A(n,function(n){t=w.random(r++),e[r-1]=e[t],e[t]=n}),e};var k=function(n){return w.isFunction(n)?n:function(t){return t[n]}};w.sortBy=function(n,t,r){var e=k(t);return w.pluck(w.map(n,function(n,t,u){return{value:n,index:t,criteria:e.call(r,n,t,u)}}).sort(function(n,t){var r=n.criteria,e=t.criteria;if(r!==e){if(r>e||r===void 0)return 1;if(e>r||e===void 0)return-1}return n.index<t.index?-1:1}),"value")};var F=function(n,t,r,e){var u={},i=k(t||w.identity);return A(n,function(t,a){var o=i.call(r,t,a,n);e(u,o,t)}),u};w.groupBy=function(n,t,r){return F(n,t,r,function(n,t,r){(w.has(n,t)?n[t]:n[t]=[]).push(r)})},w.countBy=function(n,t,r){return F(n,t,r,function(n,t){w.has(n,t)||(n[t]=0),n[t]++})},w.sortedIndex=function(n,t,r,e){r=null==r?w.identity:k(r);for(var u=r.call(e,t),i=0,a=n.length;a>i;){var o=i+a>>>1;u>r.call(e,n[o])?i=o+1:a=o}return i},w.toArray=function(n){return n?w.isArray(n)?o.call(n):n.length===+n.length?w.map(n,w.identity):w.values(n):[]},w.size=function(n){return null==n?0:n.length===+n.length?n.length:w.keys(n).length},w.first=w.head=w.take=function(n,t,r){return null==n?void 0:null==t||r?n[0]:o.call(n,0,t)},w.initial=function(n,t,r){return o.call(n,0,n.length-(null==t||r?1:t))},w.last=function(n,t,r){return null==n?void 0:null==t||r?n[n.length-1]:o.call(n,Math.max(n.length-t,0))},w.rest=w.tail=w.drop=function(n,t,r){return o.call(n,null==t||r?1:t)},w.compact=function(n){return w.filter(n,w.identity)};var R=function(n,t,r){return A(n,function(n){w.isArray(n)?t?a.apply(r,n):R(n,t,r):r.push(n)}),r};w.flatten=function(n,t){return R(n,t,[])},w.without=function(n){return w.difference(n,o.call(arguments,1))},w.uniq=w.unique=function(n,t,r,e){w.isFunction(t)&&(e=r,r=t,t=!1);var u=r?w.map(n,r,e):n,i=[],a=[];return A(u,function(r,e){(t?e&&a[a.length-1]===r:w.contains(a,r))||(a.push(r),i.push(n[e]))}),i},w.union=function(){return w.uniq(c.apply(e,arguments))},w.intersection=function(n){var t=o.call(arguments,1);return w.filter(w.uniq(n),function(n){return w.every(t,function(t){return w.indexOf(t,n)>=0})})},w.difference=function(n){var t=c.apply(e,o.call(arguments,1));return w.filter(n,function(n){return!w.contains(t,n)})},w.zip=function(){for(var n=o.call(arguments),t=w.max(w.pluck(n,"length")),r=Array(t),e=0;t>e;e++)r[e]=w.pluck(n,""+e);return r},w.object=function(n,t){if(null==n)return{};for(var r={},e=0,u=n.length;u>e;e++)t?r[n[e]]=t[e]:r[n[e][0]]=n[e][1];return r},w.indexOf=function(n,t,r){if(null==n)return-1;var e=0,u=n.length;if(r){if("number"!=typeof r)return e=w.sortedIndex(n,t),n[e]===t?e:-1;e=0>r?Math.max(0,u+r):r}if(y&&n.indexOf===y)return n.indexOf(t,r);for(;u>e;e++)if(n[e]===t)return e;return-1},w.lastIndexOf=function(n,t,r){if(null==n)return-1;var e=null!=r;if(b&&n.lastIndexOf===b)return e?n.lastIndexOf(t,r):n.lastIndexOf(t);for(var u=e?r:n.length;u--;)if(n[u]===t)return u;return-1},w.range=function(n,t,r){1>=arguments.length&&(t=n||0,n=0),r=arguments[2]||1;for(var e=Math.max(Math.ceil((t-n)/r),0),u=0,i=Array(e);e>u;)i[u++]=n,n+=r;return i},w.bind=function(n,t){if(n.bind===j&&j)return j.apply(n,o.call(arguments,1));var r=o.call(arguments,2);return function(){return n.apply(t,r.concat(o.call(arguments)))}},w.partial=function(n){var t=o.call(arguments,1);return function(){return n.apply(this,t.concat(o.call(arguments)))}},w.bindAll=function(n){var t=o.call(arguments,1);return 0===t.length&&(t=w.functions(n)),A(t,function(t){n[t]=w.bind(n[t],n)}),n},w.memoize=function(n,t){var r={};return t||(t=w.identity),function(){var e=t.apply(this,arguments);return w.has(r,e)?r[e]:r[e]=n.apply(this,arguments)}},w.delay=function(n,t){var r=o.call(arguments,2);return setTimeout(function(){return n.apply(null,r)},t)},w.defer=function(n){return w.delay.apply(w,[n,1].concat(o.call(arguments,1)))},w.throttle=function(n,t){var r,e,u,i,a=0,o=function(){a=new Date,u=null,i=n.apply(r,e)};return function(){var c=new Date,l=t-(c-a);return r=this,e=arguments,0>=l?(clearTimeout(u),u=null,a=c,i=n.apply(r,e)):u||(u=setTimeout(o,l)),i}},w.debounce=function(n,t,r){var e,u;return function(){var i=this,a=arguments,o=function(){e=null,r||(u=n.apply(i,a))},c=r&&!e;return clearTimeout(e),e=setTimeout(o,t),c&&(u=n.apply(i,a)),u}},w.once=function(n){var t,r=!1;return function(){return r?t:(r=!0,t=n.apply(this,arguments),n=null,t)}},w.wrap=function(n,t){return function(){var r=[n];return a.apply(r,arguments),t.apply(this,r)}},w.compose=function(){var n=arguments;return function(){for(var t=arguments,r=n.length-1;r>=0;r--)t=[n[r].apply(this,t)];return t[0]}},w.after=function(n,t){return 0>=n?t():function(){return 1>--n?t.apply(this,arguments):void 0}},w.keys=_||function(n){if(n!==Object(n))throw new TypeError("Invalid object");var t=[];for(var r in n)w.has(n,r)&&(t[t.length]=r);return t},w.values=function(n){var t=[];for(var r in n)w.has(n,r)&&t.push(n[r]);return t},w.pairs=function(n){var t=[];for(var r in n)w.has(n,r)&&t.push([r,n[r]]);return t},w.invert=function(n){var t={};for(var r in n)w.has(n,r)&&(t[n[r]]=r);return t},w.functions=w.methods=function(n){var t=[];for(var r in n)w.isFunction(n[r])&&t.push(r);return t.sort()},w.extend=function(n){return A(o.call(arguments,1),function(t){if(t)for(var r in t)n[r]=t[r]}),n},w.pick=function(n){var t={},r=c.apply(e,o.call(arguments,1));return A(r,function(r){r in n&&(t[r]=n[r])}),t},w.omit=function(n){var t={},r=c.apply(e,o.call(arguments,1));for(var u in n)w.contains(r,u)||(t[u]=n[u]);return t},w.defaults=function(n){return A(o.call(arguments,1),function(t){if(t)for(var r in t)null==n[r]&&(n[r]=t[r])}),n},w.clone=function(n){return w.isObject(n)?w.isArray(n)?n.slice():w.extend({},n):n},w.tap=function(n,t){return t(n),n};var I=function(n,t,r,e){if(n===t)return 0!==n||1/n==1/t;if(null==n||null==t)return n===t;n instanceof w&&(n=n._wrapped),t instanceof w&&(t=t._wrapped);var u=l.call(n);if(u!=l.call(t))return!1;switch(u){case"[object String]":return n==t+"";case"[object Number]":return n!=+n?t!=+t:0==n?1/n==1/t:n==+t;case"[object Date]":case"[object Boolean]":return+n==+t;case"[object RegExp]":return n.source==t.source&&n.global==t.global&&n.multiline==t.multiline&&n.ignoreCase==t.ignoreCase}if("object"!=typeof n||"object"!=typeof t)return!1;for(var i=r.length;i--;)if(r[i]==n)return e[i]==t;r.push(n),e.push(t);var a=0,o=!0;if("[object Array]"==u){if(a=n.length,o=a==t.length)for(;a--&&(o=I(n[a],t[a],r,e)););}else{var c=n.constructor,f=t.constructor;if(c!==f&&!(w.isFunction(c)&&c instanceof c&&w.isFunction(f)&&f instanceof f))return!1;for(var s in n)if(w.has(n,s)&&(a++,!(o=w.has(t,s)&&I(n[s],t[s],r,e))))break;if(o){for(s in t)if(w.has(t,s)&&!a--)break;o=!a}}return r.pop(),e.pop(),o};w.isEqual=function(n,t){return I(n,t,[],[])},w.isEmpty=function(n){if(null==n)return!0;if(w.isArray(n)||w.isString(n))return 0===n.length;for(var t in n)if(w.has(n,t))return!1;return!0},w.isElement=function(n){return!(!n||1!==n.nodeType)},w.isArray=x||function(n){return"[object Array]"==l.call(n)},w.isObject=function(n){return n===Object(n)},A(["Arguments","Function","String","Number","Date","RegExp"],function(n){w["is"+n]=function(t){return l.call(t)=="[object "+n+"]"}}),w.isArguments(arguments)||(w.isArguments=function(n){return!(!n||!w.has(n,"callee"))}),"function"!=typeof/./&&(w.isFunction=function(n){return"function"==typeof n}),w.isFinite=function(n){return isFinite(n)&&!isNaN(parseFloat(n))},w.isNaN=function(n){return w.isNumber(n)&&n!=+n},w.isBoolean=function(n){return n===!0||n===!1||"[object Boolean]"==l.call(n)},w.isNull=function(n){return null===n},w.isUndefined=function(n){return n===void 0},w.has=function(n,t){return f.call(n,t)},w.noConflict=function(){return n._=t,this},w.identity=function(n){return n},w.times=function(n,t,r){for(var e=Array(n),u=0;n>u;u++)e[u]=t.call(r,u);return e},w.random=function(n,t){return null==t&&(t=n,n=0),n+Math.floor(Math.random()*(t-n+1))};var M={escape:{"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#x27;","/":"&#x2F;"}};M.unescape=w.invert(M.escape);var S={escape:RegExp("["+w.keys(M.escape).join("")+"]","g"),unescape:RegExp("("+w.keys(M.unescape).join("|")+")","g")};w.each(["escape","unescape"],function(n){w[n]=function(t){return null==t?"":(""+t).replace(S[n],function(t){return M[n][t]})}}),w.result=function(n,t){if(null==n)return null;var r=n[t];return w.isFunction(r)?r.call(n):r},w.mixin=function(n){A(w.functions(n),function(t){var r=w[t]=n[t];w.prototype[t]=function(){var n=[this._wrapped];return a.apply(n,arguments),D.call(this,r.apply(w,n))}})};var N=0;w.uniqueId=function(n){var t=++N+"";return n?n+t:t},w.templateSettings={evaluate:/<%([\s\S]+?)%>/g,interpolate:/<%=([\s\S]+?)%>/g,escape:/<%-([\s\S]+?)%>/g};var T=/(.)^/,q={"'":"'","\\":"\\","\r":"r","\n":"n","	":"t","\u2028":"u2028","\u2029":"u2029"},B=/\\|'|\r|\n|\t|\u2028|\u2029/g;w.template=function(n,t,r){var e;r=w.defaults({},r,w.templateSettings);var u=RegExp([(r.escape||T).source,(r.interpolate||T).source,(r.evaluate||T).source].join("|")+"|$","g"),i=0,a="__p+='";n.replace(u,function(t,r,e,u,o){return a+=n.slice(i,o).replace(B,function(n){return"\\"+q[n]}),r&&(a+="'+\n((__t=("+r+"))==null?'':_.escape(__t))+\n'"),e&&(a+="'+\n((__t=("+e+"))==null?'':__t)+\n'"),u&&(a+="';\n"+u+"\n__p+='"),i=o+t.length,t}),a+="';\n",r.variable||(a="with(obj||{}){\n"+a+"}\n"),a="var __t,__p='',__j=Array.prototype.join,"+"print=function(){__p+=__j.call(arguments,'');};\n"+a+"return __p;\n";try{e=Function(r.variable||"obj","_",a)}catch(o){throw o.source=a,o}if(t)return e(t,w);var c=function(n){return e.call(this,n,w)};return c.source="function("+(r.variable||"obj")+"){\n"+a+"}",c},w.chain=function(n){return w(n).chain()};var D=function(n){return this._chain?w(n).chain():n};w.mixin(w),A(["pop","push","reverse","shift","sort","splice","unshift"],function(n){var t=e[n];w.prototype[n]=function(){var r=this._wrapped;return t.apply(r,arguments),"shift"!=n&&"splice"!=n||0!==r.length||delete r[0],D.call(this,r)}}),A(["concat","join","slice"],function(n){var t=e[n];w.prototype[n]=function(){return D.call(this,t.apply(this._wrapped,arguments))}}),w.extend(w.prototype,{chain:function(){return this._chain=!0,this},value:function(){return this._wrapped}})}).call(this);



















!
function(t, e, i, n) {
    var o = t(e);
    t.fn.lazyload = function(r) {
        function a() {
            var e = 0;
            s.each(function() {
                var i = t(this);
                if (!f.skip_invisible || i.is(":visible")) if (t.abovethetop(this, f) || t.leftofbegin(this, f));
                else if (t.belowthefold(this, f) || t.rightoffold(this, f)) {
                    if (++e > f.failure_limit) return ! 1
                } else i.trigger("appear"),
                e = 0
            })
        }
        var l,
        s = this,
        f = {
            threshold: 0,
            failure_limit: 0,
            event: "scroll",
            effect: "show",
            container: e,
            data_attribute: "original",
            skip_invisible: !0,
            appear: null,
            load: null,
            placeholder: "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC"
        };
        return r && (n !== r.failurelimit && (r.failure_limit = r.failurelimit, delete r.failurelimit), n !== r.effectspeed && (r.effect_speed = r.effectspeed, delete r.effectspeed), t.extend(f, r)),
        l = f.container === n || f.container === e ? o: t(f.container),
        0 === f.event.indexOf("scroll") && l.bind(f.event, 
        function() {
            return a()
        }),
        this.each(function() {
            var e = this,
            i = t(e);
            e.loaded = !1,
            (i.attr("src") === n || i.attr("src") === !1) && i.is("img") && i.attr("src", f.placeholder),
            i.one("appear", 
            function() {
                if (!this.loaded) {
                    if (f.appear) {
                        var n = s.length;
                        f.appear.call(e, n, f)
                    }
                    t("<img />").bind("load", 
                    function() {
                        var n = i.attr("data-" + f.data_attribute);
                        i.hide(),
                        i.is("img") ? i.attr("src", n) : i.css("background-image", "url('" + n + "')"),
                        i[f.effect](f.effect_speed),
                        e.loaded = !0;
                        var o = t.grep(s, 
                        function(t) {
                            return ! t.loaded
                        });
                        if (s = t(o), f.load) {
                            var r = s.length;
                            f.load.call(e, r, f)
                        }
                    }).attr("src", i.attr("data-" + f.data_attribute))
                }
            }),
            0 !== f.event.indexOf("scroll") && i.bind(f.event, 
            function() {
                e.loaded || i.trigger("appear")
            })
        }),
        o.bind("resize", 
        function() {
            a()
        }),
        /(?:iphone|ipod|ipad).*os 5/gi.test(navigator.appVersion) && o.bind("pageshow", 
        function(e) {
            e.originalEvent && e.originalEvent.persisted && s.each(function() {
                t(this).trigger("appear")
            })
        }),
        t(i).ready(function() {
            a()
        }),
        this
    },
    t.belowthefold = function(i, r) {
        var a;
        return a = r.container === n || r.container === e ? (e.innerHeight ? e.innerHeight: o.height()) + o.scrollTop() : t(r.container).offset().top + t(r.container).height(),
        a <= t(i).offset().top - r.threshold
    },
    t.rightoffold = function(i, r) {
        var a;
        return a = r.container === n || r.container === e ? o.width() + o.scrollLeft() : t(r.container).offset().left + t(r.container).width(),
        a <= t(i).offset().left - r.threshold
    },
    t.abovethetop = function(i, r) {
        var a;
        return a = r.container === n || r.container === e ? o.scrollTop() : t(r.container).offset().top,
        a >= t(i).offset().top + r.threshold + t(i).height()
    },
    t.leftofbegin = function(i, r) {
        var a;
        return a = r.container === n || r.container === e ? o.scrollLeft() : t(r.container).offset().left,
        a >= t(i).offset().left + r.threshold + t(i).width()
    },
    t.inviewport = function(e, i) {
        return ! (t.rightoffold(e, i) || t.leftofbegin(e, i) || t.belowthefold(e, i) || t.abovethetop(e, i))
    },
    t.extend(t.expr[":"], {
        "below-the-fold": function(e) {
            return t.belowthefold(e, {
                threshold: 0
            })
        },
        "above-the-top": function(e) {
            return ! t.belowthefold(e, {
                threshold: 0
            })
        },
        "right-of-screen": function(e) {
            return t.rightoffold(e, {
                threshold: 0
            })
        },
        "left-of-screen": function(e) {
            return ! t.rightoffold(e, {
                threshold: 0
            })
        },
        "in-viewport": function(e) {
            return t.inviewport(e, {
                threshold: 0
            })
        },
        "above-the-fold": function(e) {
            return ! t.belowthefold(e, {
                threshold: 0
            })
        },
        "right-of-fold": function(e) {
            return t.rightoffold(e, {
                threshold: 0
            })
        },
        "left-of-fold": function(e) {
            return ! t.rightoffold(e, {
                threshold: 0
            })
        }
    })
} (jQuery, window, document),
function(t, e) {
    "use strict";
    var i = {
        target: !1
    };
    t.fn.autofixed = function(n) {
        var o = t.extend({},
        i, n);
        o.target = "." === o.target[0] ? o.target: "." + o.target;
        var r,
        a = t(this),
        l = a.position(),
        s = o.target,
        f = a.offset();
        if (! (a.length <= 0)) {
            r = o.target === !1 ? a.parent() : t(o.target);
            var h = function(e, i) {
                s = i.target === !1 ? e.parent().offset().top: r.offset().top;
                var n = t(document).scrollTop();
                n > s ? e.addClass("fixed") : s > n && e.removeClass("fixed")
            },
            c = e.throttle(function() {
                h(a, o, l, f)
            },
            10);
            t(window).scroll(c)
        }
    }
} (jQuery, _),
function(t) {
    "use strict";
    t.kdt = t.kdt || {},
    t.extend(t.kdt, {
        spm: function() {
            var e = t.kdt.getParameterByName("spm");
            if (e = t.trim(e), "" !== e) {
                var i = e.split("_");
                i.length > 2 && (e = i[0] + "_" + i[i.length - 1]),
                window._global.spm.logType && window._global.spm.logId && (e += "_" + window._global.spm.logType + window._global.spm.logId)
            } else e = window._global.spm.logType + window._global.spm.logId || "";
            return e
        },
        getParameterByName: function(t) {
            t = t.replace(/[\[]/, "\\[").replace(/[]]/, "\\]");
            var e = "[\\?&]" + t + "=([^&#]*)",
            i = new RegExp(e),
            n = i.exec(window.location.search);
            return null === n ? "": decodeURIComponent(n[1].replace(/\+/g, " "))
        },
        removeParameter: function(t, e) {
            var i = t.split("?");
            if (i.length >= 2) {
                for (var n = encodeURIComponent(e) + "=", o = i[1].split(/[&;]/g), r = o.length; r-->0;) - 1 !== o[r].lastIndexOf(n, 0) && o.splice(r, 1);
                return t = i[0] + "?" + o.join("&")
            }
            return t
        },
        addParameter: function(e, i) {
            if (!e || 0 === e.length) return "";
            var n = e.split("#");
            e = n[0];
            for (var o in i) if (i.hasOwnProperty(o)) {
                if ("" === t.trim(i[o])) continue;
                if (e.indexOf("?") < 0) e += "?" + t.trim(o) + "=" + t.trim(i[o]);
                else {
                    var r = {},
                    a = e.split("?");
                    t.each(a[1].split("&"), 
                    function(e, i) {
                        var n = i.split("=");
                        "" !== t.trim(n[1]) && (r[n[0]] = n[1])
                    }),
                    t.each(i, 
                    function(e, i) {
                        "" !== t.trim(i) && (r[e] = i)
                    });
                    var l = [];
                    t.each(r, 
                    function(t, e) {
                        l.push(t + "=" + e)
                    }),
                    e = a[0] + "?" + l.join("&")
                }
            }
            return 2 === n.length && (e += "#" + n[1]),
            e
        },
        log: function(e, i) {
            if (!e) throw new Error("统计URL不能为空");
            var n = new Image,
            o = Math.floor(2147483648 * Math.random()).toString(36),
            r = "log_" + o;
            window[r] = n,
            n.onload = n.onerror = n.onabort = function() {
                n.onload = n.onerror = n.onabort = null,
                window[r] = null,
                n = null,
                i && _.isFunction(i.resolve) && i.resolve()
            },
            n.src = t.kdt.addParameter(e, {
                time: (new Date).getTime()
            })
        },
        openLink: function(t, e) {
            if (void 0 !== t && null !== t) if (e = e || !1) {
                var i = window.open(t, "_blank");
                i.focus()
            } else location.href = t
        }
    })
} (jQuery),
function(t, e) {
    "use strict";
    function i(e) {
        return this.each(function() {
            var i = t(this),
            o = i.data("kdt.hover");
            o || i.data("kdt.hover", o = new n(this)),
            "string" == typeof e && o[e].call(i)
        })
    }
    var n = function(e, i) {
        this.$target = null,
        this.$element = t(e),
        this.options = t.extend({},
        n.DEFAULTS, i),
        this.init()
    };
    n.DEFAULTS = {
        spacing: 10,
        delay: 200
    },
    n.prototype.init = function() {
        var i = this;
        this.setTargetPosition();
        var o = e.debounce(function() {
            i.setTargetPosition()
        },
        300);
        t(window).resize(o);
        var r,
        a;
        this.$element.on("mouseenter", 
        function() {
            clearTimeout(r),
            clearTimeout(a),
            r = setTimeout(function() {
                i.show.call(i)
            },
            n.DEFAULTS.delay)
        }),
        this.$element.on("mouseleave", 
        function() {
            clearTimeout(r),
            clearTimeout(a),
            a = setTimeout(function() {
                i.hide.call(i)
            },
            n.DEFAULTS.delay)
        }),
        this.getTarget().on("mouseenter", 
        function() {
            clearTimeout(r),
            clearTimeout(a)
        }),
        this.getTarget().on("mouseleave", 
        function() {
            clearTimeout(r),
            a = setTimeout(function() {
                i.hide.call(i)
            },
            n.DEFAULTS.delay)
        }),
        this.$element.data("shake") && this.$element.on("click", 
        function() {
            i.shake()
        })
    },
    n.prototype.show = function() {
        var t = this.getTarget();
        t.show()
    },
    n.prototype.hide = function() {
        var t = this.getTarget();
        t.hide()
    },
    n.prototype.getTarget = function() {
        if (this.$target) return this.$target;
        var e = "." + this.$element.data("target");
        if (!e) throw new Error("selector is not defined");
        return this.$target = t(e)
    },
    n.prototype.getElementSize = function() {
        return {
            width: this.$element.outerWidth(),
            height: this.$element.outerHeight()
        }
    },
    n.prototype.getElementPosition = function() {
        return this.$element.offset()
    },
    n.prototype.getTargetSize = function() {
        var t = this.getTarget();
        return {
            width: t.outerWidth(),
            height: t.outerHeight()
        }
    },
    n.prototype.setTargetPosition = function() {
        var t = this.getPosition(),
        e = this.getAlign();
        if (t) switch (t) {
        case "top":
            this.getTarget().css({
                left:
                this.getElementPosition().left - (this.getTargetSize().width / 2 - this.getElementSize().width / 2),
                top: this.getElementPosition().top - this.getTargetSize().height - this.options.spacing
            });
            break;
        case "bottom":
            "right" == e ? this.getTarget().css({
                left: this.getElementPosition().left - (this.getTargetSize().width - this.getElementSize().width),
                top: this.getElementPosition().top + this.getElementSize().height + this.options.spacing
            }) : this.getTarget().css({
                left: this.getElementPosition().left - (this.getTargetSize().width / 2 - this.getElementSize().width / 2),
                top: this.getElementPosition().top + this.getElementSize().height + this.options.spacing
            })
        }
    },
    n.prototype.getPosition = function() {
        return this.position = this.$element.data("position") || !1,
        this.position
    },
    n.prototype.getAlign = function() {
        return this.align = this.$element.data("align") || !1,
        this.align
    },
    n.prototype.shake = function() {
        var t = this.getTarget();
        t.hasClass("shake") || (t.addClass("animated shake"), setTimeout(function() {
            t.removeClass("animated shake")
        },
        1200))
    },
    n.prototype.destroy = function() {},
    t.fn.hover = i
} (jQuery, _),
function(t) {
    "use strict";
    function e() {
        return this.each(function() {
            var e = t(this),
            n = e.data("kdt.async");
            n || e.data("kdt.async", n = new i(this))
        })
    }
    var i = function(e, n) {
        this.$element = t(e),
        this.options = t.extend({},
        i.DEFAULTS, n),
        this.init()
    };
    i.prototype.init = function() {
        var e = this.$element,
        i = e.data("url");
        t.getJSON(i, 
        function(t) {
            0 === +t.code && e.html(t.data)
        })
    },
    t.fn.async = e
} (jQuery),
function(t) {
    "use strict";
    if (t.kdt) {
        var e = t.kdt.spm();
        t.kdt.clickLogHandler = function(i) {
            function n() {
                var n = t.Deferred().done(function() {
                    a || t.kdt.openLink(f)
                }),
                o = {
                    spm: e,
                    fm: "click",
                    url: window.encodeURIComponent(r),
                    referer_url: encodeURIComponent(document.referrer),
                    title: t.trim(s)
                };
                i.fromMenu && t.extend(o, {
                    click_type: "menu"
                }),
                null !== l && void 0 !== l && t.extend(o, {
                    click_id: l
                });
                var h = t.kdt.addParameter(_global.logURL, o);
                t.kdt.log(h, n)
            }
            var o = t(this),
            r = o.attr("href"),
            a = "_blank" === o.attr("target"),
            l = o.data("goods-id"),
            s = o.prop("title") || o.text();
            if ("" !== t.trim(r) && 0 !== t.trim(r).indexOf("javascript") && !o.hasClass("js-no-follow")) {
                var f = r;
                r.match(/^https?:\/\/\S*\.?(koudaitong\.com|kdt\.im|youzan\.com)/) && (f = t.kdt.addParameter(r, {
                    spm: e
                })),
                n(),
                a ? o.attr("href", f) : i.preventDefault()
            }
        },
        t(document).on("click", "a", t.kdt.clickLogHandler);
        var i = function() {
            var e = [],
            i = t(".js-goods");
            return i.length < 1 ? null: (i.each(function(i, n) {
                var o = t(n);
                e.push(o.data("goods-id"))
            }), e.join(","))
        } (),
        n = {
            spm: e,
            fm: "display",
            referer_url: encodeURIComponent(document.referrer),
            title: t.trim(document.title)
        };
        i && t.extend(n, {
            display_goods: i
        })
    }
} (jQuery);



//鼠标经过预览图片函数
function preview(img){
	$("#preview .jqzoom img").attr("src",$(img).attr("src"));
	$("#preview .jqzoom img").attr("jqimg",$(img).attr("bimg"));
}


//图片预览小图移动效果,页面加载时触发
$(function(){
	var tempLength = 0; //临时变量,当前移动的长度
	var viewNum = 5; //设置每次显示图片的个数量
	var moveNum = 2; //每次移动的数量
	var moveTime = 300; //移动速度,毫秒
	var scrollDiv = $(".spec-scroll .items ul"); //进行移动动画的容器
	var scrollItems = $(".spec-scroll .items ul li"); //移动容器里的集合
	var moveLength = scrollItems.eq(0).width() * moveNum; //计算每次移动的长度
	var countLength = (scrollItems.length - viewNum) * scrollItems.eq(0).width(); //计算总长度,总个数*单个长度
	  
	//下一张
	$(".spec-scroll .next").bind("click",function(){
		if(tempLength < countLength){
			if((countLength - tempLength) > moveLength){
				scrollDiv.animate({left:"-=" + moveLength + "px"}, moveTime);
				tempLength += moveLength;
			}else{
				scrollDiv.animate({left:"-=" + (countLength - tempLength) + "px"}, moveTime);
				tempLength += (countLength - tempLength);
			}
		}
	});
	//上一张
	$(".spec-scroll .prev").bind("click",function(){
		if(tempLength > 0){
			if(tempLength > moveLength){
				scrollDiv.animate({left: "+=" + moveLength + "px"}, moveTime);
				tempLength -= moveLength;
			}else{
				scrollDiv.animate({left: "+=" + tempLength + "px"}, moveTime);
				tempLength = 0;
			}
		}
	});
});