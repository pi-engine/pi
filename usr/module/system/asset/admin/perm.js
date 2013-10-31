angular.module('systemPermModule')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
     function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.when('/:section', {
      templateUrl: tpl('perm'),
      controller: 'PermCtrl',
      resolve: {
        data: ['$q', '$route', 'server',
          function($q, $route, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
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
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      redirectTo: '/front'
    });
    piProvider.hashPrefix();
    piProvider.translations(config.t);
    piProvider.navTabs(config.navTabs);
    piProvider.ajaxSetup();
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
.controller('PermCtrl', ['$scope', '$location', 'server', 'config', 'data',
  function($scope, $location, server, config, data) {
    angular.extend($scope, data);

    function checkCol(role) {
      
    }

    $scope.assignAction = function(key, item) {
      var action = item.roles[key] ? 'revoke' : 'grant';
      server.post(key, item.resource, item.section, action).success(function(data) {
        if (data.status) {
          item.roles[key] = !item.roles[key];
        } 
      });
    }

    $scope.assignAllResource = function(role, action) {
      var op = action ? 'grant' : 'revoke';
      var name = role.name;
      server.post(name, '_all', role.section, op).success(function(data) {
        if (!data.status) return;
        angular.forEach($scope.resources, function(resource, key) {
          angular.forEach(resource, function(item) {
            item.roles[name] = action;
          });
        });
      });
    }

    $scope.assignAllRole = function(resource, action) {
      var name = action ? 'grant' : 'revoke';
      server.post('_all', resource.resource, resource.section, name).success(function(data) {
        if (!data.status) return;
        var roles = resource.roles;
        angular.forEach(roles, function(value, key) {
          roles[key] = action;
        });
      });
    }
  }
]);