angular.module('systemRoleModule')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
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

    piProvider.translations(config.t);
    piProvider.ajaxSetup();
  }
])
.service('server', ['$http', 'config', 
  function ($http, config) {
    var root = config.urlRoot;

    this.root = root;

    this.get = function () {
      return $http.get(root + 'list');
    }

    this.add = function (role) {
      return $http.post(root + 'add', role);
    }

    this.putTitle = function (role) {
      $http.post(root + 'rename', {
        id: role.id,
        title: role.title
      });
    }

    this.putActive = function (role) {
      return $http.post(root + 'activate', {
        id: role.id
      });
    }

    this.remove = function (role) {
      return $http.post(root + 'delete', {
        id: role.id
      });
    }
  }
]).controller('RoleCtrl', ['$scope', 'data', 'server', 'config',
  function ($scope, data, server, config) {
    angular.extend($scope, data);

    $scope.uniqueUrl = server.root + 'checkExist';

    $scope.$on('piHoverInputSave', function(event, data) {
      server.putTitle(data);
    });

    $scope.cancelModal = function () {
      $scope.entity = '';
    }

    $scope.modal = function (type) {
      $scope.entity = {
        section: type,
        active: 1
      };
    }

    $scope.addRoleAction = function () {
      server.add($scope.entity).success(function (data) {
        if (!data.status) return; 
        var role = data.data;
        $scope.roles.push(role);
        $scope.entity = '';
      });
    }
  
    $scope.activeAction = function (role) {
      if (!role.custom) return;
      server.putActive(role).success(function (data) {
        if (!data.status) return;
        role.active = data.data;
      });
    }

    $scope.deleteAction = function (role) {
      if (!confirm(config.t.DELETE_CONFIRM)) return;
      server.remove(role).success(function (data) {
        if (!data.status) return; 
        var roles = $scope.roles;
        var idx;
        for (var i = 0, l = roles.length; i < l; i++) {
          if (roles[i] === role) {
            idx = i;
            break;
          }
        }
        roles.splice(idx, 1);
      });
    }
  }
]);