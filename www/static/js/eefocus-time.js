/*
    new EEFOCUS_DATA.Time('js-time', {
      year: '2013',
      month: '11'
    }, ['start', 'end']);
 */
var EEFOCUS_DATA = EEFOCUS_DATA || {};
EEFOCUS_DATA.Time = function(root, time, names) {
  this.el = $('#' + root);
  this.time = time;
  this.names = names;
  self = this;
  $(function() {
     self.init();
  });
}

EEFOCUS_DATA.Time.prototype = {
  init: function() {
    this.form = this.el.parents('form');
    this.template();
    this.startYear = this.$('start-year');
    this.startMonth = this.$('start-month');
    this.endYear = this.$('end-year');
    this.endMonth = this.$('end-month');
    this.startInput = this.el.find('[name=' + this.names[0] + ']');
    this.endInput = this.form.find('input[name=' + this.names[1] +']');
    this.events();

    var startInputValue = this.el.attr('data-value');
    var endInputValue = this.endInput.val();
    if (startInputValue) {
      startInputValue = startInputValue.split('-');
      this.startYear.val(startInputValue[0]);
      this.startMonth.val(startInputValue[1]);
    }
    if (endInputValue) {
      if (endInputValue == '至今') {
        this.$('today').attr('checked', 'checked').trigger('change');
      } else {
        endInputValue = endInputValue.split('-');
        this.endYear.val(endInputValue[0]);
        this.endMonth.val(endInputValue[1]);
      }
    }
  },
  template: function() {
    var time = this.time;
    var self = this;
    var select = function(name) {
      var str = ' 年 ';
      var ret = name.split('-');
      var options;
      if (ret[1] == 'month') {
        str = ' 月 ';
        options = self.optionFragment(1, 12);
      } else {
        options = self.optionFragment(time.year, 1950);
      }
      return '<select class="input-small" data-name="' + name + '">' + options + '</select>' + str;
    };
    var html = select('start-year') +  select('start-month') + ' - ' + select('end-year')
               + select('end-month') + '<label class="checkbox"><input type="checkbox" data-name="today">至今</label><input type="hidden" name="' + this.names[0] + '">';
    this.el.html(html);
  },
  $: function(selector) {
    return this.el.find('[data-name=' + selector + ']');
  },
  events: function() {
    var self = this;
    var setValue = $.proxy(this.setValue, this);
    this.$('today').change(function() {
      if ($(this).attr('checked')) {
        self.endYear.attr('disabled', 'disabled');
        self.endMonth.attr('disabled', 'disabled');
      } else {
        self.endYear.removeAttr('disabled');
        self.endMonth.removeAttr('disabled');
      }
      setValue();
    });
    this.startYear.change(setValue);
    this.startMonth.change(setValue);
    this.endYear.change(setValue);
    this.endMonth.change(setValue);
  },
  optionFragment: function(start, end) {
    var html = '<option value="">请选择';
    start = parseInt(start);
    end = parseInt(end);
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
  setValue: function() {
    var startYearValue = this.startYear.val();
    var startMonthValue = this.startMonth.val();
    var endYearValue = this.endYear.val();
    var endMonthValue = this.endMonth.val();

    if (this.$('today').attr('checked')) {
      this.endInput.val('至今');
    } else if (endYearValue && endMonthValue) {
      this.endInput.val(endYearValue + '-' + endMonthValue) ;
    } else {
      this.endInput.val('');
    }

    if (startYearValue && startMonthValue) {
      this.startInput.val(startYearValue + '-' + startMonthValue);
    } else {
      this.startInput.val('');
    }
  }
};