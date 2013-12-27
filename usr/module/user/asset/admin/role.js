angular.module('user')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.when('/:role/users', {
      templateUrl: tpl('role-user'),
      controller: 'UserCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
            $rootScope.alert = 2;
            server.getUserByRole(params).success(function(data) {
              data.role = params.role;
              data.users = data.users || [];
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      templateUrl: tpl('role-index'),
      controller: 'RoleCtrl',
      resolve: {
        data: ['$q', '$rootScope', 'server',
          function($q, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server.get().success(function(data) {
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    });
    
    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.service('server', ['$http', 'config',
  function ($http, config) {
    var root = config.urlRoot;
    
    this.get = function () {
      return $http.get(root + 'list');
    }

    this.getUserByRole = function(params) {
      return $http.get(root + 'user', {
        params: {
          name: params.role,
          page: params.p
        }
      });
    }

    this.addUser = function(role, entity) {
      return $http.post(root + 'user', {
        name: role,
        field: entity.field,
        user: entity.data,
        op: 'add'
      });
    }

    this.removeUser = function(role, id) {
      return $http.post(root + 'user', {
        name: role,
        field: 'uid',
        op: 'remove',
        user: id
      });
    }
  }
])
.controller('RoleCtrl', ['$scope', 'data',
  function($scope, data) {
    angular.extend($scope, data);
  }
])
.controller('UserCtrl', ['$scope', '$location', 'data', 'server',
  function($scope, $location, data, server) {
    angular.extend($scope, data);

    $scope.entity = { field: 'uid' };

    $scope.$watch('paginator.page', function(newValue, oldValue) {
      if (newValue == oldValue) return;
      $location.search('p', newValue);
    });

    $scope.removeAction = function(idx) {
      var user = $scope.users[idx];
      server.removeUser($scope.role, user.id).success(function(data) {
        if (data.status) {
          $scope.users.splice(idx, 1);
        }
      });
    }

    $scope.submit = function() {
      server.addUser($scope.role, $scope.entity).success(function(data) {
        var user = data.data;
        if (data.status) {
          $scope.users.push(user);
          $scope.entity.data = '';
        }
      });
    }
  }
]);