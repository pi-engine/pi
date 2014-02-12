angular.module('uclient')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.when('/:action', {
      templateUrl: tpl('index-all'),
      controller: 'ListCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = angular.copy($route.current.params);
            $rootScope.alert = 2;
            server.get(params).success(function(data) {
              var users = data.users;
              angular.forEach(users, function(item) {
                if (item.front_roles) {
                  item.front_roles = item.front_roles.join(',');
                }
                if (item.admin_roles) {
                  item.admin_roles = item.admin_roles.join(',');
                }
                //item.time_created *= 1000;
              })
              angular.extend(data, server.getRoles());
              if (params.action == 'remote') {
                data.filterRemote = params;
              } else {
                data.filterLocal = params;
              }
              delete params.action;
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      redirectTo: '/remote'
    });

    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.service('server', ['$http', '$cacheFactory', 'config',
  function ($http, $cacheFactory, config) {
    var urlRoot = config.urlRoot;

    this.get = function (params) {
      if (params.action == 'remote') {
        return $http.get(urlRoot + 'all', {
          params: params
        });
      } else {
        return $http.get(urlRoot + 'role', {
          params: params
        });
      }
    }

    this.filterEmpty = function(obj) {
      var search = {};
      for (var i in obj) {
        if (obj[i]) {
          search[i] = obj[i];
        }
      }
      return search;
    }

    this.getRoles = function () {
        var frontRoles = [{
            name: 'any_front',
            title: config.t.ANY_ROLE,
            section: 'front'
        }];
        var adminRoles = [{
            name: 'any_admin',
            title: config.t.ANY_ROLE,
            section: 'admin'
        }];
      angular.forEach(config.roles, function(item) {
        if (item.section == 'front') {
          frontRoles.push(item);
          item._section = config.t.FRONT;
        }
        if (item.section == 'admin') {
          adminRoles.push(item);
          item._section = config.t.ADMIN;
        }
      });
      return {
        'frontRoles': frontRoles,
        'adminRoles': adminRoles,
        'roles': config.roles
      };
    }

    this.assignRole = function(ids, role, op) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'assignRole', {
        ids: ids,
        role: role,
        type: op
      });
    }
  }
])
.controller('ListCtrl', ['$scope', '$location', 'data', 'config', 'server', 
  function ($scope, $location, data, config, server) {
    angular.extend($scope, data);

    $scope.$watch('paginator.page', function (newValue, oldValue) {
      if(newValue === oldValue) return;
      $location.search('p', newValue);
    });

    function getCheckIds() {
      var ids = [];
      angular.forEach($scope.users, function (user) {
        if (user.checked) {
            ids.push(user.id);
        }
      });
      if (!ids.length) $scope.$parent.alert = { status: 0, message: config.t.BATCH_CHECKED };
      return ids;
    }

    function handleCheckedRole(item, role) {
      if (item.checked) {
          if (role.front_roles) {
            item.front_roles = role.front_roles.join(',');
          } else {
            item.front_roles = '';
          }
          if (role.admin_roles) {
            item.admin_roles = role.admin_roles.join(',');
          } else {
            item.admin_roles = '';
          }
        item.checked = 0;
      }
    }

    $scope.markAll = function () {
      angular.forEach(this.users, function (user) {
        user.checked = $scope.allChecked;
      });
    }


    $scope.assignRoleBacthAction = function() {
      var role = $scope.assignRole;
      var ids = getCheckIds();
      $scope.assignRole = '';
      if (!ids.length) return;
      if (!role) return;
      server.assignRole(ids, role.name, 'add').success(function(data) {
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function(user) {
          handleCheckedRole(user, data.data[user.id]);
        });
      });
    }

    $scope.unassignRoleBacthAction = function() {
      var role = $scope.unassignRole;
      var ids = getCheckIds();
      $scope.unassignRole = '';
      if (!ids.length) return;
      if (!role) return;
      server.assignRole(ids, role.name, 'remove').success(function(data) {
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function(user) {
          handleCheckedRole(user, data.data[user.id]);
        });
      });
    }

    $scope.filterRemoteAction = function () {
      $location
        .path('/remote')
        .search(server.filterEmpty($scope.filterRemote))
        .search('p', null);
    }

    $scope.filterLocalAction = function() {
      $location
        .path('local')
        .search(server.filterEmpty($scope.filterLocal))
        .search('p', null);
    }
  }
]);