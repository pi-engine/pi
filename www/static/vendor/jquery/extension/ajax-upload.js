if (!Function.prototype.bind) {
  Function.prototype.bind = function () {
    var self = this,
      args = [].slice.call(arguments),
      target = args.shift();
    return function () {
      self.apply(target, args.concat([].slice.call(arguments)));
    }
  };
}
var ajaxUpload = (function ($) {
  var opts = {
    action: "",
    start: function () {},
    supportText: 'Only support', 
    json: true,
    format: 'all', // ['jpg', 'gif']
    done: function (result) {},
    fail: function () {}
  };

  function fixPosition(el1, el2) {
    var of = el1.offset();
    el2.css({
      "left": of.left,
      "top": of.top
    });
  }
  var AjaxUplovad = function (selector, params) {
    var ifram; //solve ie6 7 iframe name bug

    this.button = typeof selector == "string" ? $(selector) : selector;
    this.params = $.extend({}, opts, params || {});
    if ($("#ajaxUploadIframe").length) {
      this.iframe = $("#ajaxUploadIframe");
    } else {
      try {
        iframe = document.createElement('<iframe name="ajaxUploadIframe">');
      } catch (ex) {
        iframe = document.createElement('iframe');
        iframe.name = "ajaxUploadIframe";
      }
      this.iframe = $(iframe).attr({
        "id": "ajaxUploadIframe"
      }).css("display", "none");
    }
    this.form = $("<form>", {
      "target": "ajaxUploadIframe",
      "enctype": "multipart/form-data",
      "method": "post",
      "action": this.params.action
    }).css({
      "z-index": 9999,
      "opacity": 0,
      "position": "absolute",
      "cursor": "pointer",
      "overflow": "hidden"
    });
    this.input = $("<input>", {
      "name": this.params.name,
      "type": "file"
    }).css({
      "height": this.button.outerHeight(),
      "width": this.button.outerWidth()
    }).appendTo(this.form);
    this.init();
  };
  AjaxUplovad.prototype = {
    init: function () {
      var self = this;
      var body = $("body");
      var btn = this.button;
      fixPosition(btn, this.form);
      body.append(this.form);
      !$("#ajaxUploadIframe").length && body.append(this.iframe);
      this.input.change(this.change.bind(this));
      this.form.submit(this.submit.bind(this));
      btn.mouseenter(function () {
        self.input.css({
          "height": self.button.outerHeight(),
          "width": self.button.outerWidth() + 10
        });
        fixPosition(self.button, self.form);
      }).on('remove', function() {
        btn.remove();
        self.form.remove();
        self.iframe.remove();
      });
    },
    change: function () {
      var format = this.input.val().replace(/^[\w\W]+\.(\w+)$/, "$1").toLowerCase();
      var params = this.params;
      if (format && $.isArray(params.format) && $.inArray(format, params.format) == -1) {
        alert(params.supportText + ' '+ params.format.join(", "));
        return;
      }
      this.params.start.call(this);
      this.form.submit();
    },
    submit: function () {
      var response, self = this,
        result, ok = true;
      this.iframe.bind("load", function () {
        response = self.iframe.contents().find('body');
        if (self.params.json) {
          try {
            result = $.parseJSON(response.text());
          } catch (e) {
            self.params.fail.call(self);
            ok = false;
          }
        } else {
          result = response.html();
        }
        ok && self.params.done.call(self, result);
        setTimeout(function () {
          response.html('');
        }, 1);
        self.iframe.unbind("load");
      });
    }
  }
  return AjaxUplovad;
})(jQuery);