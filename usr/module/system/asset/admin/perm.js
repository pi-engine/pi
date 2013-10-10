systemPermModule.config(['$translateProvider',
  function ($translateProvider) {
    //Get template url
    $translateProvider.translations(systemPermModuleConfig.t);
  }
]).service('server', ['$http',
  function($http) {
    var root = systemPermModuleConfig.urlRoot;
    $http.defaults.headers.post['Content-Type'] = 
      'application/x-www-form-urlencoded;charset=utf-8';
    $http.defaults.transformRequest = [
      function (d) {
        return angular.isObject(d) ? $.param(d) : d;
      }
    ];
    this.post = function(role, resource, section, op) {
      return $http.post(root + 'assign', {
        role: role,
        resource: resource,
        section: section,
        op: op,
        name: systemPermModuleConfig.module
      });
    }
  }
]).controller('index', ['$scope', 'server',
  function($scope, server) {
    function parse() {
      var frontRoles = [];
      var adminRoles = [];
      angular.forEach(systemPermModuleConfig.roles.front, function(value, key) {
        value.name = key;
        frontRoles.push(value);
      });
      angular.forEach(systemPermModuleConfig.roles.admin, function(value, key) {
        value.name = key;
        adminRoles.push(value);
      });
      $scope.frontRoles = frontRoles;
      $scope.adminRoles = adminRoles;
      $scope.frontResources = systemPermModuleConfig.resources.front;
      $scope.adminResources = systemPermModuleConfig.resources.admin;
      $scope.frontCols = $scope.frontRoles.length + 1;
      $scope.adminCols = $scope.adminRoles.length + 1;
    }
    parse();

    $scope.clearAlert = function() {
      $scope.alert = '';
    }

    $scope.assignAction = function(key, item) {
      var action = item.roles[key] ? 'revoke' : 'grant';
      server.post(key, item.resource, item.section, action).success(function(data) {
        $scope.alert = data;
        if (data.status) {
          item.roles[key] = !item.roles[key];
        }
      }).error(function(data) {
        $scope.alert = data;
      });
    }
  }
]);