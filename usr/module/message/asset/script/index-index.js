(function($) {
    var options;
    var app = {
        init: function() {
            this.cacheElements();
            this.bindEvents();
        },
        $: function(selector) {
            return this.$el.find(selector);
        },
        cacheElements: function() {
            this.$el = $('#message-js');
            this.$delete = this.$('.message-js-delete');
            this.$select = this.$('.message-batch-action');
            this.$items = this.$('.message-item');
            this.$batch = this.$('.message-js-batch');
        },
        bindEvents: function() {
            this.$batch.click(this.checkedAll);
            this.$select.change($.proxy(this.batchAction, this));
            this.$items.bind('click',this.itemsBind);
            this.$delete.click(this.deleteAction);
           


        },
        checkedAll: function() {
            //Note: if you donot bind this, you must use app
            app.$('.message-js-check').attr('checked', app.$batch.prop('checked'));
        },
        batchAction: function() {
            var checked = app.$('.message-js-check:checked');
            var action = $.trim(this.$select.val());
            var ids = [];
            if (checked.length) {
                if (action == "delete") {
                    if (!confirm('Are you sure to delete the message selected ?')) {
                        return;
                    }
                }
                checked.each(function() {
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
        itemsBind: function(c) {
            if (c.target.tagName === "A" || c.target.tagName === "INPUT" || c.target.tagName === "IMG") {
                return;
            }
            window.location = $(this).find(".message-content p a").attr("href")
        },
        deleteAction: function() {
            if (!confirm('Are you sure to delete the message selected ?')) {
                return false;
            }
        },
    };

    this.messageIndex = function(opts) {
        options = opts || {};
        app.init();
    };
})(jQuery);