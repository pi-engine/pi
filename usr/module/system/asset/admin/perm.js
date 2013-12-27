angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
     function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.when('/:section', {
      templateUrl: tpl('perm'),
      controller: 'PermCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
            $rootScope.alert = 2;
            params.name = config.name;
            server.get(params).success(function(data) {
              var roles = [];
              angular.forEach(data.roles, function(item, key) {
                item.name = key;
                roles.push(item);
              });
              data.roles = roles;
              data.cols = roles.length + 2;
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      redirectTo: '/front'
    });
    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.service('server', ['$http', 'config',
  function($http, config) {
    var root = config.urlRoot;

    this.get = function(params) {
      return $http.get(root + 'resources', {
        params: params
      });
    }

    this.post = function(role, resource, section, op) {
      return $http.post(root + 'assign', {
        role: role,
        resource: resource,
        section: section,
        op: op,
        name: config.name
      })
    }
  }
])
.controller('PermCtrl', ['$scope', '$timeout', 'server', 'data',
  function($scope, $timeout, server, data) {
    angular.extend($scope, data);

    $scope.assignAction = function(role, item) {
      var name = role.name;
      var action;
      angular.forEach(item.roles, function(role) {
        if (role.name == name) {
         action = role.value ? 'revoke' : 'grant';
        }
      });
      server.post(name, item.resource, item.section, action).success(function(data) {
        if (!data.status) return;
        role.value = !role.value;
      });
    }

    $scope.assignAllResource = function(role, action) {
      var op = action ? 'grant' : 'revoke';
      var name = role.name;
      server.post(name, '_all', role.section, op).success(function(data) {
        if (!data.status) return;
        angular.forEach($scope.resources, function(resource, key) {
          angular.forEach(resource, function(resourceItem) {
            angular.forEach(resourceItem.roles, function(item) {
              if (item.name == name) {
                item.value = action;
              }
            });
          });
        });
      });
    }

    $scope.assignAllRole = function(resource, action) {
      var name = action ? 'grant' : 'revoke';
      server.post('_all', resource.resource, resource.section, name).success(function(data) {
        if (!data.status) return;
        var roles = resource.roles;
        angular.forEach(roles, function(item) {
          item.value = action;
        });
      });
    }

    $timeout(function() {
      //Tooltip
      $('[data-toggle=tooltip]').tooltip();
    }, 1, false);
  }
]);