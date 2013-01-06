var systemMessage = {
    tmp: '<div class="label label-{type}"><i class="icon-{cls}"></i><span class="system-layer-msg">{msg}</span></div>',
    _css: '.system-layer {position: fixed;top: -21px;height: 20px;line-height: 20px;z-index: 13;width: 100%;text-align: center;}.system-layer-show {top: 0;}.system-layer-msg {margin-left: 10px;}',
    _init: function() {
        var b = document.body;
        $('<style>').html(this._css).appendTo(b);
        this.el = $('<div class="system-layer">').appendTo(b);
    },
    succ: function(msg, time) {
        this._type('succ', msg, time || 3000);
    },
    fail: function(msg, time) {
        this._type('fail', msg, time || 3000);
    },
    hits: function(msg, time) {
        this._type('hits', msg, time || 3000);
    },
    wait: function(msg, time) {
        this._type('wait', msg, time || 3000);
    },
    hide: function(time) {
        var self = this;
        clearTimeout(this._timer);
        this._timer = setTimeout(function() {
            self.el.css({
                'transition': 'top .3s ease-in-out',
                'top': -22
            });
        }, time);
    },
    _type: function(type, msg, time) {
        var obj = {
            msg: msg
        };
        switch (type) {
            case 'succ':
                obj['cls'] = 'ok';
                obj['type'] = 'success';
                break;
            case 'fail':
                obj['cls'] = 'minus-sign';
                obj['type'] = 'important';
                break;
            case 'hits':
                obj['cls'] = 'info-sign';
                obj['type'] = 'info';
                break;
            case 'wait':
                obj['cls'] = 'exclamation-sign';
                obj['type'] = 'info';
                break;
            default:
                obj['cls'] = 'ok';
                obj['type'] = 'info';
        }
        this.el.css('transition', 'none');
        this.el.html(this.tmp.replace(/{(\w+)}/g, function($1, $2) {
            return obj[$2];
        })).css('top', 0);
        this.hide(time);
    }
};
$(function() {
    systemMessage._init();
});
