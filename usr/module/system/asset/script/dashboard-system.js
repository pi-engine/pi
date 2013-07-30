(function($) {
  var options;
  var NoticeView = Backbone.View.extend({
    el: $('#system-js-notice'),
    events: {
      'click .system-js-edit': 'editModel',
      'click .system-js-cancel': 'cancel',
      'click .system-js-save': 'messageAction'
    },
    editModel: function() {
      this.$el.addClass('system-notice-editing');
      this.$('textarea').focus().val(this.$('.system-js-notice-content').html());
    },
    cancel: function() {
      this.$el.removeClass('system-notice-editing');
    },
    messageAction: function() {
      var v = $.trim(this.$('textarea').val());
      $.post(options.messageUrl, {
        content: v
      }).done(_.bind(function(data) {
        data = $.parseJSON(data);
        this.$('.system-js-notice-content').html(data.content);
        this.$('.system-js-notice-time').html(data.time);
      }, this));
      this.cancel();
    }
  });
  var LinkItemView = Backbone.View.extend({
    className: 'inline-block system-quick-link-item',
    template: $('#temp-link').html(),
    events: {
      'click .system-js-edit': 'editAction',
      'click .system-js-remove': 'removeAction'
    },
    initialize: function() {
      this.model.on('destroy', this.remove, this);
      this.model.on('change', this.render, this);
    },
    editAction: function() {
      var c = this.model.collection;
      c.current = this.model;
      c.trigger('showForm');
    },
    removeAction: function() {
      this.model.destroy();
    },
    render: function() {
      this.$el.html(_.template(this.template, this.model.toJSON()));
      this.$el.data('model', this.model.toJSON());
      return this;
    }
  });
  var LinkListView = Backbone.View.extend({
    el: $('#system-js-quick-link'),
    formTemplate: $('#temp-form').html(),
    events: {
      'click .system-js-all-edit': 'editModel',
      'click .system-js-add': 'changeCurrent',
      'click .system-js-save': 'addOrEditSaveAction',
      'click .system-js-cancel': 'hideAddForm',
      'click .system-js-all-save': 'saveLinks'
    },
    initialize: function() {
      this.linkAddBtn = this.$('.system-js-add');
      this.form = this.$('.system-quick-link-form');
      this.renderLinks();
      this.collection.on('add', this.addOne, this);
      this.collection.on('remove', this.updateLink, this);
      this.collection.on('showForm', this.showAddForm, this);
      this.$('.js-all-save').tooltip();
      this.sortLinks();
    },
    renderLinks: function() {
      this.collection.forEach(this.addOne, this);
    },
    editModel: function() {
      this.$el.addClass('system-quick-link-edit');
      this.$('.system-quick-link-box').sortable("option", "disabled", false);
    },
    sortLinks: function() {
      this.$('.system-quick-link-box').sortable({
        items: ".system-quick-link-item",
        disabled: true
      });
    },
    addOne: function(model) {
      $(new LinkItemView({
        model: model
      }).render().el).insertBefore(this.linkAddBtn);
    },
    changeCurrent: function() {
      this.collection.current = new Backbone.Model({
        action: 'add'
      });
      this.showAddForm();
    },
    showAddForm: function() {
      this.form.html(_.template(this.formTemplate, this.collection.current.toJSON(), {
        variable: 'form'
      })).css('display', 'block');
    },
    hideAddForm: function() {
      this.form.css('display', 'none');
    },
    addOrEditSaveAction: function(e) {
      var action = $(e.currentTarget).attr('data-action');
      var title = $.trim(this.$('[name=title]').val());
      var url = $.trim(this.$('[name=url]').val());
      var obj = {
        title: title,
        url: url
      };
      if (title && url) {
        if (action == 'add') {
          this.collection.add(obj);
          this.changeCurrent();
        } else {
          this.collection.current.set(obj);
          this.hideAddForm();
        }
      }
    },
    saveLinks: function() {
      var data = [];
      this.$('.system-quick-link-item').each(function() {
        data.push($(this).data('model'));
      });
      this.$el.removeClass('system-quick-link-edit');
      this.hideAddForm();
      $.post(options.linkUrl, {
        content: data
      });
    }
  });
  var SummaryView = Backbone.View.extend({
    el: $('#system-js-module-summary'),
    events: {
      'click .system-js-all': 'toggleAll'
    },
    initialize: function() {},
    toggleAll: function() {
      this.$('.accordion-body').collapse('toggle');
    }
  });

  this.dashboardSystem = function(opts) {
    options = opts;
    new NoticeView();
    new LinkListView({
      collection: options.quicLinkCollection
    });
    new SummaryView();
  };
})(jQuery);