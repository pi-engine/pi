function d (str) {
  console.log(str);
}

(function($) {
    var jcrop_api;
    var root = {
      el: $('#js-user-avatar'),
      $: function(selector) {
        return this.el.find(selector);
      },
      cacheElements: function() {
        this.uploadBtn = this.$('.avatar-upload-btn');
      },
      init: function() {
        this.config = this.el.data('config');
        this.cacheElements();
      }
    };
    root.init();
    var config = root.config;
    var uploadBtn = root.uploadBtn;
    var saveBtn = root.$('.js-save');
    var uploadImg = root.$('.avatar-upload-image');

    function initJcrop(res) {
      var url = res['preview_url'];
      var boundx = 0;
      var boundy = 0;
      var prevImgs = root.$('.avatar-preview-img');
      var uploadBoxSize = config.uploadBoxSize;

      uploadImg.removeClass('hide').attr('src', url);
      prevImgs.each(function() {
        $(this).attr('src',  url);
      });
      jcrop_api && jcrop_api.destroy();
      if (res.w > res.h) {
        boundx = uploadBoxSize;
      } else {
        boundy = uploadBoxSize;
      }
       if (boundx) {
        boundy = uploadBoxSize * res.h / res.w;
        uploadImg.css({
          width: uploadBoxSize,
          height: boundy
        });
      } else {
        boundx = uploadBoxSize * res.w / res.h;
        uploadImg.css({
          width: boundx,
          height: uploadBoxSize
        });
      }
      uploadImg.Jcrop({
        aspectRatio: 1,
        bgOpacity: .5,
        onChange: function (c) {
          var r = Math.round;
          prevImgs.each(function() {
            var $this = $(this);
            var size = parseInt($this.data('size'), 10);
            var rx = size / c.w;
            var ry = size / c.h;
            $this.css({
              'width': r(rx * boundx),
              'height': r(ry * boundy),
              'marginLeft': r(-rx * c.x),
              'marginTop': r(-ry * c.y)
            }); 
          });
        }
      }, function () {
        jcrop_api = this;
        jcrop_api.result = res;
        this.setSelect([40, 40, 200, 200]);
      });
    }

    function showBtns() {
      root.$('.js-actions').show();
    }

    new ajaxUpload(uploadBtn, {
      action: config.urlRoot + 'upload?fake_id=' + config.fake_id,
      name: 'upload',
      format: config.format,
      json: true,
      start: function() {
        uploadBtn.val(config.processText);
      },
      done: function(res) {
        if (res.status) {
          uploadBtn.trigger('remove');
          root.$('.avatar-upload-hit').remove();
          showBtns();
          initJcrop(res.data);
        } else {
          alert(res.message);
          uploadBtn.val(config.uploadText);
        }
      }
    });

    root.$('.js-cancel').click(function() {
      location.href = location.href;
    });

    root.$('.avatar-source-nav').on('shown', function(e) {
      saveBtn.attr('data-source', $(this).data('source'));
    }).filter('[data-source=' + config.source + ']').tab('show'); 

    saveBtn.click(function() {
      var source = saveBtn.data('source');
      var data = {};
      saveBtn.attr('disabled', 'disabled');
      if (source == 'upload') {
          var result = jcrop_api.result;
          var ret = result.w > result.h ? result.w / 300 : result.h / 300;
          $.each(jcrop_api.tellScaled(), function(key, value) {
            data[key] = Math.round(value * ret);
          });
          data['avatar'] = result.preview_url;
          data['fake_id'] = config.fake_id;
      }
      data.source = source;
      $.post(config.urlRoot + 'save', data).done(function(res) {
          res = $.parseJSON(res);
          if (res.status) {
              window.location.reload();
          } else {
              saveBtn.removeAttr('disabled');
              alert(res.message);
          }
      });
    });
})(jQuery)


