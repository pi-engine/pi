(function($) {
    var userRegisterIndex = {
        options: {},

        $: function(selector) {
            return this.$el.find(selector);
        },

        init: function(options) {
            $.extend(this.options, options);
            this.cacheElements();
            this.bindEvents();
            this.$el.attr('novalidate', 'novalidate');
        },

        cacheElements: function() {
            this.$el = $('#register');
            this.$email = this.$('[name=email]');
            this.$identity = this.$('[name=identity]');
            this.$name = this.$('[name=name]');
            this.$credential = this.$('[name=credential]');
            this.$credentialConfirm = this.$('[name="credential-confirm"]');
            this.$captcha = this.$('[name="captcha\\[input\\]"]');
            this.$termsOfService = this.$('[name=terms_of_service]');
            this.$submit = this.$('[name=submit]');
        },

        bindEvents: function() {
            this.$el.on('submit', $.proxy(this.submitAction, this));
            this.$email.on('blur', $.proxy(this.emailVerifyAction, this)).on('focus', this.inputFocusAction);
            this.$identity.on('blur', $.proxy(this.identityVerifyAction, this)).on('focus', this.inputFocusAction);
            this.$name.on('blur', $.proxy(this.nameVerifyAction, this)).on('focus', this.inputFocusAction);
            this.$credential.on('blur', $.proxy(this.credentialVerifyAction, this)).on('focus', this.inputFocusAction);
            this.$credentialConfirm.on('blur', $.proxy(this.credentialConfirmVerifyAction, this)).on('focus', this.inputFocusAction);
            this.$captcha.on('blur', $.proxy(this.captchaVerifyAction, this)).on('focus', this.inputFocusAction);
        },

        submitAction: function(e) {
            if (this.isValid()) {
                this.$submit.attr('disabled', 'disabled');
            } else {
                e.preventDefault();
            }
        },

        isValid: function() {
            this.emailVerifyAction();
            this.identityVerifyAction();
            this.nameVerifyAction();
            this.credentialVerifyAction();
            this.credentialConfirmVerifyAction();
            this.captchaVerifyAction();
            this.termsOfServiceVerifyAction();

            if (this.isEmailValid &&
                this.isIdentityValid &&
                this.isNameValid &&
                this.isCredentialValid &&
                this.isCredentialConfirmValid &&
                this.isCaptchaValid &&
                this.isTermsOfServiceValid) {
                return true;
            } else {
                return false;
            }
        },

        inputFocusAction: function() {
            var $controlGroupParent = $(this).closest('.control-group');
            $controlGroupParent.removeClass('error').find('.help-inline').html('');
        },

        emailVerifyAction: function() {
            var msg;
            var val = $.trim(this.$email.val());
            this.$email.val(val);

            if (val == '') {
                this.isEmailValid = false;
                msg = '请输入邮箱';
            } else if (!/^[0-9a-z_][_.0-9a-z-]{0,31}@([0-9a-z][0-9a-z-]{0,30}\.){1,4}[a-z]{2,4}$/i.test(val)) {
                this.isEmailValid = false;
                msg = '邮箱格式不正确';
            } else if (/@yahoo(\.com)?\.cn$/i.test(val)) {
                this.isEmailValid = false;
                msg = '暂不支持 yahoo 中国邮箱，请使用其他邮箱';
            } else {
                var i = 0,
                    len = tempEmail.length,
                    isTempEmail = false;

                for(; i < len; i++) {
                    if((val.indexOf('@' + tempEmail[i]) != -1) && (val.slice(-tempEmail[i].length) == tempEmail[i])) {
                        isTempEmail = true;
                        break;
                    }
                }

                if(isTempEmail) {
                    this.isEmailValid = false;
                    msg = '暂不支持该邮箱注册，请使用其他邮箱';
                }

                if (!msg) {
                    $.ajax({
                        async: false,
                        url: this.options.checkExistUrl,
                        data: {
                            email: val
                        },
                        dataType: 'json',
                        success: $.proxy(function(result){
                            if (!result.status) {
                                this.isEmailValid = true;
                                msg = '该邮箱可以使用';
                            } else {
                                this.isEmailValid = false;
                                msg = '该邮箱已经存在';
                            }
                        }, this) 
                    });
                }
            }

            this.renderMsg(this.$email, msg, this.isEmailValid);
        },

        identityVerifyAction: function() {
            var msg;
            var val = $.trim(this.$identity.val()).toLowerCase();
            this.$identity.val(val);

            if (val == '') {
                this.isIdentityValid = false;
                msg = '请输入用户名';
            } else if (!/^[a-z][0-9a-z]{4,24}$/i.test(val)) {
                this.isIdentityValid = false;
                msg = '5-25个字符，支持字母、数字，以字母开头';
            } else {
                $.ajax({
                    async: false,
                    url: this.options.checkExistUrl,
                    data: {
                        identity: val
                    },
                    dataType: 'json',
                    success: $.proxy(function(result){
                        if (!result.status) {
                            this.isIdentityValid = true;
                            msg = '该用户名可以使用';
                        } else {
                            this.isIdentityValid = false;
                            msg = '该用户名已经存在';
                        }
                    }, this) 
                });
            }

            this.renderMsg(this.$identity, msg, this.isIdentityValid);
        },

        nameVerifyAction: function() {
            var msg;
            var val = $.trim(this.$name.val()).toLowerCase();
            this.$name.val(val);

            if (val == '') {
                this.isNameValid = false;
                msg = '请输入昵称';
            } else if (!/^[a-z][0-9a-z]{4,24}$/i.test(val)) {
                this.isNameValid = false;
                msg = '5-25个字符，支持字母、数字，以字母开头';
            } else {
                $.ajax({
                    async: false,
                    url: this.options.checkExistUrl,
                    data: {
                        name: val
                    },
                    dataType: 'json',
                    success: $.proxy(function(result){
                        if (!result.status) {
                            this.isNameValid = true;
                            msg = '该昵称可以使用';
                        } else {
                            this.isNameValid = false;
                            msg = '该昵称已经存在';
                        }
                    }, this) 
                });
            }

            this.renderMsg(this.$name, msg, this.isNameValid);
        },

        credentialVerifyAction: function() {
            var msg;
            var val = $.trim(this.$credential.val());
            this.$credential.val(val);

            if (val == '') {
                this.isCredentialValid = false;
                msg = '请输入密码';
            } else if (!/^[a-zA-Z0-9\u3002\uff1b\uff0c\uff1a\u201c\u201d\uff08\uff09\u3001\uff1f\u300a\u300b\uFF01\u201c\u201d\u2018\u2019\u300e\u300f\u300c\u300d\uFF09\uFF08\.\_\-\?\~\!\@\#\$\%\^\&\*\\\+\`\=\[\]\(\)\{\}\|\;\'\:\"\,\/\<\>]{6,16}$/i.test(val)) {
                this.isCredentialValid = false;
                msg = '密码长度为6-16个字符';
            } else {
                this.isCredentialValid = true;
            }

            this.renderMsg(this.$credential, msg, this.isCredentialValid);
        },

        credentialConfirmVerifyAction: function() {
            var msg;
            var val = $.trim(this.$credentialConfirm.val());
            this.$credentialConfirm.val(val);

            if (val == '') {
                this.isCredentialConfirmValid = false;
                msg = '请再次输入密码';
            } else if (!/^[a-zA-Z0-9\u3002\uff1b\uff0c\uff1a\u201c\u201d\uff08\uff09\u3001\uff1f\u300a\u300b\uFF01\u201c\u201d\u2018\u2019\u300e\u300f\u300c\u300d\uFF09\uFF08\.\_\-\?\~\!\@\#\$\%\^\&\*\\\+\`\=\[\]\(\)\{\}\|\;\'\:\"\,\/\<\>]{6,16}$/i.test(val)) {
                this.isCredentialConfirmValid = false;
                msg = '密码长度为6-16个字符';
            } else if (val !== $.trim(this.$credential.val())) {
                this.isCredentialConfirmValid = false;
                msg = '两次输入密码不一致';
            } else {
                this.isCredentialConfirmValid = true;
            }

            this.renderMsg(this.$credentialConfirm, msg, this.isCredentialConfirmValid);
        },

        captchaVerifyAction: function() {
            var msg;
            var val = $.trim(this.$captcha.val());
            this.$captcha.val(val);

            if (val == '') {
                this.isCaptchaValid = false;
                msg = '请输入验证码';
            } else {
                this.isCaptchaValid = true;
            }

            this.renderMsg(this.$captcha, msg, this.isCaptchaValid, true);
        },

        termsOfServiceVerifyAction: function() {
            var msg;
            var isChecked = this.$termsOfService.is(':checked');

            if (isChecked) {
                this.isTermsOfServiceValid = true;
            } else {
                this.isTermsOfServiceValid = false;
                msg = '要完成注册必须阅读并接受与非网用户协议!';
                alert(msg);
            }
        },

        renderMsg: function($elem, msg, isValid) {
            msg = msg ? msg : '';
            var $controlGroupParent = $elem.closest('.control-group');

            if (isValid) {                
                $controlGroupParent.removeClass('error');
            } else {
                $controlGroupParent.addClass('error');
            }

            $controlGroupParent.find('.help-inline').html(msg).show();
        }
    };

    window.userRegisterIndex = userRegisterIndex;
})(jQuery);
