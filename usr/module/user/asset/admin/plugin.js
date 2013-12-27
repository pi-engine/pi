angular.module('user')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    function resolve(action) {
      return {
        data: ['$q', '$rootScope', 'server',
          function($q, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server[action].get().success(function (data) {
              var ret = {};
              ret.displayList = data.display_list;
              ret.selectList = data.select_list;
              ret.action = action;
              deferred.resolve(ret);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ] 
      }
    }

    $routeProvider.when('/activity', {
      templateUrl: tpl('plugin-activity'),
      controller: 'SortableCtrl',
      resolve: resolve('activity')
    }).when('/quicklink', {
      templateUrl: tpl('plugin-quicklink'),
      controller: 'SortableCtrl',
      resolve: resolve('quicklink')
    }).otherwise({
      redirectTo: '/timeline',
      templateUrl: tpl('plugin-timeline'),
      controller: 'TimelineCtrl'
    });
    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
]).service('server', ['$http', 'config',
  function ($http, config) {
    var urlRoot = config.urlRoot;
    this.timeline = {
      get: function (page) {
        return $http.get(urlRoot + 'timeline', {
          params: {
            p: page || 1
          }
        });
      },
      put: function (item) {
        return $http.post(urlRoot + 'toggleTimelineDisplay', {
          id: item.id
        });
      }
    };

    this.quicklink = {
      get: function () {
        return $http.get(urlRoot + 'quicklink');
      },
      put: function (ids) {
        return $http.post(urlRoot + 'dressUpQuicklink', {
          ids: ids
        });
      }
    };

    this.activity = {
      get: function () {
        return $http.get(urlRoot + 'activity');
      },
      put: function (ids) {
        return $http.post(urlRoot + 'dressUpActivity', {
          ids: ids
        });
      }
    };
  }
]).controller('TimelineCtrl', ['$scope', 'server',
  function ($scope, server) {
    $scope.paginator = {
      page: 1
    };

    $scope.$watch('paginator.page', function (page) {
      server.timeline.get(page).success(function (data) {
        $scope.timeline = data.timeline;
        $scope.paginator = data.paginator;
      });
    });

    $scope.disableAction = function (item) {
      if (!item.active) return;
      server.timeline.put(item).success(function (data) {
        if (data.status) item.active = 0;
      });
    }

    $scope.activeAction = function (item) {
      if (item.active) return;
      server.timeline.put(item).success(function (data) {
        if (data.status) item.active = 1;
      });
    }

  }
]).controller('SortableCtrl', ['$scope', 'data', 'server',
  function ($scope, data, server) {
    angular.extend($scope, data);

    function getDisplay() {
      var ids = [];
      var list = $scope.displayList;
      angular.forEach(list, function (item) {
        ids.push(item.id);
      });
      ids = ids.join(',');
      return ids;
    }

    $scope.removeDisplay = function (idx) {
      var item = $scope.displayList[idx];
      $scope.displayList.splice(idx, 1);
      $scope.selectList.push(item);
    }

    $scope.addDisplay = function (idx) {
      var item = $scope.selectList[idx];
      $scope.selectList.splice(idx, 1);
      $scope.displayList.push(item);
    }

    $scope.$watch('displayList', function(newValue, oldValue) {
      if (newValue === oldValue) return;
      server[$scope.action].put(getDisplay());
    }, true);
  }
]);