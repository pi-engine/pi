(function($) {
  var CompoundView = Backbone.View.extend({
    template: _.template($('#compound-template').html()),
    className: 'pi-widget js-source',
    events: {
      'click .pi-icon-cursor': 'animateRemove'
    },
    render: function() {
      this.$el.html(this.template(this.model));
      return this.el;
    },
    animateRemove: function() {
      var self = this;
      this.$el.fadeOut(300, function() {
        group.addOne(self.model, 'show');
        self.remove();
      });
    }
  });
  var ProfileView = Backbone.View.extend({
    template: _.template($('#profile-template').html()),
    className:'user-widget js-source',
    render: function() {
      this.$el.html(this.template(this.model))
        .draggable({
        helper: 'clone',
        revert: "invalid",
        connectToSortable: '.user-widget-group .user-widget-body',
        start: function(e, ui) {
          ui.helper
            .outerWidth($(e.currentTarget).outerWidth());
        }
      }).attr('data-model', JSON.stringify(this.model));

      return this.el;
    }
  });
  var GroupView = Backbone.View.extend({
    template: _.template($('#group-template').html()),
    className: 'pi-widget user-widget-group-hide',
    events: {
      'click .js-remove-all': 'animateRemove',
      'click .js-toggle': 'toggleShow',
      'click .js-remove-one': 'removeField',
      'click .js-edit-title': 'editTitle',
      'keypress .user-group-input': 'updateOnEnter',
      'blur .user-group-input': 'saveTitle' 
    },
    defaultModel: {
      title: '',
      fields: [],
      compound: ''
    },
    initialize: function() {
      this.model = $.extend({}, this.defaultModel, this.model);
    },
    render: function() {
      var el = this.$el;
      var compound = this.model.compound;
      el.html(this.template(this.model))
        .attr({
          'data-compound': compound
        });
      if (compound) {
        el.addClass('user-widget-compound');
      } else {
        el.addClass('user-widget-group');
      }
      this.sortable();
      return this.el;
    },
    animateRemove: function() {
      var self = this;
      var model = this.model;
      this.$el.fadeOut(300, function() {
        if (model.compound) {
          compound.addOne(model);
        }
        self.remove();
      });
    },
    toggleShow: function() {
      var el = this.$el;
      if (el.hasClass('user-widget-group-hide')) {
        el.removeClass('user-widget-group-hide')
          .find('.js-toggle')
          .removeClass('icon-angle-down')
          .addClass('icon-angle-up');
      } else {
        el.addClass('user-widget-group-hide')
          .find('.js-toggle')
          .removeClass('icon-angle-up')
          .addClass('icon-angle-down');
      }
    },
    removeField: function(e) {
      var tar = $(e.target).parent();
      profile.addOne(tar.data('model'));
      animateRemove(tar);
    },
    sortable: function() {
      var self = this;
      var model = this.model;
      this.$('.user-widget-body').sortable({
        items: '.user-widget',
        delay: 100,
        receive: function(e, ui) {
          var item = ui.item;
          item.fadeOut(300, function() {
            item.remove();
          });
        }
      });
    },
    editTitle: function(e) {
      this.$('.user-group-input')
        .val(this.$('.user-group-title').html())
        .show()
        .focus();
    },
    updateOnEnter: function(e) {
      if (e.keyCode == 13) {
        this.saveTitle();
      }
    },
    saveTitle: function() {
      var input = this.$('.user-group-input');
      var title = $.trim(input.val());
      if (title) {
        this.$('.user-group-title').html(title);
      } 
      input.hide();
    }
  });
  var animateRemove = function(el, fn) {
    el.fadeOut(300, function() {
      el.remove();
      fn && fn();
    });
  }
  var opts;
  var profile = {
    $el: $('#js-profile'),
    init: function() {
      this.render();
    },
    render: function() {
      var collection = opts.profile;
      for (var i = 0; i < collection.length; i++) {
        this.addOne(collection[i]);
      }
    },
    addOne: function(model) {
      this.$el.append(new ProfileView({
        model: model
      }).render());
    }
  };
  var compound = {
    $el: $('#js-compound'),
    init: function() {
      this.render();
    },
    render: function() {
      var collection = opts.compounds;
      for (var i = 0; i < collection.length; i++) {
        this.addOne(collection[i]);
      }
    },
    addOne: function(model) {
      this.$el.append(new CompoundView({
        model: model
      }).render());
    }
  }
  var group = {
    $el: $('#js-groups'),
    $: function(selector) {
      return this.$el.find(selector);
    },
    init: function() {
      this.$list = this.$('.user-widget-groups');
      this.$form = this.$('form');
      this.render();
      this.bindEvents();
    },
    render: function() {
      var collection = opts.data;
      for (var i = 0; i < collection.length; i++) {
        this.addOne(collection[i]);
      }
    },
    addOne: function(model, show) {
      var item = new GroupView({
        model: model
      });
      console.log(show);
      if (show) {
        item.toggleShow();
      }
      this.$list.append(item.render());
    },
    bindEvents: function() {
      var self = this;
      this.$form.submit(function(e) {
        e.preventDefault();
        var el = group.$('[name=title]');
        var val = $.trim(el.val());
        el.val('');
        if (val) {
          group.addOne({
            title: val
          }, 'show');
        }
      });
      this.$list.sortable({
        items: '.pi-widget',
        handle: '.pi-widget-header',
        start: function(e, ui) {
          ui.placeholder.outerHeight(ui.item.outerHeight());
        }
      });
      this.$('.js-save').click(function(e) {
        var list = self.$list;
        var data = [];
        list.find('.pi-widget').each(function() {
          var obj = {
            fields: []
          };
          var $this = $(this);
          obj.title = $this.find('.user-group-title').html();
          obj.compound = $this.attr('data-compound');
          $this.find('.user-widget').each(function() {
            obj.fields.push($(this).data('model'));
          });
          data.push(obj);
        });
        $.post(opts.urlSave, {
          data: data
        }).done(function(res) {
          res = $.parseJSON(res);
          if (res.status) {
            systemMessage.succ(opts.successMsg);
          } else {
            systemMessage.fail(opts.failMsg)
          }
        });
      });
    }
  }
  this.profileDressup = function(options) {
    opts = options;
    group.init();
    profile.init();
    compound.init();
  };
})(jQuery)