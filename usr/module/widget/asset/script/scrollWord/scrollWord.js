/*new ScrollWord(id,param) */
jQuery.easing['jswing'] = jQuery.easing['swing'];
jQuery.extend(jQuery.easing, {
    easeOutCubic: function (x, t, b, c, d) {
        return c * ((t = t / d - 1) * t * t + 1) + b;
    }
});
function ScrollWord(id, param) {
    this.obj = $("#" + id);
    this.params = $.extend({
        time : 2000
    }, param || {});
    this.init();
}

ScrollWord.prototype = {
    init : function() {
        this.timer = setInterval($.proxy(this.rotate,this), this.params.time);
        this.bindEvent();
    },
    bindEvent : function() {
        var self = this;
        this.obj.mouseover(function() {
            clearInterval(self.timer);
        }).mouseout(function() {
            self.timer = setInterval(self.rotate.bind(self), self.params.time);
        });
    },
    rotate : function() {
        var self = this, li = self.obj.find("li:first"), h = li.outerHeight();
        this.obj.animate({
            "marginTop" : -h
        }, 800, 'easeOutCubic',function() {
            self.obj.append(li);
            self.obj.css("marginTop", 0);
        });
    }
}