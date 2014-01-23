(function($) {
  var config = $('#user-js-compound').data('config');

  function submitTip(form, msg) {
    form.find('.has-error').
      removeClass('has-error').
      find('.help-block').hide();
    if (!msg) return;
    for(var i in msg) {
      var err = [];
      for (var j in msg[i]) {
        err.push(msg[i][j]);
      }
      form.find('[name=' + i + ']').
        parents('.form-group').
        addClass('has-error').
        find('.help-block').
        show().
        html(err.join(','));
    }
  }

  var CompoundsCollection = Backbone.Collection.extend({
    initialize: function() {
      this.on('remove', this.resetSet);
    },
    resetSet: function() {
      this.forEach(function(model, index) {
        model.set('set', index);
      });
    }
  });

  var PageView = Backbone.View.extend({
    el: $('#user-js-compound'),
    initialize: function() {
      this.listView = new FieldListView({
        el: this.$('.user-info-list'),
        collection: new CompoundsCollection(config.compounds)
      });
      this.addView = new AddItemView({
        el: this.$('.user-info-add')
      });
      this.listView.parentView = this;
      this.addView.parentView = this;
    }
  });

  var FieldView = Backbone.View.extend({
    className: 'user-info-item',
    events: {
      'click .js-edit': 'toggleEdit',
      'click .js-cancel': 'render',
      'submit form': 'submit',
      'click .js-delete': 'fadeRemove'
    },
    initialize: function() {
      this.listenTo(this.model, 'change', this.render);
    },
    template: _.template($('#field-template').html()),
    render: function() {
      this.$el.removeClass('user-info-item-edit');
      this.$el.html(this.template(this.model.toJSON()));
      return this.el;
    },
    toggleEdit: function() {
      var self = this;
      var body = this.$('.user-info-body');
      if (this.$el.hasClass('user-info-item-edit')) {
        this.render();
        return;
      }
      $.get(config.urlRoot + 'compoundForm', this.getParams()).done(function(res) {
          self.$el.addClass('user-info-item-edit');
          body.html(res);
      });
    },
    submit: function(e) {
      var self = this;
      var form = this.$('form');
      e.preventDefault();
      $.post(config.urlRoot + 'compoundForm?' + $.param(this.getParams()), 
        form.serialize()).
        done(function(res) {
          res = $.parseJSON(res);
          if (res.status) {
            self.model.set(res.data);
            if(!self.model.hasChanged()) {
              self.render();
            }
          } else {
            submitTip(form, res.message);
          }
        })
    },
    fadeRemove: function() {
      if (!confirm(config.deleteConfirm)) return;
      var self = this;
      var collection = this.model.collection;
      $.post(config.urlRoot + 'deleteCompound', this.getParams()).done(function(res) {
        res = $.parseJSON(res);
        if (res.status) {
          self.$el.fadeOut(250, function() {
            self.remove();
            collection.remove(self.model);
          });
        } else {
          alert(res.message);
        }
      });
    },
    getParams: function() {
      return {
        groupId: config.groupId,
        set: this.model.get('set')
      }
    }
  });

  var FieldListView = Backbone.View.extend({
    initialize: function() {
      this.render();
      this.sortable();
      this.listenTo(this.collection, 'add', this.addOne);
    },
    addOne: function(model) {
      this.$el.append(new FieldView({
        model: model
      }).render());
    },
    render: function() {
      this.$el.html('');
      this.collection.forEach(this.addOne, this);
    },
    sortable: function() {
      var self = this;
      this.$el.sortable({
          handle: '.user-info-header',
          items: '.user-info-item',
          start: function (e, ui) {
            ui.item.data('start', ui.item.index());
          },
          update: function(event, ui) {
            var start = ui.item.data('start');
            var end = ui.item.index();
            var models = self.collection.models;
            var set = [];
            models.splice(end, 0, models.splice(start, 1)[0]);
            var length = models.length;
            for (var i = 0; i < length; i++) {
              _.each(models, function(item, index) {
                if (item.get('set') == i) {
                  set.push(index);
                }
              });
            }
            
            $.post(config.urlRoot + 'editCompoundSet', {
                groupId: config.groupId,
                set: set.join(',')
            }).done(function(res) {
              res = $.parseJSON(res);
              if (!res.status) return;
              self.collection.resetSet();
            });
          }
      });
    }
  });

  var AddItemView = Backbone.View.extend({
    events: {
      'submit form': 'submit'
    },
    initialize: function() {
      this.$('.js-cancel').remove();
    },
    submit: function(e) {
      var form = this.$('form');
      var submit = this.$('[name=submit]');
      var self = this;
      e.preventDefault();
      submit.attr('disabled', 'disabled');
      $.post(config.urlRoot + 'addCompoundItem', form.serialize()).
        done(function(res) {
          res = $.parseJSON(res);
          if (res.status) {
            location.reload();
          } else {
            submitTip(form, res.message);
            submit.removeAttr('disabled', 'disabled'); 
          }
       }).
       fail(function() {
        location.href = location.href;
       });
    }
  });

  new PageView();
})(jQuery)