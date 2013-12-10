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

      var parent = target.parent();

      e.preventDefault();
      $.get(target.attr('data-url'), {
        name: parent.attr('data-name'),
        'class': 'nav pi-modules-nav-sub'
      }).done(function(data) {
        data = $.trim(data);
        if (!data) {
          data = '<ul class="nav pi-modules-nav-sub"><li class="disabled"><a>' + config.emptyNav +'</a></ul>';
        }
        target.html(data);
        parent.find('> a').trigger('click');
      });
    }
  }

  new ModulesNav;

  //For angularjs nav
  var fragReg = /#!?(.*)$/;
  $('#pi-nav-top').find('.nav-pills li').each(function() {
    var $this = $(this);
    var match = $this.find('a').attr('href').match(fragReg);
    console.log(match);
    if (!match) return false;
    $this.removeClass('active').attr('ng-class', 'navClass("' + match[1] + '")');
  });

  //For debug
  $(function() {
    var debug = $('#pi-logger-output');
    debug.length &&  debug.insertAfter($('.pi-module-content'));
  });
})(jQuery)