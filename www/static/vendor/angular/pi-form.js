(function(window, angular) {'use strict';
  var form = angular.module('piForm', []);
  form.directive('piUnique', function($http, $q) {
    var link = function (scope, element, attr, ctrl) {
      var checking;
      var url = attr.piUnique;
      var params = {};
      if (!url) {
        throw new Error('Please set url value on pi-unique attribute');
      }
      var deferred;
      scope.$watch(attr.ngModel, function(value) {
        if(!value) return;
        if(deferred) deferred.resolve();
        deferred = $q.defer();
        params[attr.name] = value;
        $http.get(url, {
          cache: true,
          params: params,
          timeout: deferred.promise
        }).success(function(data) {
          ctrl.$setValidity('unique', !data.status);
        });
      });
    };
    return {
      require: 'ngModel',
      link: link,
      restrict: 'A'
    }
  }).directive('piMatch', function() {
    var link = function(scope, element, attr, ctrl) {
        var match = element.inheritedData('$formController')[attr.piMatch];
        ctrl.$parsers.push(function(value) {
          if (value) {
              ctrl.$setValidity("mismatch", value === match.$viewValue);
              return value;
          } else {
              ctrl.$setValidity("mismatch", true);
              return undefined;
          }
        });
        match.$parsers.push(function(value) {
          var val = ctrl.$viewValue;
          if (val) {
              ctrl.$setValidity("mismatch", value === val);
          }
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
