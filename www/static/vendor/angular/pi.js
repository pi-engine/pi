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
  }).directive('piMessage', ['$timeout', 'pi',
    function($timeout, pi) {
      /**
        status: 0 fail
        status: 1 success
       */
      return {
        template: 
          '<div ng-if="alert" style="position: fixed; z-index: 1013; width: 60%; left: 20%; text-align: center; top: 20px">' +
            '<div class="label label-{{alert.type}}" style="font-size: 14px; line-height: 20px; padding: 5px 20px; text-align: left; white-space: normal;">' + 
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
          var error = attr.error || pi.translations['ERROR'] || 'Connect error, Please try again later';

          scope.$parent.$watch(attr.piMessage, function(data) {
            console.log(scope);
            var tip = {};
            if (!angular.isObject(data)) return;
            if (!data.status && !data.message) {
              tip.message = error;
            } else {
              tip.message = data.message;
            }
            if (show) $timeout.cancel(show);
            if (data.status) {
              tip.cls = 'ok';
              tip.type = 'success';
            } else {
              tip.cls = 'minus-sign';
              tip.type = 'important';
            }
            scope.alert = tip;
            show = $timeout(function() {
              scope.alert = '';
            }, time);
          });
        }
      }
    }
  ]).directive('piNavTabs', ['$location',
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
  ]).directive('piToggleBtn', ['pi',
    function(pi) {
      return {
        template:
          '<div class="btn-group">' +
                '<button class="btn" ng-click="yesAction()" ng-class="{\'active btn-success\': toggle.status}">' +
                    '{{toggle.yesText}}' +
                '</button>' +
                '<button class="btn" ng-click="noAction()" ng-class="{\'active btn-success\': !toggle.status}">' +
                    '{{toggle.noText}}' +
                '</button>' +
          '</div>',
        replace: true,
        restrict: 'A',
        controller: 'piToggleBtnCtrl'
      }
    }
  ]).controller('piNavTabsCtrl', ['$scope', '$location', 'pi',
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
  ]).controller('piToggleBtnCtrl', ['$scope', '$attrs', 'pi',
    function($scope, $attrs, pi) {
      var ret = $attrs.piToggleBtn;
      var eventName = $attrs.eventName || 'toogleStatus';
      if (!ret) throw new Error('Please set the attribute that you want change'); 
      ret = ret.split('.');

      $scope.toggle = {
        yesText: $attrs.yes || pi.translations['YES'] || 'Yes',
        noText: $attrs.no || pi.translations['NO'] || 'No',
        status: $scope[ret[0]][ret[1]]
      }

      $scope.$on(eventName, function(event, msg) {
        $scope.toggle.status = msg[ret[1]];
      });

      $scope.yesAction = function() {
        console.log($scope);
        //$scope[ret[0]][ret[1]] = 1;
        //$scope.$emit(eventName, $scope[ret[0]]);
      }

      $scope.noAction = function() {
        //$scope[ret[0]][ret[1]] = 0;
        //$scope.$emit(eventName, $scope[ret[0]]);
      }
    }
  ]).provider('pi', ['$httpProvider', '$locationProvider',
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
  ]).filter('translate', ['pi',
    function(pi) {
      return function(input) {
        var value = pi.translations[input];
        return value ? value : input;
      }
    }
  ]);
})(window, window.angular);