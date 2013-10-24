systemRoleModule.config(['$translateProvider', '$routeProvider', '$locationProvider',
  function ($translateProvider, $routeProvider, $locationProvider) {
    function tpl(name) {
      return systemRoleModuleConfig.assetRoot + name + '.html';
    }
    $translateProvider.translations(systemRoleModuleConfig.t);
    $routeProvider.when('/:role/users', {
      templateUrl: tpl('role-user'),
      controller: 'UserCtrl'
    }).otherwise({
      templateUrl: tpl('role-index'),
      controller: 'RoleCtrl'
    });
    $locationProvider.hashPrefix('!');
  }
]).directive('piFocus', function () {
  return {
    restrict: 'A',
    link: function (scope, element, attr) {
      scope.$watch(attr.piFocus, function (value) {
        if (value) {
          element[0].focus();
        }
      });
    }
  }
}).service('server', function ($http) {
  var root = systemRoleModuleConfig.urlRoot;
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
  this.root = root;
  this.get = function () {
    return $http.get(root + 'list');
  }
  this.add = function (role) {
    return $http.post(root + 'add', role);
  };
  this.putTitle = function (role) {
    $http.post(root + 'rename', {
      id: role.id,
      title: role.title
    });
  };
  this.putActive = function (role) {
    return $http.post(root + 'activate', {
      id: role.id
    });
  };
  this.remove = function (role) {
    return $http.post(root + 'delete', {
      id: role.id
    });
  };
  this.getUserByRole = function(role) {
    return $http.get(root + 'user', {
      params: {
        name: role
      }
    });
  }
}).controller('RoleCtrl', function ($scope, server) {
  server.get().success(function (data) {
    var frontRoles = data.frontRoles;
    var adminRoles = data.adminRoles;
    var parse = function (item) {
      item.editTitle = 0;
      item.originTitle = item.title;
    };
    angular.forEach(frontRoles, parse);
    angular.forEach(frontRoles, parse);
    $scope.frontRoles = data.frontRoles;
    $scope.adminRoles = data.adminRoles;
  });
  $scope.uniqueUrl = server.root + 'checkExist';
  $scope.cancelModal = function () {
    $scope.entity = null;
  }
  $scope.modal = function (type) {
    $scope.entity = {
      section: type,
      active: 1
    };
  }
  $scope.addRoleAction = function () {
    server.add($scope.entity).success(function (data) {
      $scope.alert = data;
      if (data.status) {
        var role = data.data;
        role.count = role.count || 0;
        if (role.section == 'front') {
          $scope.frontRoles.push(role);
        } else {
          $scope.adminRoles.push(role);
        }
        $scope.entity = '';
      }
    });
  }
  $scope.renameAction = function (role) {
    if (role.title == '') {
      role.title = role.originTitle;
    }
    if (role.title != role.originTitle) {
      server.putTitle(role);
    }
    role.editTitle = 0;
  }
  $scope.activeAction = function (role) {
    if (!role.custom) return;
    server.putActive(role).success(function (data) {
      $scope.alert = data;
      if (data.status) {
        role.active = data.data;
      }
    });
  }
  $scope.deleteAction = function (role, index) {
    if (!confirm(systemRoleModuleConfig.t.DELETE_CONFIRM)) return;
    server.remove(role).success(function (data) {
      $scope.alert = data;
      if (data.status) {
        if (role.section == 'front') {
          $scope.frontRoles.splice(index, 1);
        } else {
          $scope.adminRoles.splice(index, 1);
        }
      }
    });
  }
  $scope.clearAlert = function () {
    $scope.alert = '';
  }
}).controller('UserCtrl', ['$scope', '$routeParams', 'server',
  function($scope, $routeParams, server) {
    var role = $routeParams.role;
    $scope.role = role;
    server.getUserByRole(role).success(function(data) {
      $scope.users = data.users;
      $scope.paginator = data.paginator;
    });
  }
]);