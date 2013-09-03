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
            this.$form = this.$('form');
            this.$content = this.$("[name='content']");
            this.$delete = this.$('a[data-confirm]');
        },
        bindEvents: function() {
            this.$form.submit(this.submitAction);
            this.$content.focus(this.conFocus);
            this.$delete.click(this.deleteAction);
        },
        submitAction: function() {
            var self = $('[name="content"]'),
                val = self.val();
            app.$form.find('span').remove();
            if (val == '') {
                app.$form.append('<span></span>');
                app.$form.find('span').addClass('pull-right message-error').html('You can’t send a empty message');
                self.addClass('message-username');
                return false;
            }  
        },
        conFocus: function() {
            $(this).removeClass('message-username');
            app.$form.find('span').empty();
        },
        deleteAction: function() {
            var href = app.$delete.attr('href');
            $('#confirm-modal').find('.modal-body').text($(this).attr('data-confirm'));
            $('.confirm-ok').attr('href', href);
            $('#confirm-modal').modal({show:true});
            return false;
        },
    };

    this.messageIndex = function(opts) {
        options = opts || {};
        app.init();
    };
})(jQuery);