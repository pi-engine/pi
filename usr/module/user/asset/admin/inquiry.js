angular.module('userInquiryModule')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
      templateUrl: tpl('user-profile'),
      controller: 'ProfileCtrl'
    });

    piProvider.hashPrefix();
    piProvider.translations(config.t);
    piProvider.ajaxSetup();
  }
])
.service('server', ['$http', 'config',
  function($http, config) {
    var urlRoot = config.urlRoot;

    this.get = function(params) {
      return $http.get(urlRoot + 'profile', {
        params: params
      });
    }
  }
])
.controller('ProfileCtrl', ['$scope', 'server',
  function($scope, server) {
    $scope.entity = { field: 'name' };

    $scope.submit = function() {
      server.get($scope.entity).success(function(data) {
        var user = data.user;
        user.time_activated *= 1000;
        user.time_disabled *= 1000;

        angular.extend($scope, data);
        $scope.entity.data = '';
      });
    }
  }
]);