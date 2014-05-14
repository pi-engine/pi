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
                        "caption"   : getVal('caption'),
                        "link"      : getVal('link'),
                        "desc"      : getVal('desc')
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
        },
        render: function() {
            var data = this.model.clone();
            this.$el.html(this.template(data.toJSON()));
            return this.$el;
        },
        cancel: function() {
            this.model.destroy();
        }
    });
    var allView = Backbone.View.extend({
        el: $("#widget-items"),
        events: {
            'click .widget-item-add' : 'add'
        },
        initialize: function() {
            this.$addBtn = this.$('.widget-item-add');
            this.$el.insertBefore(page.form.find('.form-group:last'));
            this.collection.on("add", this.addOne, this);
            this.list = new Array();
            this.index = 0;
            this.render();
        },
        uniqid : function(prefix) {
            var uid = new Date().getTime().toString(16);
            uid += Math.floor((1 + Math.random()) * Math.pow(16, (16 - uid.length))).toString(16).substr(1);
            
            return (prefix || '') + uid;
        },
        render: function() {
            this.collection.forEach(this.addOne, this);
            this.sortable();
        },
        addOne: function(model) {
            var id = this.uniqid('widget');
            this.list[this.index] = id;
            this.index++;
            var template = '<div id="' + id + '"></div>';
            this.$(".row").prepend(template);
            var item = new itemView({
                el    : $("#" + id),
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
        add: function() {
            this.collection.add({
                caption : '',
                link    : '',
                desc    : ''
            });
        }
    });
    this.widgetAction = function(opts) {
        options = opts;
        new allView({
            collection: new Backbone.Collection(opts.items)
        });
        page.init();
    };
})(jQuery, _, Backbone);
