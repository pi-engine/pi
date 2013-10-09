systemUserModule.config(['$translateProvider', '$routeProvider', '$locationProvider',
  function ($translateProvider, $routeProvider, $locationProvider) {
    //Get template url
    function tpl(name) {
      return systemUserModuleConfig.assetRoot + name + '.html';
    }
    $translateProvider.translations(systemUserModuleConfig.t);
    $routeProvider.when('/index', {
      templateUrl: tpl('user-index'),
      controller: 'UserCtrl'
    }).when('/new', {
      templateUrl: tpl('user-form'),
      controller: 'formCtrl'
    }).when('/edit/:id', {
      templateUrl: tpl('user-form-edit'),
      controller: 'formEditCtrl',
      resolve: {
        user: ['$q', '$route', 'server',
          function ($q, $route, server) {
            var deferred = $q.defer();
            var id = $route.current.params.id;
            server.getUserById(id).success(function (data) {
              deferred.resolve(data);
            });
            return deferred.promise;
          }
      ]}
    }).otherwise({
      redirectTo: '/index',
    });
    $locationProvider.hashPrefix('!');
  }
]).service('server', ['$http', '$cacheFactory', 
  function ($http, $cacheFactory) {
    var root = systemUserModuleConfig.urlRoot;
    var isFile = function (obj) {
      return Object.prototype.toString.apply(obj) === '[object File]';
    };
    //emulate jQuery post
    $http.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8';
    $http.defaults.transformRequest = [
      function (d) {
        return angular.isObject(d) && !isFile(d) ? $.param(d) : d;
      }
    ];
    this.roles = systemUserModuleConfig.roles;
    this.uniqueUrl = root + 'checkExist';
    this.get = function (params) {
      return $http.get(root + 'list', {
        params: params
      });
    }
    this.getUserById = function (id) {
      return $http.get(root + 'getUser', {
        params: {
          id: id
        }
      })
    }
    this.post = function (entity) {
      return $http.post(root + 'addUser', entity);
    }
    this.put = function (entity) {
      return $http.post(root + 'updateUser', entity);
    }
    this.remove = function(entity) {
      return $http.post(root + 'delete', {
        id: entity.id
      });
    }
    this.clearCache = function() {
      $cacheFactory.get('$http').removeAll();
    }
  }
]).controller('UserCtrl', ['$scope', '$routeParams', 'server', 
  function ($scope, $routeParams, server) {
    $scope.roles = server.roles;
    $scope.filter = {};
    $scope.paginator = {
      page: 1
    };
    var list = function (num) {
      var params = angular.copy($scope.filter);
      params.p = num || 1;
      if (params.role) {
        if (params.role.type == 'front') {
          params.front_role = params.role.name;
        } else {
          params.admin_role = params.role.name;
        }
        delete params.role;
      }
      server.get(params).success(function (data) {
        angular.forEach(data.users, function (item) {
          item.time_created *= 1000;
          if (item.front_roles) {
            item.front_roles = item.front_roles.join(',');
          }
          if (item.admin_roles) {
            item.admin_roles = item.admin_roles.join(',');
          }
        });

        $scope.users = data.users;
        $scope.paginator = data.paginator;
      });
    }
    $scope.$watch('paginator.page', list);
    $scope.filterAction = list;

    $scope.deleteAction = function(idx) {
      var user = $scope.users[idx];
      if (!confirm()) return;
      server.remove(user).success(function(data) {
        if (data.status) {
          $scope.users.splice(idx, 1);
        }
      });
    }
  }
]).controller('formCtrl', ['$scope', 'server',
  function ($scope, server) {
    $scope.entity = {
      activated: 1,
      enable: 1,
      roles: ['member']
    };
    $scope.roles = server.roles;
    $scope.uniqueUrl = server.uniqueUrl;
    angular.forEach($scope.roles, function (item) {
      if ($scope.entity.roles.indexOf(item.name) != -1) {
        item.checked = true;
      }
    });
    $scope.submit = function () {
      server.post($scope.entity).success(function (data) {
        $scope.alert = data;
        server.clearCache();
      });
    }
    $scope.clearAlert = function () {
      $scope.alert = '';
    }
    $scope.$watch('roles', function () {
      var roles = [];
      angular.forEach($scope.roles, function (item) {
        if (item.checked) {
          roles.push(item.name)
        }
      });
      $scope.entity.roles = roles;
    }, true);
  }
]).controller('formEditCtrl', ['$scope', 'server', 'user',
  function ($scope, server, user) {
     function parse() {
      var front_roles = user.front_roles || [];
      var admin_roles = user.admin_roles || [];
      var roles = front_roles.concat(admin_roles);
      user.activated = user.time_activated ? 1 : 0;
      user.enable = user.time_disabled ? 0 : 1;
      angular.forEach(server.roles, function (item) {
        if (roles.indexOf(item.name) != -1) {
          item.checked = true;
        }
      });
      $scope.roles = server.roles;
      $scope.entity = user;
      $scope.uniqueUrl = server.uniqueUrl + '?id=' + user.id;
    };
    //Parse data to adapt need
    parse();
    $scope.submit = function () {
      server.put($scope.entity).success(function (data) {
        $scope.alert = data;
        server.clearCache();
      });
    }
    $scope.clearAlert = function () {
      $scope.alert = '';
    }
    $scope.$watch('roles', function () {
      var roles = [];
      angular.forEach($scope.roles, function (item) {
        if (item.checked) {
          roles.push(item.name)
        }
      });
      $scope.entity.roles = roles;
    }, true);
  }
]);