(function($) {
  var config = $('#user-js-compound').data('config');

    /*var userCompoundEdit = {
        $: function(selector) {
            return this.$el.find(selector);
        },
        init: function(options) {
            this.cacheElements();
            this.bindEvents();
        },
        cacheElements: function() {
            this.$el = $('#user-js-compound');
            this.$list = this.$('.user-info-list');
            this.$addForm = this.$('.user-info-add form');
            this.$('.user-info-item').each(function() {
                new UserItem($(this));
            });
            new UserAdd(this.$addForm);
        },
        bindEvents: function() {
            var self = this;
            this.$list.sortable({
                handle: '.user-info-header',
                items: '.user-info-item',
                update: function(event, ui) {
                    var set = [];
                    var items = self.$el.find('.user-info-item');
                    var length = items.length;
                    for (var i = 0; i < length; i++) {
                        items.each(function(index) {
                            if ($(this).find('[name=set]').val() == i) {
                                set.push(index);
                            }
                        });
                    }
                    //Reset index
                    self.resetIndex();
                    $.post(config.urlRoot + 'editCompoundSet', {
                        compound: config.compound,
                        set: set.join(',')
                    });
                }
            });
        },
        resetIndex: function() {
            var items = this.$list.find('.user-info-item');
            items.each(function(index) {
                $(this).find('[name=set]').val(index);
            });
            this.$('.user-info-add [name=set]').val(items.length);
        },
        toggleSortable: function() {
            var list = this.$list;
            var disabled = list.sortable('option', 'disabled');
            if (disabled) {
                list.find('.user-info-header')
                    .css('cursor', 'move');
                list.sortable('option', 'disabled', false);

            } else {
                list.find('.user-info-header')
                    .css('cursor', 'default');
                list.sortable('option', 'disabled', true);
            }
        },
        addOne: function(form) {
            var item = $('<div class="user-info-item">');
            var index = 'compound' + this.$('.user-info-item').length;
            item.html($('#item-template').html());
            form.attr({
                id: index,
                name: index
            }).find('.controls-action')
            .html($('#form-action-template').html());
            item.append(form);
            new UserItem(item);
            this.$list.append(item);
        }
    };
    var submitTip = function(form, res) {
        //clear error
        form.find('.error')
            .removeClass('error')
            .find('.help-inline')
            .html('');
        if (!res.status) {
            var msg = res.message;
            for(var i in msg) {
                var err = [];
                for (var j in msg[i]) {
                    err.push(msg[i][j]);
                }
                form.find('[name=' + i + ']')
                    .parents('.control-group')
                    .addClass('error')
                    .find('.help-inline')
                    .html(err.join(','));
            }
        }
    };
    //show list
    var UserItem = function(el) {
        this.$el = el;
        this.$show = this.$('.user-field-dl');
        this.$form = this.$('form');
        this.render();
        this.bindEvents();
    };
    $.extend(UserItem.prototype, {
        $: function(selector) {
            return this.$el.find(selector);
        },
        render: function() {
            var form = this.$form[0];
            var list = config.elementsList;
            var ret = '';
            for (var i in list) {
                var value = form[list[i]].value || '<em class="muted">unfiled</em>';
                ret += '<dt>' + i + '<dd>' + 
                    $.trim(value);
            }
            this.$show.html(ret);
        },
        bindEvents: function() {
            var el = this.$el;
            var self = this;
            el.on('click', '.js-edit', $.proxy(this.toggleEdit, this));
            el.on('click', '.js-reset', $.proxy(this.toggleEdit, this));
            el.on('click', '.js-delete', $.proxy(this.deleteAction, this));
            this.$form.submit(function(e) {
                e.preventDefault();
                $.post(config.urlRoot + 'editCompound', self.$form.serialize())
                 .done(function(res) {
                    self.submit(res);
                 });
            });
        },
        deleteAction: function() {
            var el = this.$el;
            var form = this.$form;
            var get = function(name) {
                return form.find('[name=' + name + ']').val();
            };
            if (confirm(config.deleteConfirm)) {
                $.post(config.urlRoot + 'deleteCompound', {
                    compound: get('group'),
                    set: get('set')
                }).done(function(res) {
                    res = $.parseJSON(res);
                    if (res.status) {
                        el.fadeOut(300, function() {
                            el.remove();
                            userCompoundEdit.resetIndex();
                        });
                    }
                });
            }
        },
        toggleEdit: function() {
            var el = this.$el;
            userCompoundEdit.toggleSortable();
            el.toggleClass('user-info-item-edit');
        },
        submit: function(res) {
            var el = this.$el;
            res = $.parseJSON(res);
            submitTip(this.$form, res);
            if (res.status) {
                this.render();
                this.toggleEdit();
            }
        }
    }); */
    //add form
  var UserAdd = function (el) {
    this.$el = el;
    this.bindEvents();
  }
  $.extend(UserAdd.prototype, {
    bindEvents: function () {
      var self = this;
      var el = this.$el;
      el.submit(function (e) {
        e.preventDefault();
        $.post(config.urlRoot + 'addCompoundItem', el.serialize())
          .done(function (res) {
            res = $.parseJSON(res);
            submitTip(el, res);
            if (res.status) {
              self.submit(res);
            }
          });
      });
    },
    $: function (selector) {
      return this.$el.find(selector);
    },
    submit: function () {
      var set = this.$('[name=set]');
      var num = parseInt(set.val(), 10);
      userCompoundEdit.addOne(this.$el.clone());
      this.$el[0].reset();
      set.val(num + 1);
    }
  });
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
      e.preventDefault();
      $.post(config.urlRoot + 'compoundForm?' + $.param(this.getParams()), 
        this.$('form').serialize()).
        done(function(res) {
          res = $.parseJSON(res);
          if (res.status) {
            self.model.set(res.data);
            if(!self.model.hasChanged()) {
              self.render();
            }
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
            var list = self.collection;
            list.splice(end, 0, list.splice(start, 1)[0]);
            //self.render();
            //d(self);
            //Reset index
            /*$.post(config.urlRoot + 'editCompoundSet', {
                compound: config.id,
                set: set.join(',')
            });*/
          }
      });
    }
  });

  var AddItemView = Backbone.View.extend({
    events: {
      'submit form': 'submit'
    },
    submit: function(e) {
      var form = this.$('form');
      var self = this;
      e.preventDefault();
      $.post(config.urlRoot + 'addCompoundItem', form.serialize()).
        done(function(res) {
          res = $.parseJSON(res); 
          if (!res.status) return;
          self.parentView.listView.collection.push(res.data);
          form[0].reset();
       });
    }
  });


  new PageView();

  //new UserAdd($('#user-info-add form'))

  function d(str) {
    console.log(str);
  }

})(jQuery)