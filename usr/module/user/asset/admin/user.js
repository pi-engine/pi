angular.module('user')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    function resolve(action) {
      return {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
            $rootScope.alert = 2;
            server.get(action, params).success(function(data) {
              var users = data.users;
              for (var i = 0, l = users.length; i < l; i++) {
                var item = users[i];
                item.checked = 0;
                if (item.front_roles) {
                  item.front_roles = item.front_roles.join(',');
                }
                if (item.admin_roles) {
                  item.admin_roles = item.admin_roles.join(',');
                }
                item.editUrl = config.editUrlRoot + 'index/uid/' + item.id;
              }
              angular.extend(data, server.getRoles());
              data.filter = params;
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          } 
        ]
      };
    }

    $routeProvider.when('/all', {
      templateUrl: tpl('index-all'),
      controller: 'ListCtrl',
      resolve: resolve('all')
    }).when('/activated', {
      templateUrl: tpl('index-activated'),
      controller: 'ListCtrl',
      resolve: resolve('activated')
    }).when('/pending', {
      templateUrl: tpl('index-pending'),
      controller: 'ListCtrl',
      resolve: resolve('pending')
    }).when('/new', {
      templateUrl: tpl('index-new'),
      controller: 'NewCtrl'
    }).when('/search', {
      templateUrl: tpl('advanced-search'),
      controller: 'SearchCtrl'
    }).when('/all/search', {
      templateUrl: tpl('advanced-search-result'),
      controller: 'ListCtrl',
      resolve: resolve('search')
    }).otherwise({
      redirectTo: '/all'
    });

    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.service('server', ['$http', '$cacheFactory', 'config',
  function ($http, $cacheFactory, config) {
    var urlRoot = config.urlRoot;

    this.get = function (action, params) {
      return $http.get(urlRoot + action, {
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
      var adminRoles = [];
      var assignRoles = [];
      angular.forEach(config.roles, function(item) {
        if (item.type == 'front') {
          frontRoles.push(item);
        }
        if (item.type == 'admin') {
          adminRoles.push(item);
        }
        if (item.name != 'member') {
          assignRoles.push(item);
        }
      });
      return {
        'frontRoles': frontRoles,
        'adminRoles': adminRoles,
        'roles': config.roles,
        'assignRoles': assignRoles
      };
    }

    this.roles = config.roles;

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

    this.advanceSearch = function(params) {
      return $http.get(urlRoot + 'search', {
        params: params
      });
    }

    this.uniqueUrl = urlRoot + 'checkExist';
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

    function handleStatus(users, data) {
      if (!data.status) return;
      var status = data.users_status;
      var handleItem = function(item) {
        var ret = status[item.id];
        item.active = ret.active;
        item.time_disabled = ret.disabled;
        item.time_activated = ret.activated;
        item.checked = 0;
      };
      if (angular.isArray(users)) {
        $scope.allChecked = 0;
        angular.forEach(users, function (user) {
          if(!user.checked) return;
          handleItem(user);
        });
      } else {
        handleItem(users);
      }
    }

    $scope.markAll = function () {
      angular.forEach($scope.users, function (user) {
        user.checked = $scope.allChecked;
      });
    }

    $scope.disableBatchAction = function () {
      var ids = getCheckIds();
      if (!ids.length) return;
      server.disable(ids).success(function (data) {
        handleStatus($scope.users, data);
      });
    }

    $scope.enableAction = function(user) {
      if (user.time_disabled) {
        server.enable(user.id).success(function (data) {
          handleStatus(user, data);
        });
      } else {
        server.disable(user.id).success(function (data) {
          handleStatus(user, data);
        });
      }
    }
   
    $scope.enableBatchAction = function () {
      var ids = getCheckIds();
      if (!ids.length) return;
      server.enable(ids).success(function (data) {
        handleStatus($scope.users, data);
      });
    }

    $scope.activeAction = function (user) {
      if (user.time_activated) return;
      if (!confirm(config.t.CONFIRM_ACTIVATED)) return;
      server.active(user.id).success(function (data) {
        handleStatus(user, data);
      });
    }

    $scope.activeBatchAction = function () {
      var ids = getCheckIds();
      if (!ids.length) return;
      if (!confirm(config.t.CONFIRM_ACTIVATED_BATCH)) return;
      server.active(ids).success(function (data) {
        handleStatus($scope.users, data);
      });
    }

    $scope.deleteAction = function (idx) {
      if (!confirm(config.t.CONFIRM)) return;
      var users = this.users
      var user = users[idx];
      server.remove(user.id).success(function (data) {
        if (data.deleted_uids) {
          users.splice(idx, 1);
        }
      });
    }

    $scope.deleteBatchAction = function () {
      var ids = getCheckIds();
      if (!ids.length) return;
      if (!confirm(config.t.CONFIRMS)) return;
      server.remove(ids).success(function (data) {
        var ret = [];
        var ids = data.deleted_uids || [];
        $scope.allChecked = 0;
        angular.forEach($scope.users, function (user) {
          if (ids.indexOf(user.id) == -1) {
            ret.push(user);
            user.checked = 0;
          }
        });
        $scope.users = ret;
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
      server.assignRole(ids, role, 'remove').success(function(data) {
        if (!data.status) return;
        $scope.allChecked = 0;
        angular.forEach($scope.users, function(user) {
          handleCheckedRole(user, data.data[user.id]);
        });
      });
    }

    $scope.filterAction = function () {
      $location.search(server.filterEmpty($scope.filter));
      $location.search('p', null);
    }
  }
])
.controller('NewCtrl', ['$scope', 'server',
  function ($scope, server) {
    var entity = {
      activated: 1,
      enable: 1,
      roles: ['member']
    };
    function setRole() {
      angular.forEach($scope.roles, function (item) {
        if (entity.roles.indexOf(item.name) != -1) {
          item.checked = true;
        } else {
          item.checked = false;
        }
      });
    }
    
    $scope.entity = angular.copy(entity);
    $scope.uniqueUrl = server.uniqueUrl;
    $scope.roles = angular.copy(server.roles);
    setRole();

    $scope.submit = function () {
      server.add($scope.entity).success(function(data) {
        if (!data.status) return;
        $scope.entity = angular.copy(entity);
        $scope.userForm.$setPristine();
        setRole();
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
])
.controller('SearchCtrl', ['$scope', '$location', '$filter', 'config', 'server',
  function($scope, $location, $filter, config, server) {
    $scope.roles = angular.copy(server.roles);
    $scope.today = config.today;
    $scope.filter = {};

    $scope.$watch('roles', function(newValue, oldValue) {
      if (newValue === oldValue) {
        angular.forEach(newValue, function(item) {
          if (item.name == 'member') {
            item.checked = true;
          }
        });
        return;
      }
      var front_role = [];
      var admin_role = [];
      var filter = $scope.filter;
      angular.forEach(newValue, function(item) {
        if (item.checked) {
          if (item.type == 'front') {
            front_role.push(item.name);
          } else {
            admin_role.push(item.name);
          }
        }
      });
      if (front_role.length) {
        filter.front_role = front_role.join(',');
      }
      if (admin_role.length) {
        filter.admin_role = admin_role.join(',');
      }
    }, true);

    $scope.submit = function() {
      var filter = angular.copy($scope.filter);
      var parse = function(time) {
        return $filter('date')(time, 'yyyy-M-d');
      }

      if (filter.time_created_from) {
        filter.time_created_from = parse(filter.time_created_from);
      }

      if (filter.time_created_to) {
        filter.time_created_to = parse(filter.time_created_to);
      }
     
      $location.path('/all/search').search(filter);
    }
  }
]);