(function($, _, Backbone) {
  var options;
  var page = {
    form: $(".form-horizontal"),
    init: function() {
      this.form.submit(function() {
        var content = [];
        page.form.find(".widget-item").each(function() {
          var el = $(this);
          var getVal = function(name) {
            return $.trim(el.find('[name=' + name + ']').val());
          };

          content.push({
              "image": el.find("img").attr("src"),
            "caption": getVal('caption'),
            "link": getVal('link'),
            "desc": getVal('desc')
          });
        });
          page.form.find("[name=content]").val(JSON.stringify(content));
      });
    }
  }
  var itemView = Backbone.View.extend({
    template: _.template($("#widget-item-template").html()),
    events: {
      "click .close": "cancel"
    },
    initialize: function() {
      this.model.on("destroy", this.remove, this);
      this.model.on("change", this.render, this);
    },
    render: function() {
      this.$el.html(this.template(this.model.toJSON()));
      return this.$el;
    },
    cancel: function() {
      this.model.destroy();
    }
  });
  var allView = Backbone.View.extend({
    el: $("#widget-items"),
    events: {
      'click .widget-item-add': 'popup'
    },
    initialize: function() {
      this.$addBtn = this.$('.widget-item-add');
      this.$el.insertBefore(page.form.find('.form-group:last'));
      this.collection.on("add", this.addOne, this);
      this.render();
    },
    render: function() {
      this.collection.forEach(this.addOne, this);
      this.fileupload();
      this.sortable();
    },
    addOne: function(model) {
      var item = new itemView({
        model: model
      }).render();
      item.insertBefore(this.$addBtn);
    },
    fileupload: function() {
      var self = this;
      this.$("[name=image]").fileupload({
        url: options.uploadUrl,
        formData: function() {
          return [];
        },
        done: function(e, data) {
          var res = $.parseJSON(data.jqXHR.responseText);
          if (res.status) {
            self.collection.add({
                image: res.image,
                caption: '',
                link: '',
                desc: ''
            });
          } else {
            alert(res.message);
          }
        }
      });
    },
    sortable: function() {
      this.$el.sortable({
        items: ".widget-item",
        tolerance: "pointer"
      });
    },
    popup: function(e) {
      this.$('[name=image]')[0].click();
    }
  });
  this.widgetAction = function(opts) {
    options = opts;
    new allView({
      collection: new Backbone.Collection(opts.items)
    });
    page.init();
  }
})(jQuery, _, Backbone);
