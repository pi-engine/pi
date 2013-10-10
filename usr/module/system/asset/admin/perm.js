systemPermModule.config(['$translateProvider',
  function ($translateProvider) {
    //Get template url
    $translateProvider.translations(systemPermModuleConfig.t);
  }
]).service('server', ['$http',
  function($http) {
    var root = systemPermModuleConfig.urlRoot;
    this.post = function(role, resource, section, op) {
      return $http.post(root + 'assign', {
        role: role,
        resource: resource,
        section: section,
        op: op,
        name: systemPermModuleConfig.module
      })
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
      $scope.frontCols = $scope.frontRoles.length + 2;
      $scope.adminCols = $scope.adminRoles.length + 2;
    }

    function error() {
      $scope.alert = {
        status: 0,
        message: systemPermModuleConfig.t.ERROR
      }
    }

    function checkCol(role) {
      var resources;
      if (role.section == 'front') {
        resources = $scope.frontResources;
      } else {
        resources = $scope.adminResources;
      }
      angular.forEach(resources, function(value, key) {
        angular.forEach(value, function(child) {
          child.roles[role.name] = role._all;
        });
      });
    }

    function checkRow() {

    }

    parse();

    $scope.assignAction = function(key, item) {
      var action = item.roles[key] ? 'revoke' : 'grant';
      server.post(key, item.resource, item.section, action).success(function(data) {
        $scope.alert = data;
        if (data.status) {
          item.roles[key] = !item.roles[key];
        } 
      }).error(error);
    }

    $scope.assignAllResource = function(role, action) {
      var action = action ? 'grant' : 'revoke';
      server.post(role.name, '_all', role.section, action).success(function(data) {
        $scope.alert = data;
        if (data.status) {
          location.href = location.href;
        }
      }).error(error);
    }

    $scope.assignAllRole = function(resource, action) {
      var action = action ? 'grant' : 'revoke';
      server.post('_all', resource.resource, resource.section, action).success(function(data) {
        $scope.alert = data;
        if (data.status) {
          location.href = location.href;
        }
      }).error(error);
    }
  }
]);