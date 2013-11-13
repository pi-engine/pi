/*
  new eefocus.StartTime('js-time', maxYear, 'start');
 */
(function($) {
  var eefocus = this.eefocus || {};
  var SOFAR_STR = '至今';
  var obj = {
    optionFragment: function(start, end) {
      var html = '<option value="">请选择';
      if (start > end) {
        for(var i = start; i >= end; i--) {
          html += '<option value="' + i + '">' + i;
        }
      } else {
        for(var i = start; i <= end; i++) {
          html += '<option value="' + i + '">' + i;
        }
      }
      return html;
    },
    select: function(name) {
      var str = ' 年 ';
      var options;
      if (name == 'month') {
        str = ' 月 ';
        options = this.optionFragment(1, 12);
      } else {
        options = this.optionFragment(this.maxYear, 1950);
      }
      return '<select class="input-small" data-name="' + name + '">' + options + '</select>' + str;
    },
    $: function(name) {
      return this.el.find('[data-name=' + name + ']');
    },
    setValue: function() {
      var yearValue = this.year.val();
      var monthValue = this.month.val();
      if (yearValue && monthValue) {
        this.input.val(yearValue + '-' + monthValue);
      } else {
        this.input.val('');
      }
    } 
  };

  eefocus.StartTime = function(root, maxYear, name, value) {
    this.el = $('#' + root);
    this.maxYear = maxYear;
    this.name = name;
    this.render();
    this.events();
    if (value) {
      value = value.split('-');
      this.year.val(value[0]);
      this.month.val(value[1]).trigger('change');
    }
  }

  eefocus.EndTime = function(root, maxYear, name, value, sofar) {
    this.el = $('#' + root);
    this.maxYear = maxYear;
    this.name = name;
    this.render();
    this.events();
    if (value) {
      if (value == SOFAR_STR) {
        this.$('sofar').attr('checked', 'checked').trigger('change');
      } else {
        value = value.split('-');
        this.year.val(value[0]);
        this.month.val(value[1]).trigger('change');
      }
    }
    this.valiate();
    this.sofar = sofar;
  }

  $.extend(eefocus.StartTime.prototype, obj, {
    render: function() {
      var html = this.select('year') + this.select('month')
        + '<input type="hidden" name="' + this.name + '">'; 
      this.el.html(html);
      this.input = this.el.find('[name=' + this.name + ']');
      this.year = this.$('year');
      this.month = this.$('month');
    },
    events: function() {
      var setValue = $.proxy(this.setValue, this);
      this.year.change(setValue);
      this.month.change(setValue);
    }
  });
  
  $.extend(eefocus.EndTime.prototype, obj, {
    render: function() {
      var html = this.select('year') + this.select('month') 
      + '<label class="checkbox"><input type="checkbox" data-name="sofar">至今</label><input type="hidden" name="' + this.name + '">';
      this.el.html(html);
      this.input = this.el.find('[name=' + this.name + ']');
      this.year = this.$('year');
      this.month = this.$('month');
    },
    events: function() {
      var self = this;
      var setValue = $.proxy(this.setValue, this);
      this.$('sofar').change(function() {
        if ($(this).attr('checked')) {
          self.year.val('').attr('disabled', 'disabled');
          self.month.val('').attr('disabled', 'disabled');
          self.input.val(SOFAR_STR);
        } else {
          self.year.removeAttr('disabled');
          self.month.removeAttr('disabled');
          self.input.val('');
        }
      });
      this.year.change(setValue);
      this.month.change(setValue);
    },
    valiate: function() {
      var form = this.el.parents('form');
      var startInput = form.find('[name=start]');
      var endInput = this.input;
      var self = this;
      form.submit(function(e) {
        var startValue = startInput.val();
        var endValue = endInput.val();
        var pass = true;
        if (!startValue || !endValue) {
          self.showErrMsg('开始时间和结束时间不能为空', e);
          pass = false;
        }
        if (startValue && endValue) {
          startValue = startValue.split('-');
          if (endValue == SOFAR_STR) {
            endValue = self.sofar.split('-');
          } else {
            endValue = endValue.split('-');
          }
          startValue[0] = parseInt(startValue[0]);
          startValue[1] = parseInt(startValue[1]);
          endValue[0] = parseInt(endValue[0]);
          endValue[1] = parseInt(endValue[1]);

          if (startValue[0] > endValue[0] || 
              (startValue[0] == endValue[0] && startValue[1] > endValue[1])) {
            self.showErrMsg('开始时间不能大于结束时间');
            pass = false;
          }
        }

        if (!pass) {
          e.preventDefault();
          e.stopPropagation();
        }
      })
    },
    showErrMsg: function(msg) {
      this.el.parents('.control-group').addClass('error')
        .find('.help-inline').html(msg).show();
    }
  });

  this.eefocus = eefocus;
})(jQuery);




