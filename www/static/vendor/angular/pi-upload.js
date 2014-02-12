/**
 * @name pi.directive: upload
 * @restrict A
 * @example
  <div pi-upload="uploadConfig">
    <button class="btn btn-default">
      {{upload}}
    </button>
  </div>
  <script>
  $scope.uploadConfig = {
      url: '/admin/widget/carousel/upload',
      name: 'image',
      data: {
        key1: 'value1',
      },
      start: function() {},
      success: function(res) {},
      error: function(res) {}
  }
  </script>
 */
angular.module('pi.upload', [])
.constant('piUploadConfig', {
  forceIFrameUpload: false,
  url: '/upload',
  method: 'post',
  multiple: true,
  name: 'file',
  iframeName: 'piUploadIframe',
  data: {},
  start: null,
  success: angular.noop,
  error: angular.noop,
  progress: angular.noop //Not support now
})
.directive('piUpload', ['piUploadConfig', 'piUpload',
  function(config, upload) {
    return {
      restrict: 'A',
      scope: {
        'options': '=piUpload'
      },
      link: function(scope, element, attr) {
        function getRect(ele) {
          var rect = ele[0].getBoundingClientRect();
          return {
            width: rect.width || ele.prop('offsetWidth'),
            height: rect.height || ele.prop('offsetHeight')
          };
        }
       
        var button = element.find('button');
        var inputFile = angular.element('<input type="file">');
        var btnRect = getRect(button);
        config = angular.copy(config);
        
        angular.extend(config, scope.options);

        element.css({
          position: 'relative',
          overflow: 'hidden',
          cursor: 'pointer',
          width: btnRect.width + 'px',
          height: btnRect.height + 'px'
        });
        inputFile.css({
          position: 'absolute',
          display: 'block',
          left: 0,
          top: 0,
          bottom: 0,
          opacity: 0,
          'filter': 'alpha(opacity=0)',
        })
        .attr({
          multiple: config.multiple,
          name: config.name
        });
        element.append(inputFile);

        //Solve event delegate
        element.on('click', function() {
          var input = element.find('input');
          input.one('change', function() {
            config.data[config.name] = input;
            upload(config);
          });
        });
      }
    }; 
  }
])
.factory('piFormDataUpload', ['$http', '$timeout',
  function($http, $timeout) {
    return function(config) {
      if (config.start) {
        $timeout(config.start, 0, false);
      }

      $http({
        url: config.url,
        method: config.method,
        data: config.data,
        headers: {
          'Content-Type': undefined //Important
        },
        transformRequest: function(data) {
          var formData = new FormData();
          angular.forEach(data, function(value, key) {
            //Upload file
            if (key == config.name) {
              value = value[0].files;
              if (value.length > 1) {
                angular.forEach(value, function(file, index) {
                  formData.append(key + '[' + index + ']', file);
                });
              } else {
                formData.append(key, value[0]);
              }
            } else {
              formData.append(key, value);
            }
          });
          return formData;
        }
      })
      .success(config.success)
      .error(config.error);
    };
  }
])
.factory('piIframeUpload', ['$timeout',
  function($timeout) {
    return function(config) {
      var body = angular.element(document.body);
      var iframe = angular.element('<iframe name="piUploadIframe">');
      var form = angular.element('<form>');

      if (config.start) {
        $timeout(config.start, 0);
      }
      form.attr({
        target: 'piUploadIframe',
        enctype: 'multipart/form-data',
        method: config.method,
        action: config.url
      }).css({
        display: 'none'
      });
      angular.forEach(config.data, function(value, key) {
        var input;
        if (key == config.name) {
          input = value.clone().attr('multiple', false);
          value.after(input);
          //Input file multiple (only works with FormData upload) 
          form.append(value);
        } else {
          input = angular.element('<input type="hidden">');
          input.attr({
            name: key,
            value: value
          });
          form.append(input);
        }
      });

      form.append(iframe);
      body.append(form);
      iframe.on('load', function() {
        var doc = this.contentWindow ? this.contentWindow.document : this.contentDocument;
        var res = angular.element(doc.body).text();
        $timeout(function() {
          try {
            config.success(angular.fromJson(res));
          } catch(e) {
            config.error(res);
          }
        }, 0);
        iframe.off('load');
        form.remove();
      });
      form[0].submit();
    };
  }
])
.factory('piUpload', ['piFormDataUpload', 'piIframeUpload',
  function(piFormDataUpload, piIframeUpload) {
      var support = {
        formData: window.FormData,
        xhr: window.XMLHttpRequest
      };
      function upoad (config) {
        if (!config.forceIFrameUpload && support.formData) {
          piFormDataUpload(config);
        } else {
          piIframeUpload(config);
        }
      }
      return upoad;
  }
]);