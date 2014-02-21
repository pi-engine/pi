(function ($) {
  var options;
  var app = {
    init: function () {
      this.cacheElements();
      this.bindEvents();
    },
    $: function (selector) {
      return this.$el.find(selector);
    },
    cacheElements: function () {
      this.$el = $('#message-js');
      this.$delete = this.$('a[data-confirm]');
      this.$select = this.$('.message-batch-action');
      this.$items = this.$('.message-item');
      this.$batch = this.$('.message-js-batch');
      this.$confirm = this.$('.confirm-ok');
    },
    bindEvents: function () {
      this.$batch.click(this.checkedAll);
      this.$select.change($.proxy(this.batchAction, this));
      this.$items.bind('click', this.itemsBind);
      this.$delete.click(this.deleteAction);
      this.$confirm.on('click', $.proxy(this.confirmAction, this));
    },
    checkedAll: function () {
      //Note: if you donot bind this, you must use app
      app.$('.message-js-check').attr('checked', app.$batch.prop('checked'));
    },
    confirmAction: function () {
      var checked = this.$('.message-js-check:checked');
      var ids = [];
      if (checked.length) {
        checked.each(function () {
          ids.push($(this).attr('data-id'));
        });
        location.href = options.host + 'index/delete/ids-' + ids.join(',');
      }
    },
    batchAction: function () {
      var checked = app.$('.message-js-check:checked');
      var action = $.trim(this.$select.val());
      var ids = [];

      if (checked.length) {
        if (action == "delete") {
            if (!confirm(options.confirms)) {
              app.$select.attr('value', '');
              return;
            }
          }

        checked.each(function () {
            ids.push($(this).attr('data-id'));
          });
          var url = options.host + "index/" + action + "/ids-" + ids;
          if (options.p) {
            location.href = url + '/p-' + options.p;
          } else {
            location.href = url;
          }
      } else {
        app.$select.attr('value', '');
      }
    },
    itemsBind: function (c) {
      if (c.target.tagName === "A" || c.target.tagName === "INPUT" || c.target.tagName === "IMG") {
        return;
      }
      window.location = $(this).find(".message-content p a").attr("href")
    },
    deleteAction: function (e) {
      var target = $(e.target);
      var msg = target.data('confirm')
      if (!confirm(msg)) {
        e.preventDefault();
      }
    },
  };

  this.messageIndex = function (opts) {
    options = opts || {};
    app.init();
  };
})(jQuery);