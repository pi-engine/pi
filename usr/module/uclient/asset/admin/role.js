angular.module('uclient')
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
            var role = $route.current.params.role;
            $rootScope.alert = 2;
            server.getUserByRole(role).success(function(data) {
              data.role = role;
              data.users = data.users || [];
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      templateUrl: tpl('roles'),
      controller: 'RoleCtrl',
      resolve: {
        data: ['$q', 'server',
          function($q, server) {
            var deferred = $q.defer();
            server.get().success(function(data) {
              deferred.resolve(data);
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

    this.getUserByRole = function(role) {
      return $http.get(root + 'user', {
        params: {
          name: role
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
.controller('UserCtrl', ['$scope', 'data', 'server',
  function($scope, data, server) {
    angular.extend($scope, data);

    $scope.entity = { field: 'uid' };

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
          $scope.entity.data = '';
          $scope.users.push(user);
        }
      });
    }
  }
]);