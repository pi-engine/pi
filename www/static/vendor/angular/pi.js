(function(window, angular) {'use strict';
  var pi = angular.module('pi', []);
  pi.directive('piUnique', ['$http', '$timeout',
    function($http, $timeout) {
      function link(scope, element, attr, ctrl) {
        var url = attr.piUnique;
        var params = {};
        var request;
        if (!url) {
          throw new Error('Please set url value on pi-unique attribute');
        }

        scope.$watch(attr.ngModel, function(value) {
          if(!value) return;
          // If there was a previous request, stop it.
          if(request) $timeout.cancel(request);
          params[attr.name] = value;
          request = $timeout(function() {
            $http.get(url, {
              cache: true,
              params: params,
            }).success(function(data) {
              ctrl.$setValidity('unique', !data.status);
            });
          }, 400);
        });
      }
      return {
        require: 'ngModel',
        link: link,
        restrict: 'A'
      }
    }
  ]).directive('piMatch', function() {
    var link = function(scope, element, attr, ctrl) {
        var match = element.inheritedData('$formController')[attr.piMatch];
        ctrl.$parsers.push(function(value) {
          if (value == match.$viewValue) {
            ctrl.$setValidity("mismatch", true);
            return value;
          } else {
            ctrl.$setValidity("mismatch", false);
            return undefined;
          }
        });
        match.$parsers.push(function(value) {
          var val = ctrl.$viewValue || '';
          ctrl.$setValidity("mismatch", value == val);
          return value;
        });
    };
    return {
        require: 'ngModel',
        link: link,
        restrict: 'A'
    };
  }).directive('piMessage', ['$timeout',
    function($timeout) {
      return {
        template: 
          '<div ng-if="alert" style="position: fixed; z-index: 1013; width: 60%; left: 20%; text-align: center; top: 20px">' +
            '<div class="label label-{{type}}" style="font-size: 14px; line-height: 20px; padding: 5px 20px; text-align: left; white-space: normal;">' + 
              '<i class="icon-{{cls}}"></i>' +
              '<span style="margin-left: 10px;">{{message}}</span>' +
            '</div>' +
          '</div>',
        restrict: 'A',
        scope: true,
        replace: true,
        link: function(scope, element, attr) {
          var show;
          var time = attr.time || 3000;
          var error = attr.error || 'Connect error, Please try again later';
          
          scope.$parent.$watch(attr.piMessage, function(data) {
            if (!data) return;
            if (!angular.isObject(data)) {
              data = {
                status: 0,
                message: error
              }
            };
            scope.alert = data;
            if (show) $timeout.cancel(show);
            if (data.status) {
              scope.cls = 'ok';
              scope.type = 'success';
            } else {
              scope.cls = 'minus-sign';
              scope.type = 'important';
            }
            scope.message = data.message;
            show = $timeout(function() {
              scope.alert = '';
            }, time);
          });
        }
      }
    }
 ]);
})(window, window.angular);