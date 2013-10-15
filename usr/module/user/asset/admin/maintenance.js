angular.module('userMaintenanceModule')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }
    $routeProvider.when('/stats', {
      templateUrl: tpl('maintenance-stats'),
      controller: 'statsCtrl'
    }).when('/log', {
      templateUrl: tpl('maintenance-log'),
      controller: 'logCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
            $rootScope.alert = 2;
            params.sort = params.sort || 'time_activated';
            server.getLog(params).success(function (data) {
              angular.forEach(data.users, function(item) {
                item.time_last_login *= 1000;
                item.time_created *= 1000;
              });
              data.sort = params.sort;
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).when('/deleted', {
      templateUrl: tpl('maintenance-deleted'),
      controller: 'deletedCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server.getDeleted($route.current.params).success(function (data) {
              angular.forEach(data.users, function(item) {
                item.time_activated *= 1000;
                item.time_created *= 1000;
                item.time_deleted *= 1000;
              });
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      redirectTo: '/stats'
    });
    piProvider.hashPrefix();
    piProvider.navTabs(config.navTabs);
    piProvider.translations(config.t);
    piProvider.ajaxSetup();
  }
])
.service('server', ['$http', 'config',
  function($http, config) {
    var root = config.urlRoot;

    this.getStats = function() {
      return $http.get(root + 'stats');
    }

    this.getLog = function(params) {
      return $http.get(root + 'logList', {
        params: params
      });
    }

    this.getDeleted = function(params) {
      return $http.get(root + 'deletedList', {
        params: params
      });
    }
  }
])
.controller('statsCtrl', ['$scope', 'server', 'config',
  function($scope, server, config) {
    server.getStats().success(function(data) {
      var tabs = [];
      angular.forEach(data.ip, function(value, key) {
        tabs.push({
          title: config.t[key.toUpperCase()],
          content: value
        });
      });
      delete data.ip;
      $scope.tabs = tabs;
      angular.extend($scope, data);
    });
  }
])
.controller('logCtrl', ['$scope', '$location', 'data', 'server', 
  function($scope, $location, data, server) {
    angular.extend($scope, data);

    $scope.$watch('sort', function(newValue, oldValue) {
      if(newValue === oldValue) return;
      $location.search('sort', newValue);
    });

    $scope.$watch('paginator.page', function(newValue, oldValue) {
      if(newValue === oldValue) return;
      $location.search('p', newValue);
    });
  }
])
.controller('deletedCtrl', ['$scope', '$location', 'data', 'server',
  function($scope, $location, data, server) {
    angular.extend($scope, data);

    $scope.$watch('paginator.page', function(newValue, oldValue) {
      if(newValue === oldValue) return;
      $location.search('p', newValue);
    });

    $scope.$watch('allChecked', function(value) {
      angular.forEach($scope.users, function(item) {
        item.checked = value;
      });
    });

    $scope.clearBatchAction = function() {

    }

    $scope.clearAllAction = function() {

    }

    $scope.clearAction = function() {

    }
  }
]);