angular.module('pi', [])
.directive('piUnique', ['$http', '$timeout', '$parse',
  function($http, $timeout, $parse) {
    function link(scope, element, attr, ctrl) {
      var url = $parse(attr.piUnique)(scope);
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
        }, 300);
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
        '<div ng-show="alert" style="position: fixed; z-index: 1043; width: 60%; left: 20%; text-align: center; top: 20px">' +
          '<div class="label label-{{alert.type}}" style="font-size: 16px; padding: 8px 15px; white-space: normal;">' + 
            '<i class="fa fa-{{alert.cls}}"></i>' +
            '<span style="margin-left: 10px;">{{alert.message}}</span>' +
          '</div>' +
        '</div>',
      restrict: 'A',
      scope: true,
      replace: true,
      link: function(scope, element, attr) {
        var show;
        var time = attr.time || 3000;
        var error = pi.translate['ERROR'] || 'Connect error, Please try again later';
        var load = pi.translate['LOAD'] || 'Loading...';
       
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
              tip.cls = 'minus-circle';
              tip.message = data.message || error;
              tip.type = 'danger';
              break;
            case 2:
              tip.cls = 'spinner fa-spin';
              tip.type = 'info';
              tip.message = data.message || load;
              break;
            default:
              tip.cls = 'check';
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
.directive('piAlert', function() {
  return {
    template:
      '<div ng-if="message" class="alert" ng-class="type && \'alert-\' + type">' +
        '<button class="close" ng-click="close()">&times;</button>' +
        '{{message}}' +
      '</div>',
    restrict: 'A',
    scope: {
      type: '=',
      message: '='
    },
    link: function(scope, element, attr) {
      scope.close = function() {
        scope.message = '';
      }
    }
  }
})
.directive('piHoverInput', function() {
  return {
    template:
      '<div class="pi-hover-input" ng-class="{\'pi-hover-input-editing\': editing}">' +
        '<span ng-click="inputShow()">{{text}}</span>' +
        '<input type="text" class="form-control input-sm" ng-model="text" ng-blur="editTextAction()" ng-keypress="enterSaveAction($event)">' +
      '</div>',
    replace: true,
    scope: {
      text: '='
    },
    restrict: 'A',
    controller: 'piHoverInputCtrl'
  }
})
.controller('piNavTopCtrl', ['$scope', '$location',
  function($scope, $location) {
    $scope.navClass = function (item) {
      if ($location.path().substr(0, item.length) == item) {
        return 'active';
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
        var input = $element.find('input');
        input.val('');
        input[0].focus();
        input.val($scope.text);
      }, 0, false);
    }

    $scope.editTextAction = save;
  
    $scope.enterSaveAction = function(e) {
      if (e.which == 13) {
        $scope.editing = 0;
        $timeout(function() {
          e.target.blur();
        }, 0, false);
      }
    }
  } 
])
.provider('pi', ['$httpProvider', '$locationProvider',
  function($httpProvider, $locationProvider) {
    var translations = {};

    this.$get = function() {
      return {
        translate: function(key) {
          var value = translations[key];
          return value ? value : key;
        }
      };
    }
    //A provider method for add translations
    this.addTranslations = function(t) {
      angular.extend(translations, t);
    }

    //A provider method for $http interceptors
    this.addAjaxInterceptors = function() {
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
              var data = rejection.data;
              $rootScope.alert = {
                status: 0,
                message: data.message
              }
              //For pi ajax debug
              if (angular.isDefined(piLoggerSetCategoryDisplay) && data.exception) {
                document.getElementById('pi-logger-category-debug')
                        .innerHTML = '<pre>' + data.exception.xdebug_message + '</pre>';
                piLoggerSetCategoryDisplay('debug', 1);
              }
              return $q.reject(rejection);
            }
          }
        }
      ]);
    }
    //A provider method for get dynamic html
    this.setGetHeader = function(httpRequest) {
      var httpRequest = httpRequest || { 'X-Requested-With': 'XMLHttpRequest', 'Accept': '*/*' };
      $httpProvider.defaults.headers.get = httpRequest;
    }

    this.setHashPrefix = function(str) {
      $locationProvider.hashPrefix(str || '!');
    }
  }
])
.filter('translate', ['pi',
  function(pi) {
    return pi.translate;
  }
]);
