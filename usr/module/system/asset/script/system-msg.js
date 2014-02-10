var systemMessage = {
  tmp: '<div class="alert alert-{type}" style="font-size: 14px; line-height: 20px; padding: 5px 20px; text-align: left; white-space: normal;"><i class="icon-{cls}"></i><span style="margin-left: 10px;">{msg}</span></div>',
  _init: function() {
    this.el = $('<div class="system-layer" style="position: fixed; z-index: 1043; width: 60%; left: 20%; text-align: center;">').appendTo(document.body);
  },
  succ: function(msg, time) {
    this._type('succ', msg, time || 3000);
  },
  fail: function(msg, time) {
    this._type('fail', msg, time || 5000);
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
        'top': -self.el.outerHeight()
      });
    }, time);
  },
  _type: function(type, msg, time) {
    var obj = {
      msg: msg
    };
    switch (type) {
      case 'succ':
        obj.cls = 'ok';
        obj.type = 'success';
        break;
      case 'fail':
        obj.cls = 'minus-sign';
        obj.type = 'info';
        break;
      case 'hits':
        obj.cls = 'info-sign';
        obj.type = 'info';
        break;
      case 'wait':
        obj.cls = 'exclamation-sign';
        obj.type = 'info';
        break;
      default:
        obj.cls = 'ok';
        obj.type = 'info';
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
