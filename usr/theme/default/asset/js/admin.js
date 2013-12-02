(function($) {
  var config = $('#js-modules-nav').data('config');
  
  var ModulesNav = function() {
    this.el = $('#js-modules-nav');
    this.$('.collapse').on('show.bs.collapse', $.proxy(this.load, this));
  };
  ModulesNav.prototype = {
    $: function(selector) {
      return this.el.find(selector);
    },
    template: function(arr) {
      var html = '';
      var itemHelper = function(obj) {
        var str = '<li class="{cls}"><a href="{link}">{text}</a>';
        return str.replace(/\{(\w+)\}/g, function($1,$2) {
          return obj[$2] ? obj[$2] : '';
        });
      }
      if (arr.length) {
        $.each(arr, function(index, item) {
          html += itemHelper(item);
        });
        return html;
      } else {
        return itemHelper({
          link: 'javascript:void(0)',
          text: config.emptyNav,
          cls: 'disabled'
        });
      }
    },
    load: function(e) {
      var target = $(e.target);
      var self = this;
      if (target.find('li').length) return;

      e.preventDefault();
      $.getJSON(config.url).done(function(data) {
        target.html(self.template(data))
              .parent()
              .find('> a').trigger('click');
      });
    }
  }

  new ModulesNav;
})(jQuery)