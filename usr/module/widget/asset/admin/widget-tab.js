(function($, _, Backbone) {
    var options;

    var page = {
        form: $("form#block"),
        init: function() {
            this.form.submit(function() {
                var content = [];
                page.form.find(".widget-item").each(function() {
                    var el = $(this);
                    var getVal = function(name) {
                        return $.trim(el.find('[name=' + options.prefix + name + ']').val());
                    };

                    content.push({
                        "caption":  getVal('caption'),
                        "link":     getVal('link'),
                        "id":       getVal('id')

                    });
                });
                page.form.find("[name=content]").val(JSON.stringify(content));
            });
        }
    };

    var itemView = Backbone.View.extend({
        template: _.template($("#widget-item-template").html()),
        events: {
            "click .close"   : "cancel"
        },
        initialize: function() {
            this.model.on("destroy", this.remove, this);
            this.model.on("change", this.render, this);
            //this.$el.attr("data-id", this.model.get("id"));
        },
        render: function() {
            var data = this.model.clone();
            data.set('prefix', options.prefix);
            //data.set('url', '');
            this.$el.html(this.template(data.toJSON()));
            return this.$el;
        },
        cancel: function() {
            tabView.$("[data-id=" + this.model.get("id") + "]").removeClass("selected");
            //this.model.destroy();
            this.remove();
        }
    });

    var allView = Backbone.View.extend({
        el: $("#widget-items"),
        events: {
            "click .widget-module-block": "loadBlock",
            "click [data-id]": "addItem"
        },
        initialize: function() {
            this.$addBtn = this.$('.widget-item-add');
            this.$el.insertBefore(page.form.find('.form-group:last'));
            this.collection.on("add", this.addOne, this);
            this.render();
        },
        render: function() {
            this.collection.forEach(this.addOne, this);
            this.sortable();
        },
        addOne: function(model) {
            var item = new itemView({
                model : model
            }).render();
            item.insertBefore(this.$addBtn);
        },
        sortable: function() {
            this.$el.sortable({
                items: ".widget-item",
                tolerance: "pointer"
            });
        },
        addItem: function(e) {
            var target = $(e.currentTarget);
            if (target.hasClass("selected")) {
            } else {
                this.collection.add({
                    caption:    $.trim(target.text()),
                    id:         target.attr("data-id"),
                    link:       ""
                });
                target.addClass("selected");
            }
        },
        loadBlock: function(e) {
            var self = this;
            var target = $(e.currentTarget).parent(".widget-tab-module-item");
            var ul = target.find("ul");
            var url = options.loadUrl.replace('__NAME__', target.attr("data-name"));
            var blockCollection = this.collection;
            if (!target.attr("load")) {
                $.getJSON(url).done(function(result) {
                    target.attr("load", 1);
                    if (result.status == 1) {
                        var t = '<% _.each(data,function(item) { %><li data-id="<%= item.id %>"><%= item.caption %></li><% }); %>';
                        if (result.data.length) {
                            ul.html(_.template(t, result.data, {variable: "data"}));
                            self.collection.each(function(m) {
                                ul.find("[data-id=" + m.get("id") + "]").addClass("selected");
                            });
                        } else {
                            ul.html('<span class="text-muted">N/A</span>');
                        }
                    }
                });
            }
            target.toggleClass("active");
        }
    });

    this.widgetAction = function(opts) {
        options = opts;
        tabView = new allView({
            collection: new Backbone.Collection(opts.items)
        });
        page.init();
    };
})(jQuery, _, Backbone);
