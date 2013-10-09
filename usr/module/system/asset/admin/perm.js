systemPermModule.config(['$translateProvider', '$routeProvider',
  function ($translateProvider, $routeProvider) {
    //Get template url
    function tpl(name) {
      return systemPermModuleConfig.assetRoot + name + '.html';
    }
    $translateProvider.translations(systemPermModuleConfig.t);
    $routeProvider.otherwise({
      templateUrl: tpl('perm'),
      controller: 'index'
    });
  }
]).controller('index', ['$scope',
  function($scope) {
    function parse() {
      $scope.frontRoles = systemPermModuleConfig.roles.front;
      $scope.adminRoles = systemPermModuleConfig.roles.admin;
      $scope.frontResources = systemPermModuleConfig.resources.front;
      $scope.adminResources = systemPermModuleConfig.resources.admin;
    }
    parse();
    
  }
]);