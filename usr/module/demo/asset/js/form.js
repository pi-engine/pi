(function($) {
  var FormModel = Backbone.Model.extend({
  	validate: function(attrs, options) {
  		var error = {};
			if (!attrs.username) {
				error.username = 'Please enter username';
			} else if (attrs.username.length < 6) {
				error.username = 'Username is too short';
			} else if (!/^\w{6,15}$/.test(attrs.username)) {
				error.username = 'Username only include numbers and letters';
			}
			if (!attrs.email) {
				error.email = 'Please enter email';
			} else if (!/^[0-9a-z_][_.0-9a-z-]{0,31}@([0-9a-z][0-9a-z-]{0,30}\.){1,4}[a-z]{2,4}$/.test(attrs.email)) {
				error.email = 'Please enter a valid email address';
			}
			if (!attrs.multiple.length) {
				error.multiple = 'Please check one at lease';
			}
  		if (!attrs.time) {
  			error.time = 'Please choose your time';
  		}
  		if (!attrs.select) {
  			error.select = 'Please choose your select';
  		}
			if (!attrs.textarea) {
				error.textarea = 'Please enter description';
			} else if(attrs.textarea.length < 20 ) {
				error.textarea = 'Description should greater than 20 charset';
			}
		  return !_.isEmpty(error) && error;
  	},
  	url: '/model'
  });

  var FormView = Backbone.View.extend({
  	el: $('#demo-form'),
  	template: $('#form-template').html(),
  	inputTemplate: $('#input-template').html(),
  	events: {
  		'blur .js-username': 'setUsername',
  		'blur .js-email': 'setEmail',
  		'click .js-multiple': 'setMultiple',
  		'change .js-select': 'setSelect',
  		'submit': 'submit'
  	},
  	initialize: function() {
  		this.model = new FormModel;
  		this.model.on('change', this.renderInput, this);
  		this.render();
  	},
  	render: function() {
  		this.$el.html(_.template(this.template, this.model.toJSON()));
  		this.$('[name=time]').datepicker({
    		format: "yyyy-mm-dd"
		  });
  		this.$('[data-toggle]').tooltip({
  			container: 'body'
  		});
  	},
  	renderInput: function() {
  		this.model.isValid();
  		var error = this.model.validationError || {};
  		var changed = _.keys(this.model.changed);
      // only validate one attribute
  		if (changed.length == 1) {
  			var name = changed[0];
  			return this.$('[name=' + name + ']').parents('.controls').find('.help-inline').html(_.template(this.inputTemplate, { error: error[name] || '' }));
  		}
  		for (var i in error) {
  			if (error.hasOwnProperty(i)) {
				  this.$('[name=' + i + ']').parents('.controls').find('.help-inline').html(_.template(this.inputTemplate, { error: error[i] }));
  			}
  		}
  	},
  	setUsername: function() {
  		this.model.set('username', this.getInputVal('username'));
  		// after you can also check uniqueness
  	},
  	setEmail: function() {
  		this.model.set('email', this.getInputVal('email'));
  	},
  	setMultiple: function() {
  		this.model.set('multiple', this.setMultipleVal());
  	},
  	setMultipleVal: function() {
  		var multipleVal = [];
  		this.$('[name=multiple]:checked').each(function() {
  			multipleVal.push($(this).val());
  		});
  		return multipleVal;
  	},
  	setSelect: function() {
  		this.model.set('select', this.getInputVal('select'));
  	},
  	submit: function(e) {
  		// prevent form submit
  		e.preventDefault();
  		
  		//save data to server
  		this.model.save({
  			username: this.getInputVal('username'),
  			email: this.getInputVal('email'),
  			sex: this.getInputVal('sex'),
  			time: this.getInputVal('time'),
  			multiple: this.setMultipleVal(),
  			select: this.getInputVal('select'),
  			textarea: this.getInputVal('textarea')
  		}, {
  			//url: ''
  		});
  		
  	},
  	getInputVal: function(name) {
  		return $.trim(this.$('[name=' + name + ']').val());
  	}
  });

  new FormView;
})(jQuery)