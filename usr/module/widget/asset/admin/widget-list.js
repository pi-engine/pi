(function($, _, Backbone) {
    var options;
    var page = {
        form: $(".form-horizontal"),
        init: function() {
            this.form.submit(function() {
                var content = [];
                page.form.find(".widget-block").each(function() {
                    var el = $(this);
                    var getVal = function(name) {
                        return $.trim(el.find('[name=' + name + ']').val());
                    };

                    content.push({
                        "caption"   : getVal('caption'),
                        "image"     : el.find("img").attr("src"),
                        "link"      : getVal('link'),
                        "desc"      : getVal('desc'),
                        "detail"    : getVal('detail')
                    });
                });
                page.form.find("[name=content]").val(JSON.stringify(content));
            });
        }
    };
    var listItemView = Backbone.View.extend({
        template: _.template($("#list-template").html()),
        events: {
            "click .remove-block"   : "cancel",
            "click .remove-image"   : "removeUpload"
        },
        initialize: function() {
            this.model.on("destroy", this.remove, this);
            this.model.on("change", this.render, this);
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
                        self.renderUpload(options.imageRoot + res.image);
                    } else {
                        alert(res.message);
                    }
                }
            });
        },
        render: function() {
            var htmlImage = '';
            var image = this.model.get('image');
            if (image) {
                htmlImage = _.template($('#image-template').html(), {
                    'image' : image
                });
            } else {
                htmlImage = $("#upload-template").html();
            }
            var data = this.model.clone();
            data.set({
                'image' : htmlImage
            });
            this.$el.html(this.template(data.toJSON()));
            if (!image) {
                this.fileupload();
            }
            return this.$el;
        },
        renderUpload : function(image) {
            var htmlImage = '';
            if (image) {
                htmlImage = _.template($('#image-template').html(), {
                    'image' : image
                });
            } else {
                htmlImage = $("#upload-template").html();
            }
            this.$(".widget-upload-image").html(htmlImage);
            if (!image) {
                this.fileupload();
            }
        },
        removeUpload: function() {
            this.renderUpload('');
        },
        cancel: function() {
            this.model.destroy();
        }
    });
    var allListView = Backbone.View.extend({
        el: $("#widget-js-list"),
        events: {
            'click .widget-block-add' : 'add',
            'click .widget-upload-btn': 'popup'
        },
        initialize: function() {
            this.$addBtn = this.$('.widget-block-add');
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
            var item = new listItemView({
                el    : $("#" + id),
                model : model
            }).render();
            item.insertBefore(this.$addBtn);
        },
        sortable: function() {
            this.$el.sortable({
                items: ".widget-list-upload",
                tolerance: "pointer"
            });
        },
        popup: function(e) {
            this.$('[name=image]')[0].click();
        },
        add: function() {
            this.collection.add({
                image   : '',
                caption : '',
                link    : '',
                desc    : '',
                detail  : ''
            });
        }
    });
    this.widgetListAction = function(opts) {
        options = opts;
        new allListView({
            collection: new Backbone.Collection(opts.imgs)
        });
        page.init();
    };
})(jQuery, _, Backbone);
