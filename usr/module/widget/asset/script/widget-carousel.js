(function($, _, Backbone) {
  var options;
  var page = {
    form: $("#widget-js-form"),
    init: function() {
      this.form.submit(function() {
        var content = [];
        page.form.find(".widget-carousel-upload").each(function() {
          var el = $(this);
          var getVal = function(name) {
            return $.trim(el.find('[name=' + name + ']').val());
          };

          content.push({
            "caption": getVal('caption'),
            "image": el.find("img").attr("src"),
            "link": getVal('link'),
            "desc": getVal('desc')
          });
        });
        page.form.find("[name=content]").val(JSON.stringify(content));
      });
    }
  }
  var carouselItemView = Backbone.View.extend({
    className: "widget-carousel-upload",
    template: _.template($("#carousel-template").html()),
    events: {
      "click .icon-remove-sign": "cancel"
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
  var carouselListView = Backbone.View.extend({
    el: $("#widget-js-carousel"),
    events: {
      'click .widget-upload-btn': 'popup'
    },
    initialize: function() {
      this.$addBtn = this.$('.widget-upload-btn');
      this.collection.on("add", this.addOne, this);
      this.render();
    },
    render: function() {
      this.collection.forEach(this.addOne, this);
      this.fileupload();
      this.sortable();
    },
    addOne: function(model) {
      var item = new carouselItemView({
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
              image: options.imageRoot + res.image,
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
        items: ".widget-carousel-upload",
        tolerance: "pointer"
      });
    },
    popup: function(e) {
      this.$('[name=image]')[0].click();
    }
  });
  this.widgetCarouselAction = function(opts) {
    options = opts;
    new carouselListView({
      collection: new Backbone.Collection(opts.imgs)
    });
    page.init();
  }
})(jQuery, _, Backbone);
