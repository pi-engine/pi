angular.module('pi', [])
.directive('piUnique', ['$http', '$timeout',
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
])
.directive('piMatch', function() {
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
})
.directive('piMessage', ['$timeout', 'pi',
  function($timeout, pi) {
    /**
      status: 0 fail
      status: 1 success
      status: 2 loading
     */
    return {
      template: 
        '<div ng-if="alert" style="position: fixed; z-index: 1013; width: 60%; left: 20%; text-align: center; top: 20px">' +
          '<div class="label label-{{alert.type}}" style="font-size: 16px; padding: 8px 15px; white-space: normal;">' + 
            '<i class="icon-{{alert.cls}}"></i>' +
            '<span style="margin-left: 10px;">{{alert.message}}</span>' +
          '</div>' +
        '</div>',
      restrict: 'A',
      scope: true,
      replace: true,
      link: function(scope, element, attr) {
        var show;
        var time = attr.time || 3000;
        var error = pi.translations['ERROR'] || 'Connect error, Please try again later';
        var load = pi.translations['LOAD'] || 'Loading...';
       
        scope.$parent.$watch(attr.piMessage, function(data) {
          var tip = {};
          if (angular.isUndefined(data)) return;
          if (angular.isNumber(data)) {
            data = { status: data }
          }
          if (!data) {
            scope.alert = '';
            return;
          }
          switch (data.status) {
            case 0:
              tip.cls = 'minus-sign';
              tip.message = data.message || error;
              tip.type = 'important';
              break;
            case 2:
              tip.cls = 'spinner icon-spin';
              tip.type = 'info';
              tip.message = data.message || load;
              break;
            default:
              tip.cls = 'ok';
              tip.type = 'success';
              tip.message = data.message;
          }
          if (show) $timeout.cancel(show);
          scope.alert = tip;
          if (data.status != 2) {
            show = $timeout(function() {
              scope.alert = '';
            }, time);
          }
        });
      }
    }
  }
])
.directive('piNavTabs', ['$location',
  function($location) {
    return {
      template: 
        '<ul class="nav nav-tabs">' +
          '<li ng-repeat="item in navTabs" ng-class="navClass(item.href)">' +
            '<a href="{{item.href}}">{{item.text}}</a>' +
        '</ul>',
      replace: true,
      scope: true,
      restrict: 'A',
      controller: 'piNavTabsCtrl'
    }
  }
])
.directive('piHoverInput', function() {
  return {
    template:
      '<div class="pi-hover-input" ng-class="{\'pi-hover-input-editing\': editing}">' +
        '<span ng-click="inputShow()">{{text}}</span>' +
        '<input type="text" class="input-medium" ng-model="text" ng-blur="editTextAction()" ng-keypress="enterSaveAction($event)">' +
      '</div>',
    replace: true,
    scope: {
      text: '='
    },
    restrict: 'A',
    controller: 'piHoverInputCtrl'
  }
})
.controller('piNavTabsCtrl', ['$scope', '$location', 'pi',
  function($scope, $location, pi) {
    var reg = /^.*\//;
    $scope.navTabs = pi.navTabs;
    $scope.navClass = function (item) {
      var path = item.replace(reg, '/');
      if (!path) return;
      if ($location.path().substr(0, path.length) == path) {
        return "active";
      }
    }
  }
])
.controller('piHoverInputCtrl', ['$scope', '$element', '$timeout', '$attrs',
  function($scope, $element, $timeout, $attrs) {
    var model = $scope.$parent[$attrs.piHoverInput] || $scope.text;
    $scope._text = $scope.text;
    function save () {
      $scope.text = $scope.text.trim();
      if (!$scope.text) {
        $scope.text = $scope._text;
      } else {
        $scope.$emit('piHoverInputSave', model);
      }
      $scope.editing = 0;
    }

    $scope.inputShow = function() {
      $scope.editing = 1;
      $timeout(function() {
        $element.find('input')[0].focus();
      });
    }

    $scope.editTextAction = save;

    $scope.enterSaveAction = function(e) {
      if (e.which == 13) {
        $scope.editing = 0;
      }
    }
  } 
])
.provider('pi', ['$httpProvider', '$locationProvider',
  function($httpProvider, $locationProvider) {
    var data = {
      navTabs: {},
      translations: {}
    };
    this.$get = [
      function() {
        return data;
      }
    ];

    this.navTabs = function(navTabs) {
      data.navTabs = navTabs;
    }

    this.translations = function(t) {
      data.translations = t;
    }

    this.ajaxSetup = function() {
      $httpProvider.interceptors.push(['$q', '$rootScope',
        function($q, $rootScope) {
          return {
            response: function(response) {
              var data = response.data;
              if (angular.isObject(data) && data.hasOwnProperty('message')) {
                $rootScope.alert = data;
              }
              return response;
            },
            responseError: function(rejection) {
              $rootScope.alert = {
                status: 0,
                message: rejection.data.message
              }
              return $q.reject(rejection);
            }
          }
        }
      ]);
    }

    this.hashPrefix = function(str) {
      $locationProvider.hashPrefix(str || '!');
    }
  }
])
.filter('translate', ['pi',
  function(pi) {
    return function(input) {
      var value = pi.translations[input];
      return value ? value : input;
    }
  }
]);

if (typeof d === 'undefined') {
  var d = function(obj) {
    console.log(obj);
  }
}