/*new FocusPic(id,param)*/
jQuery.easing['jswing'] = jQuery.easing['swing'];
jQuery.extend(jQuery.easing, {
    easeOutSine: function (x, t, b, c, d) {
        return c * Math.sin(t / d * (Math.PI / 2)) + b;
    }
});
var FocusPic = (function ($) {
    var options = {
        rotateTime: 3400,
        finishTime: 700,
        imgWrapCls: ".carousel-inner",
        numWrapCls: ".carousel-control-bg",
        animateType: "easeOutSine",
        triggerType: "mouseenter"
    };
    function FocusPic(id, param) {
        this.obj = $("#" + id);
        this.param = $.extend({}, options, param || {});
        this.imgWrap = this.obj.find(this.param.imgWrapCls);
        this.numWrap = this.obj.find(this.param.numWrapCls);
        this.length = this.imgWrap.find(">").length;
        this.step = this.imgWrap.find(">").outerWidth();
        this.current = 1;
        this.init();
    }
    FocusPic.prototype = {
        init: function () {
            var self = this;
            this.imgWrap.append(this.imgWrap.find(">:first").clone());
            this.time = setInterval($.proxy(this.autoSlide, this), this.param.rotateTime);
            this.numWrap.on(this.param.triggerType, "li", function () {
                self.numWrap.find(">").removeClass("current");
                $(this).addClass("current");
                self.slideTo($(this).index());
            }).mouseleave(function () {
                clearInterval(self.time);
                self.time = setInterval($.proxy(self.autoSlide, self), self.param.rotateTime);
            });
        },
        autoSlide: function () {
            var self = this;
            this.current++;
            if (this.current <= this.length) {
                this.imgWrap.animate({
                    "marginLeft": "-=" + this.step
                }, this.param.finishTime, this.param.animateType);
            } else {
                this.imgWrap.animate({
                    "marginLeft": "-=" + this.step
                }, this.param.finishTime, this.param.animateType, function () {
                    self.imgWrap.css("marginLeft", 0);
                });
                this.current = 1;
            }
            this.numWrap.find(">").removeClass("current").eq(this.current - 1).addClass("current");
        },
        slideTo: function (index) {
            clearInterval(this.time);
            this.current = index + 1;
            this.imgWrap.stop(true, true).animate({
                "marginLeft": -index * this.step
            }, 500, "easeOutSine");
        }
    };
    return FocusPic;
})(jQuery);