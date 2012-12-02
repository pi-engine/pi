if (!Function.prototype.bind) {
    Function.prototype.bind = function() {
        var self = this, args = [].slice.call(arguments), target = args.shift();
        return function() {
            self.apply(target, args.concat([].slice.call(arguments)));
        }
    };
}
var $j = (function() {
    var cache = {};
    return function(key) {
        return cache[key] ? cache[key] : cache[key] = $(key);
    }
})();
var $m = (function() {
    var cache = {};
    return function(key, value) {
        if ( typeof value == "undefined") {
            return cache[key];
        } else {
            if (value) {
                return cache[key] ? cache[key] : cache[key] = value;
            } else {
                delete cache[key];
            }
        }
    }
})();
String.prototype.render = function (obj) {
    var s = this.toString(),
        html = "",
        fn = function (obj) {
            s = s.replace(/{if\s+(\w+)}([\s\S]+?){\/if}/g, function ($1, $2, $3) {
                if (obj[$2]) {
                    return $3.replace(/{else}.+/, "");
                } else {
                    return $3.replace(/.+{else}|.+/, "");
                }
            });
            s = s.replace(/\{(\w+)\}/g, function ($1, $2) {
                if (obj[$2]) {
                    return obj[$2];
                } else {
                    return "";
                }
            });
            return s;
        };
    if ($.isArray(obj)) {
        for (var i = 0, l = obj.length; i < l; i++) {
            html += fn(obj[i]);
        }
    } else {
        html = fn(obj);
    }
    return html;
}
var Controller=function(includes) {
    var eventSplitter=/^(\w+)\s*(.*)$/;
    var result = function() {
        if (!this.el) {
            this.el = $(document.createElement(this.tag));
        }
        this.initializer.apply(this, arguments);
        this.init.apply(this, arguments);
    };
    result.prototype.init = function() {
    };
    result.include = function(ob) {
        $.extend(this.prototype, ob);
    };
    result.extend = function(ob) {
        $.extend(this, ob);
    };
    result.include({
        tag:"div",
        initializer : function(options) {
            this.options = options;
            for (var key in this.options)
                this[key] = this.options[key];
            if (this.events)
                this.delegateEvents();
            if (this.elements)
                this.refreshElements();
        },
        $ : function(selector) {
            return this.el.find(selector);
        },
        refreshElements : function() {
            for (var key in this.elements) {
                this[this.elements[key]] = this.$(key);
            }
        },
        delegateEvents : function() {
            for (var key in this.events) {
                var methodName = this.events[key];
                var method = this[methodName].bind(this);
                var match = key.match(eventSplitter);
                var eventName = match[1], selector = match[2];
                if (selector === '') {
                    this.el.on(eventName, method);
                } else {
                    this.el.on(eventName,selector, method);
                }
            }
        },
        replace:function(element){
          var previous, ref;
          ref = [this.el, element.el || element], previous = ref[0], this.el = ref[1];
          previous.replaceWith(this.el);
          this.delegateEvents();
          this.refreshElements();
          return this.el;
        }
    });
    includes&&result.include(includes);
    return result;
}
var beautAlert = {
        msgBox: $("<div>", {
            "id": "msgbox",
            "class": "msgbox-layer-wrap"
        }),
        done: function (msg, type, time) {
            var obj = {
                msg: msg,
                type: type
            }, body = '<span class="msg-box-inner msg-box-{type}"><span class="msg-info">{msg}</span></span>'.render(obj),
            position=function(el){
               el.css("display","block");
               el.css("left",($(document).outerWidth()-el.outerWidth())/2);
            };
            if ($("#msgbox").length) {
                this.msgBox.html(body);
                position(this.msgBox);
            } else {
                this.msgBox.html(body).appendTo(document.body);;
                position(this.msgBox);
            }
            if (/msie 6\.0/i.test(navigator.userAgent)) {
                this.msgBox.css("top", document.documentElement.clientHeight / 2 + document.documentElement.scrollTop);
            }
            clearTimeout(this.time);
            if (type != "wait") {
                this.time = setTimeout(this.hidden.bind(this), time || 1700);
            }
        },
        tipShow: function (tar,option) {
            this.tipClose();
            option=$.extend({msg:tar.attr("data-title"),"class":"tooltip-arrow-s",position:["bottom","center"],offset:[0,0],arrowDir:"top"},option||{});
            this.tip=$('<div class="pi-ui-tooltip {class}" id="{id}"> <span class="tooltip-arrow tooltip-arrow-out"></span><span class="tooltip-arrow tooltip-arrow-inner"></span><div class="tooltip-msg">{msg}</div></div>'.render(option)).appendTo(document.body);
            var of=getPosition(tar,this.tip,option);
            this.tip.css({
               "left":of.left,
               "top":of.top
           }).css("visibility","visible").fadeIn("100");
        },
        hidden: function () {
            this.msgBox.fadeOut("200");
        },
        tipClose:function(){
            this.tip&&this.tip.remove();
        }
};







