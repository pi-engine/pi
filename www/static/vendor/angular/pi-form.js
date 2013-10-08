(function(window, angular) {'use strict';
  var form = angular.module('piForm', []);
  form.directive('piUnique', function($http, $timeout) {
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
        if(request) clearTimeout(request);
        params[attr.name] = value;
        request = $timeout(function() {
          $http.get(url, {
            cache: true,
            params: params,
          }).success(function(data) {
            ctrl.$setValidity('unique', !data.status);
          });
        }, 600);
      });
    }
    return {
      require: 'ngModel',
      link: link,
      restrict: 'A'
    }
  }).directive('piMatch', function() {
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
  });
})(window, window.angular);
