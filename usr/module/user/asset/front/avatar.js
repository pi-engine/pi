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
    //var allSize = config.allSize;
    
    var uploadBtn = root.uploadBtn;
    var saveBtn = root.$('.js-save');
    var uploadImg = root.$('.avatar-upload-image');
    var emailInput = root.$('[name=email]');
    var repositoryRadios = root.$('.avatar-repository-inline li');
    var EMAIL_REGEXP = /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,6}$/; 
    var ajaxCache = (function () {
      var cache = {};
      return function(url, params) {
        if (params) {
          url = url + '?'+ $.param(params);
        }
        if (cache[url]) {
          return cache[url];
        } else {
          return cache[url] = $.get(url);
        }
      }
    })();
    


    function initJcrop(res) {
      var url = res['preview_url'];
      var boundx = 0;
      var boundy = 0;
      var uploadBoxSize = config.uploadBoxSize;
      var prevImgs = $('#fromUpload .avatar-preview-img').show();
      var allSizeArr = [];
      var idx = 0;

      uploadImg.removeClass('hide').attr('src', url);
      $.each(config.allSize.upload, function(key, value) {
        prevImgs.eq(idx++).attr({
          src: url,
          'data-size': value
        })
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
          saveBtn.removeAttr('disabled').data('upload', 1);
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

    root.$('.avatar-source-nav').on('shown.bs.tab', function(e) {
      var source = $(this).data('source');
      saveBtn.attr('data-source', source);
      if (source == 'upload' && !saveBtn.data('upload')) {
        saveBtn.attr('disabled', 'disabled');
      } else {
        saveBtn.removeAttr('disabled');
      }
    }).filter('[data-source=' + config.source + ']').tab('show'); 

    saveBtn.click(function() {
      var source = saveBtn.attr('data-source');
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
      } else if(source == 'gravatar') {
        data['email'] = $.trim(root.$('[name=email]').val());
      } else if (source == 'select') {
        data['name'] = root.$('[name=repository-avatar]:checked').val();
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

    emailInput.blur(function() {
      var email = $.trim(emailInput.val());
      if (!email || !EMAIL_REGEXP.test(email)) return;
      ajaxCache(config.urlRoot + 'gravatar', {
        email: email
      }).done(function(res) {
        res = $.parseJSON(res);
        var prevImgs = $('#fromGravatar .avatar-preview-img');
        var url = res.preview_url;
        var idx = 0;
        var replaceSize = function(value) {
          return url.replace(/(s=).*?(&)/,'$1' + value + '$2');
        }
        $.each(config.allSize.gravatar, function(key, value) {
          prevImgs.eq(idx++).attr('src', replaceSize(value));
        });
      });
    });

    repositoryRadios.click(function() {
      var name = $(this).find('input').attr('checked', 'checked').val();
      var prevImgs = $('#formRepository .avatar-preview-img');
      
      ajaxCache(config.urlRoot + 'repository', {
        name: name
      }).done(function(res) {
        res = $.parseJSON(res);
        if (res.status) {
          var idx = 0;
          var url;
          $.each(config.allSize.select, function(key) {
            url = res.dirname + '/' + key + '.' + res.ext;
            prevImgs.eq(idx++).attr('src', url);
          });
        } else {
          alert(res.message);
        }
      });
    });

    if (config.source == 'select') {
      repositoryRadios.find('input').filter('[value=' + config.filename + ']').attr('checked', 'checked');
    } else {
      repositoryRadios.find('input').eq(0).attr('checked', 'checked');
    }
    //ie fixed
    if (config.source != 'upload') {
      $('#fromUpload .avatar-preview-img').hide();
    }
})(jQuery)


