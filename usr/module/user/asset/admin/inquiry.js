angular.module('user')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.when('/:field/:data', {
      templateUrl: tpl('user-profile'),
      controller: 'SearchCtrl',
      resolve: {
        data: ['$q', '$route', '$rootScope', 'server',
          function($q, $route, $rootScope, server) {
            var deferred = $q.defer();
            var params = $route.current.params;
            $rootScope.alert = 2;
            server.get(params).success(function(data) {
              data.entity = params;
              deferred.resolve(data);
              $rootScope.alert = '';
            }).error(function(data) {
              deferred.resolve({
                entity: params,
                userNone: data.message
              });
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      templateUrl: tpl('user-profile'),
      controller: 'IndexCtrl'
    });

    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.factory('server', ['$http', 'config',
  function($http, config) {
    var urlRoot = config.urlRoot;

    return {
      get: function(params) {
        return $http.get(urlRoot + 'profile', {
          params: params
        });
      }
    }
  }
])
.controller('IndexCtrl', ['$scope', '$location',
  function($scope, $location) {
    $scope.entity = { field: 'name' };

    $scope.submit = function() {
      var entity = $scope.entity;
      var path = '/' + entity.field + '/' + entity.data;

      $location.path(path);
    }
  }
])
.controller('SearchCtrl', ['$scope', '$location', 'data',
  function($scope, $location, data) {
    angular.extend($scope, data);

    $scope.submit = function() {
      var entity = $scope.entity;
      var path = '/' + entity.field + '/' + entity.data;

      $location.path(path);
    }
  }
]);