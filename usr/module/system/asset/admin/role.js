angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.when('/new', {
      templateUrl: tpl('role-new'),
      controller: 'RoleNewCtrl',
    }).when('/all', {
      templateUrl: tpl('roles'),
      controller: 'RolesCtrl',
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
    }).otherwise({
      redirectTo: '/all'
    });
    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
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
]).controller('RolesCtrl', ['$scope', 'data', 'server', 'config',
  function ($scope, data, server, config) {
    angular.extend($scope, data);

    $scope.$on('piHoverInputSave', function(event, data) {
      server.putTitle(data);
    });

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
]).controller('RoleNewCtrl', ['$scope', 'server',
  function($scope, server) {
    $scope.uniqueUrl = server.root + 'checkExist';

    function init() {
      $scope.entity = {
        active: 1,
        section: 'front'
      };
    }

    init();

    $scope.newAction = function() {
      server.add($scope.entity).success(function (data) {
        if (!data.status) return;
        init();
        $scope.roleForm.$setPristine(); 
      });
    }
  }
]);