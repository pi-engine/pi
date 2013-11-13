(function($) {
    var userRegisterReactivate = {
        options: {},

        $: function(selector) {
            return this.$el.find(selector);
        },

        init: function(options) {
            $.extend(this.options, options);
            this.cacheElements();
            this.bindEvents();
            this.countdownInterval = null;
            this.countdownStart = null;
            this.isTimetoReactivate = true;
        },

        cacheElements: function() {
            this.$el = $('.user-js-reactivate');
            this.$reactivateLink = this.$('.user-js-reactivate-link');
            this.$reactivateTip = this.$('.user-js-reactivate-tip');
            this.$reactivateMessage = this.$('.user-js-reactivate-message');
            this.$reactivateCountdown = this.$('.user-js-reactivate-countdown');
        },

        bindEvents: function() {
            this.$reactivateLink.on('click', $.proxy(this.reactivateAction, this));
        },

        reactivateAction: function(e) {
            e.preventDefault();

            if (this.isTimetoReactivate) {
                this.isTimetoReactivate = false;
                this.$reactivateLink.addClass('disabled');

                $.getJSON(this.options.reactivateUrl, $.proxy(function(result) {
                    this.$reactivateMessage.text('');
                    this.$reactivateCountdown.text('');
                    this.$reactivateTip.show();
                    this.countdownStart = +new Date();

                    if (result.status) {
                        this.renderReactivateMessage(this.options.AlREADY_SENT);
                    } else {
                        this.renderReactivateMessage(result.message);
                    }

                    this.countdownInterval = setInterval($.proxy(function(){
                        var passTime = parseInt((+new Date() - this.countdownStart) / 1000);
                        this.isTimetoReactivate = passTime > 60;

                        if (this.isTimetoReactivate) {
                            this.$reactivateTip.hide();
                            this.$reactivateLink.removeClass('disabled');
                            clearInterval(this.countdownInterval);
                        } else {
                            this.$reactivateCountdown.text(', ' + Math.max((60 - passTime), 0) + 's');
                        }
                    }, this), 100);
                }, this));
            }
        },

        renderReactivateMessage: function(message) {
            this.$reactivateMessage.text(message).hide().fadeIn();
        }
    };

    window.userRegisterReactivate = userRegisterReactivate;
})(jQuery);
