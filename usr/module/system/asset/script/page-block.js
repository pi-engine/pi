(function($) {
  var options;
  var ModuleListItemView = Backbone.View.extend({
    template: _.template($("#module-temp").html()),
    templateBlock: _.template($("#module-block-temp").html()),
    events: {
      "click .module-header": "loadBlock"
    },
    initialize: function() {},
    render: function() {
      this.$el.html(this.template(this.model.toJSON()));
      return this;
    },
    loadBlock: function() {
      var h = this.$(".module-header");
      var b = this.$(".module-blocks");
      var tb = this.templateBlock;
      var self = this;
      h.toggleClass("active");
      if (!h.attr("data-loaded")) {
        $.getJSON(options.blockListUrl.replace('__NAME__', this.model.get("name"))).done(function(res) {
          var data = res.data;
          if (data.length) {
            for (var i = 0, l = data.length; i < l; i++) {
              b.append($(tb(data[i])).data("info", data[i]));
            }
          } else {
            b.append('<span class="label label-info">No Block</span>');
          }
          self.dragBlock(b.find(".module-block"));
          b.css('dispaly', 'block');
          h.attr("data-loaded", 1);
        });
      } else {
        b.slideToggle("300");
      }
    },
    dragBlock: function(el) {
      el.draggable({
        helper: "clone",
        handle: ".module-block-title"
      });
      el.each(function() {
        var $this = $(this);
        if ($("[id*=pi-zone-]").find(".widget-clone[data-id=" + $this.attr("data-id") + "]").length) {
          $this.draggable("option", "disabled", true);
        }
      });
    }
  });
  var ModuleListView = Backbone.View.extend({
    el: $("#widget-wrap"),
    initialize: function() {
      this.render();
    },
    render: function() {
      this.collection.forEach(this.addOne, this);
    },
    addOne: function(model) {
      this.$el.append(new ModuleListItemView({
        model: model
      }).render().el);
    }
  });
  var PageItemView = Backbone.View.extend({
    template: _.template($("#page-area-temp").html()),
    initialize: function() {
      this.render();
    },
    render: function() {
      var m = this.model;
      var el = this.$el;
      for (var i = 0, l = m.length; i < l; i++) {
        el.append(this.template(m[i]));
      }
      return this;
    }
  });
  var ThemeListView = Backbone.View.extend({
    template: $("#temp-theme").html(),
    el: $("#js-theme"),
    events: {
      "click .load-theme": "loadTheme",
      "click .set-theme-item": "setTheme"
    },
    initialize: function(options) {
      this.page = options.page;
      $('.submit-theme').click(_.bind(this.submitTheme, this));
    },
    loadTheme: function() {
      var w = this.$(".theme-wrap");
      var self = this;
      var u = w.find("ul");
      w.fadeToggle("200");
      this.$('.load-theme').toggleClass('active');
      if (!w.attr("data-load")) {
        $.getJSON(options.getThemeUrl).done(function(result) {
          u.html(_.template(self.template, _.toArray(result), {
            variable: 'data'
          }));
          w.attr("data-load", 1);
        });
      }
    },
    submitTheme: function() {
      var data = {
        page: this.page,
        blocks: {}
      };
      var b = $("[id*=pi-zone-]");
      var ret;
      for (var i = 0; i < 100; i++) {
        ret = [];
        b.filter(function(index, el) {
          return~ $(el).attr('id').indexOf('-' + i + '-');
        }).find(".widget-clone").each(function() {
          ret.push(parseInt($(this).attr("data-id"), 10));
        });
        if (ret.length) {
          data.blocks[i] = ret;
        }
      }
      $.post(options.pageSaveUrl, data).done(function(res) {
        res = $.parseJSON(res);
        if (res.status) {
          systemMessage.succ(res.message);
        } else {
          systemMessage.fail(res.message);
        }
      });
    },
    setTheme: function(e) {
      this.$(".set-theme-item").removeClass("active");
      var tar = $(e.currentTarget);
      tar.addClass("active");
      $.get(options.getZoneUrl.replace('__NAME__', tar.attr("data-name"))).done(function(res) {
        var org = [];
        var i;
        for (i = 0; i < 100; i++) {
          org.push($("#pi-zone-" + i + "-edit").find(".ui-placeholder,.pi-zone-num").remove().end().html());
        }
        $("#drag-main").html(res);
        for (i = 0; i < 100; i++) {
          $("#pi-zone-" + i + "-edit").html(org[i]);
        }
        initPageEvent();
      });
    }
  });
  var initPageEvent = function() {
    var drag = $("[id*=pi-zone-]");
    drag.droppable({
      accept: "#widget-wrap .module-block",
      hoverClass: "ui-state-hover",
      drop: function(event, ui) {
        var h = ui.draggable,
          m = h.data("info");
        h.draggable("option", "disabled", true);
        $(_.template($("#page-area-temp").html(), {
          title: m.title,
          id: m.id,
          description: m.description
        })).insertBefore($(this).find(".ui-placeholder"));
      }
    }).append("<div class='ui-placeholder'></div>");
    drag.each(function() {
      var $this = $(this);
      $this.css("min-height", $this.parent().height());
      $this.prepend("<div class='pi-zone-num'>" + $this.attr("id").replace(/[a-z-]/g, "") + "</div>");
    });
    drag.sortable({
      placeholder: "ui-state-highlight",
      connectWith: drag,
      items: ".widget-clone",
      revert: 300
    });
    drag.on("click", ".close", function(e) {
      var tar = $(e.target),
        p = tar.parents(".widget-clone");
      p.fadeOut(100, function() {
        p.remove();
        $("#" + p.attr("id").replace(/\-clone$/, "")).draggable("option", "disabled", false);
      });
    });
  };
  this.pageBlcokAction = function(opts) {
    options = opts || {};
    new ModuleListView({
      collection: new Backbone.Collection(options.modules)
    });
    var blocks = options.blocks;
    for (var i in blocks) {
      if (blocks.hasOwnProperty(i)) {
        new PageItemView({
          el: $("#pi-zone-" + parseInt(i, 10) + "-edit"),
          model: blocks[i]
        });
      }
    }
    new ThemeListView({
      page: options.page
    });
    initPageEvent();
  };
})(jQuery);
