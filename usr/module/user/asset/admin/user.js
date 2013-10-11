userListModule.config(['$translateProvider', '$routeProvider', '$locationProvider',
  function ($translateProvider, $routeProvider, $locationProvider) {
    //Get template url
    function tpl(name) {
      return userListModuleConfig.assetRoot + name + '.html';
    }
    $translateProvider.translations(userListModuleConfig.t);
    $locationProvider.hashPrefix('!');
    $routeProvider.when('/activated', {
      templateUrl: tpl('index-activated'),
      controller: 'ListCtrl'
    }).when('/pending', {
      templateUrl: tpl('index-pending'),
      controller: 'ListCtrl'
    }).when('/new', {
      templateUrl: tpl('index-new'),
      controller: 'NewCtrl'
    }).otherwise({
      redirectTo: '/all',
      templateUrl: tpl('index-all'),
      controller: 'ListCtrl'
    });
  }
]).service('server', ['$http', '$cacheFactory',
  function ($http, $cacheFactory) {
    var urlRoot = userListModuleConfig.urlRoot;

    this.get = function (action, params) {
      return $http.get(urlRoot + action, {
        params: params || ''
      });
    }

    this.getRoles = function () {
      var frontRoles = [];
      var adminRoles = [];
      angular.forEach(userListModuleConfig.roles, function(item) {
        if (item.type == 'front') {
          frontRoles.push(item);
        }
        if (item.type == 'admin') {
          adminRoles.push(item);
        }
      });
      return {
        'frontRoles': frontRoles,
        'adminRoles': adminRoles,
        'roles': userListModuleConfig.roles
      };
    }

    this.parse = function (data) {
      var users = data.users;
      for (var i = 0, l = users.length; i < l; i++) {
        var item = users[i];
        item.time_disabled *= 1000;
        item.time_created *= 1000;
        item.time_activated *= 1000;
        item.checked = 0;
        if (item.front_roles) {
          item.front_roles = item.front_roles.join(',');
        }
        if (item.admin_roles) {
          item.admin_roles = item.admin_roles.join(',');
        }
      }
    }

    this.disable = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'disable', {
        ids: ids
      });
    }

    this.enable = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'enable', {
        ids: ids
      });
    }

    this.active = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'activateUser', {
        ids: ids
      });
    }

    this.remove = function (ids) {
      if (angular.isArray(ids)) {
        ids = ids.join(',');
      }
      return $http.post(urlRoot + 'deleteUser', {
        ids: ids
      });
    }

    this.add = function (params) {
      return $http.post(urlRoot + 'addUser', params);
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

    this.uniqueUrl = urlRoot + 'checkExist';
  }
]).controller('MainCtrl', ['$scope', '$location',
  function ($scope, $location) {
    $scope.navClass = function (path) {
      if ($location.path().substr(0, path.length) == path) {
        return "active";
      } else {
        return "";
      }
    }
  }
]).controller('ListCtrl', ['$scope', '$location', 'server',
  function ($scope, $location, server) {
    var action = $location.path().replace(/^\//, '');
    $scope.paginator = {
      page: 1
    };
    $scope.$watch('paginator.page', function (num) {
      var param = { p: num };
      angular.extend(param, $scope.filter);
      server.get(action, param).success(function (data) {
        server.parse(data);
        $scope.users = data.users;
        $scope.paginator = data.paginator;
      });
    });
    angular.extend($scope, server.getRoles());

    function getCheckIds() {
      var ids = [];
      angular.forEach($scope.users, function (user) {
        if (user.checked) {
            ids.push(user.id);
        }
      });
      return ids;
    }

    $scope.markAll = function (checked) {
      angular.forEach(this.users, function (user) {
        user.checked = checked;
      });
    }

    $scope.disableBatchAction = function () {
      var users = $scope.users;
      server.disable(getCheckIds()).success(function (data) {
        $scope.alert = data;
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach(users, function (user) {
            if (user.checked) {
              user.time_disabled = 1;
              user.active = 0;
              user.checked = 0;
            }
          });
        }
      });
    }

    $scope.enableAction = function(user) {
      if (user.time_disabled) {
        server.enable(user.id).success(function (data) {
          $scope.alert = data;
          if (data.status) {
            user.time_disabled = 0;
            if (user.time_activated) user.active = 1;
          }
        });
      } else {
        server.disable(user.id).success(function (data) {
          $scope.alert = data;
          if (data.status) {
            user.time_disabled = 1;
            user.active = 0;
          }
        });
      }
    }
   
    $scope.enableBatchAction = function () {
      var users = $scope.users;
      server.enable(getCheckIds()).success(function (data) {
        $scope.alert = data;
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach(users, function (user) {
            if (user.checked) {
              user.time_disabled = 0;
              if (user.time_activated) user.active = 1;
              user.checked = 0;
            }
          });
        }
      });
    }

    $scope.activeAction = function (user) {
      if (user.time_activated) return;
      server.active(user.id).success(function (data) {
        $scope.alert = data;
        if (data.status) {
          user.time_activated = 1;
        }
      });
    }

    $scope.activeBatchAction = function () {
      server.active(getCheckIds()).success(function (data) {
        $scope.alert = data;
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach($scope.users, function (user) {
            if (user.checked) {
              user.time_activated = 1;
              user.checked = 0;
            }
          });
        }
      });
    }

    $scope.deleteAction = function (idx) {
      if (!confirm(userListModuleConfig.t.CONFIRM)) return;
      var users = this.users
      var user = users[idx];
      server.remove(user.id).success(function (data) {
        $scope.alert = data;
        if (data.status) {
          users.splice(idx, 1);
        }
      });
    }

    $scope.deleteBatchAction = function () {
      if (!confirm(userListModuleConfig.t.CONFIRMS)) return;
      server.remove(getCheckIds()).success(function (data) {
        var ret = [];
        $scope.alert = data;
        if (data.status) {
          $scope.allChecked = 0;
          angular.forEach($scope.users, function (user) {
            !user.checked && ret.push(user);
          });
          $scope.users = ret;
        }
      });
    }

    $scope.assignRoleBacthAction = function() {
      var role = $scope.assignRole;
      if (!role) return;
      server.assignRole(getCheckIds(), role.name, 'add').success(function(data) {
        $scope.alert = data;
        $scope.assignRole = '';
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function (user) {
          if (user.checked) {
            if (role.type == 'front') {
              if (user.front_roles) {
                user.front_roles += ',' + role.name;
              } else {
                user.front_roles = role.name;
              }
            }
            if (role.type == 'admin') {
              if (user.admin_roles) {
                user.admin_roles += ',' + role.name;
              } else {
                user.admin_roles = role.name;
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
        $scope.alert = data;
        $scope.unassignRole = '';
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function (user) {
          if (user.checked) {
            if (role.type == 'front' && user.front_roles) {
              user.front_roles = user.front_roles.replace(RegExp(',?' + role.name), '');
            }
            if (role.type == 'admin' && user.admin_roles) {
              user.admin_roles = user.admin_roles.replace(RegExp(',?' + role.name), '');
            }
            user.checked = 0;
          }
        });
      });
    }

    $scope.filterAction = function () {
      server.get('all', $scope.filter).success(function (data) {
        $scope.users = data.users;
        $scope.paginator = data.paginator;
      });
    }
  }
]).controller('NewCtrl', ['$scope', 'server',
  function ($scope, server) {
    var entity = {
      activated: 1,
      enable: 1,
      roles: ['member']
    };
    $scope.entity = angular.copy(entity);
    $scope.uniqueUrl = server.uniqueUrl;
    $scope.roles = server.getRoles().roles;
    angular.forEach($scope.roles, function (item) {
      if ($scope.entity.roles.indexOf(item.name) != -1) {
        item.checked = true;
      }
    });
    
    $scope.submit = function () {
      server.add($scope.entity).success(function (data) {
        $scope.alert = data;
        if (data.status) {
          $scope.entity = angular.copy(entity);
        }
      });
    }

    $scope.$watch('roles', function () {
      var roles = [];
      angular.forEach($scope.roles, function (item) {
        if (item.checked) {
          roles.push(item.name);
        }
      });
      $scope.entity.roles = roles;
    }, true);
  }
]);