(function($) {
    var options;
    var app = {
        init: function() {
            this.cacheElements();
            this.bindEvents();
            this.errorAction();
        },
        $: function(selector) {
            return this.$el.find(selector);
        },
        cacheElements: function() {
            this.$el = $('#message-js');
            this.$username = this.$("input[name='username']");
            this.$posi = this.$('.message-input-posi');
            this.$form = this.$('form');
            this.$content = this.$("[name='content']");
            this.$error = this.$('.message-js-error');
        },
        bindEvents: function() {
            this.$username.blur(this.userBlur);
            this.$username.focus(this.userFocus);
            this.$posi.on('click','.message-user-suc>a',this.posiOn);
            this.$form.submit(this.submitAction);
            this.$content.focus(this.conFocus);     
        },
        userBlur: function() {
            var val = $(this).val();
            $.get(options.url, {
                username: val
            }).done(function(res) {
                res = $.parseJSON(res);
                app.$posi.find('p').remove();
                
                if (res.status) {
                    app.$posi.append('<p class="label message-user-suc"></p>');
                    app.$posi.find('p').html('<a href="javascript:;" class="pull-right">×</a>'+val);
                    app.$username.removeClass().parent().find('span').empty();                 
                } else {
                    if (val != '') {
                        app.$posi.find('span').remove();
                        app.$posi.append('<span></span>');
                        var tip = 'User ' + val+ ' is not found';
                        app.$posi.find('p').empty();
                        app.$posi.find('span').html(tip == null ? '' : tip).addClass('message-error');
                        app.$username.addClass('message-username');
                    }
                }
            }); 
        },
        userFocus: function() {
            $(this).removeClass().parent().find('span').empty(); 
            $(this).parent().find('p').empty();
        },
        posiOn: function() {
            $(this).parent().empty();
            app.$username.val('');
        },
        submitAction: function() {
            var self = $('[name="content"]'),
                sendTxt = $('.message-send-text'),
                val = self.val(),
                user = app.$username.val(),
                inner = app.$posi.find('p').innerHTML;
            sendTxt.find('span').remove();
            // app.$posi.find('span').remove();
            if(user == '' && val == '') {
                app.$posi.find('span').remove();
                app.$posi.append('<span></span>');
                var tip = 'User can’t be empty';
                app.$posi.find('span').html(tip).addClass('message-error');
                app.$username.addClass('message-username');
                sendTxt.append('<span></span>');
                sendTxt.find('span').addClass('help-block message-error').html('You can’t send a empty message');
                self.addClass('message-username');
                return false;
            }
            if (user == '') {
                app.$posi.find('span').remove();
                app.$posi.append('<span></span>');
                var tip = 'User can’t be empty';
                app.$posi.find('span').html(tip).addClass('message-error');
                app.$username.addClass('message-username');
                return false;
            }
            if (val == '' ) {
                sendTxt.append('<span></span>');
                sendTxt.find('span').addClass('help-block message-error').html('You can’t send a empty message');
                self.addClass('message-username');
                return false;
            } 
            if (user != '' && inner == '' && val == '' ) {
                app.$posi.append('<span></span>');
                var tip = 'User ' + val+ ' is not found';
                app.$posi.find('p').empty();
                app.$posi.find('span').html(tip == null ? '' : tip).addClass('message-error');
                app.$username.addClass('message-username');
                return false;
            }
            if (inner != '') {
                app.$posi.find('span').empty;
            }
        },
        conFocus: function() {
            $(this).removeClass('message-username');
            $(this).parent().find('span').empty();
        },
        errorAction: function() {            
            var errForm = app.$error.next('form'),
                errInput = errForm.find('input[name="username"]'),
                errTip = 'User '+ errInput.val() + ' is not found';
            app.$posi.append('<span></span>');
            errInput.addClass('message-username').next('span').html(errTip).addClass('message-error');
        }
    };

    this.messageIndex = function(opts) {
        options = opts || {};
        app.init();
    };
})(jQuery);