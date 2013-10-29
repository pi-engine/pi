angular.module('uclientUserModule')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.when('/?', {
      templateUrl: tpl('index-all'),
      controller: 'ListCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
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
                item.time_created *= 1000;
              })
              angular.extend(data, server.getRoles());
              data.filter = params;
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    });

    piProvider.navTabs(config.navTabs);
    piProvider.translations(config.t);
    piProvider.ajaxSetup();
  }
])
.service('server', ['$http', '$cacheFactory', 'config',
  function ($http, $cacheFactory, config) {
    var urlRoot = config.urlRoot;

    this.get = function (params) {
      return $http.get(urlRoot + 'all', {
        params: params
      });
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
      var frontRoles = [];
      var adminRoles = [{
        name: 'none',
        title: config.t.NONE_ADMIN,
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
      adminRoles.push({
        name: 'any',
        title: config.t.ANY_ADMIN,
        section: 'admin'
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
      return ids;
    }

    $scope.markAll = function () {
      angular.forEach(this.users, function (user) {
        user.checked = $scope.allChecked;
      });
    }


    $scope.assignRoleBacthAction = function() {
      var role = $scope.assignRole;
      if (!role) return;
      server.assignRole(getCheckIds(), role.name, 'add').success(function(data) {
        $scope.assignRole = '';
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function (user) {
          if (user.checked) {
            if (role.section == 'front') {
              if (user.front_roles) {
                user.front_roles += ',' + role.title;
              } else {
                user.front_roles = role.title;
              }
            }
            if (role.section == 'admin') {
              if (user.admin_roles) {
                user.admin_roles += ',' + role.title;
              } else {
                user.admin_roles = role.title;
              } 
            }
            user.checked = 0;
          }
        });
      });
    }

    $scope.unassignRoleBacthAction = function() {
      var role = $scope.unassignRole;
      if (!role) return;
      server.assignRole(getCheckIds(), role, 'remove').success(function(data) {
        $scope.unassignRole = '';
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function (user) {
          if (user.checked) {
            if (role.section == 'front' && user.front_roles) {
              user.front_roles = user.front_roles.replace(RegExp(',?' + role.title), '');
            }
            if (role.section == 'admin' && user.admin_roles) {
              user.admin_roles = user.admin_roles.replace(RegExp(',?' + role.title), '');
            }
            user.checked = 0;
          }
        });
      });
    }

    $scope.filterAction = function () {
      $location.search(server.filterEmpty($scope.filter));
      $location.search('p', null);
    }
  }
]);