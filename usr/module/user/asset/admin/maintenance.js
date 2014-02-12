angular.module('user')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }
    $routeProvider.when('/stats', {
      templateUrl: tpl('maintenance-stats'),
      controller: 'statsCtrl',
      resolve: {
        data: ['$q', '$rootScope', 'server',
          function($q, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server.getStats().success(function (data) {
              var tabs = [];
              angular.forEach(data.ip, function(value, key) {
                tabs.push({
                  title: config.t[key.toUpperCase()],
                  content: value
                });
              });
              delete data.ip;
              data.tabs = tabs;
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).when('/logs', {
      templateUrl: tpl('maintenance-logs'),
      controller: 'logCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
            $rootScope.alert = 2;
            params.sort = params.sort || 'time_activated';
            server.getLog(params).success(function (data) {
              data.sort = params.sort;
              angular.forEach(data.users, function(item) {
                item.editUrl = config.editUrlRoot + 'index/uid/' + item.id;
              });
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
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).when('/logs/:id', {
      templateUrl: tpl('maintenance-logs-detail'),
      controller: 'logsDetailCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server.getUserLogs($route.current.params.id).success(function(data) {
              angular.forEach(data, function(value, key) {
                if (!value) data[key] = config.t.NULL;
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
    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
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

    this.getUserLogs = function(id) {
      return $http.get(root + 'log', {
        params: {
          uid: id
        }
      });
    }

    this.getDeleted = function(params) {
      return $http.get(root + 'deletedList', {
        params: params
      });
    }

    this.clear = function(params) {
      return $http.post(root + 'clear', params);
    }
  }
])
.controller('statsCtrl', ['$scope', 'data', 'server',
  function($scope, data, server) {
    angular.extend($scope, data);
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
.controller('deletedCtrl', ['$scope', '$location', 'data', 'server', 'config',
  function($scope, $location, data, server, config) {
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
      var ids = [];
      var users = $scope.users;
      angular.forEach(users, function(item) {
        item.checked && ids.push(item.id);
      });
      if (!ids.length) return;
      if (!confirm(config.t.DELETE_BATCH)) return;
      server.clear({ uids: ids.join(',') }).success(function(data) {
        if (!data.status) return;
        var ret = [];
        angular.forEach(users, function(item) {
          !item.checked && ret.push(item);
        });
        $scope.users = ret;
      });
    }

    $scope.clearAllAction = function() {
      if (!$scope.users.length) return;
      if (!confirm(config.t.DELETE_ALL)) return;
      server.clear({ type: 'all' }).success(function(data) {
        if (!data.status) return;
        $scope.users = [];
        $scope.paginator.count = 0;
      });
    }

    $scope.clearAction = function(idx) {
      if (!confirm(config.t.DELETE_ONE)) return;
      var item = $scope.users[idx];
      server.clear({ uids: item.id }).success(function(data) {
        if (!data.status) return;
        $scope.users.splice(idx, 1);
      });
    }
  }
])
.controller('logsDetailCtrl', ['$scope', 'data', 
  function($scope, data) {
    $scope.entity = data;
  }
]);