/*
  new eefocus.StartTime('js-time', maxYear, 'start');
 */
(function($) {
  var eefocus = eefocus || {};
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

  eefocus.StartTime = function(root, maxYear, name) {
    this.el = $('#' + root);
    this.maxYear = maxYear;
    this.name = name;
    this.render();
    this.events();
  }

  eefocus.EndTime = function(root, maxYear, name) {
    this.el = $('#' + root);
    this.maxYear = maxYear;
    this.name = name;
    this.render();
    this.events();
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
          self.input.val('至今');
        } else {
          self.year.removeAttr('disabled');
          self.month.removeAttr('disabled');
          self.input.val('');
        }
      });
      this.year.change(setValue);
      this.month.change(setValue);
    }
  });

  this.eefocus = eefocus;
})(jQuery)




