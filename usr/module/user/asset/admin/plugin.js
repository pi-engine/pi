angular.module('userPluginModule')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }
    $routeProvider.when('/activity', {
      templateUrl: tpl('plugin-activity'),
      controller: 'SortableCtrl'
    }).when('/quicklink', {
      templateUrl: tpl('plugin-quicklink'),
      controller: 'SortableCtrl'
    }).otherwise({
      redirectTo: '/timeline',
      templateUrl: tpl('plugin-timeline'),
      controller: 'TimelineCtrl'
    });
    piProvider.hashPrefix();
    piProvider.navTabs(config.navTabs);
    piProvider.translations(config.t);
    piProvider.ajaxSetup();
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
]).controller('SortableCtrl', ['$scope', '$location', 'server',
  function ($scope, $location, server) {
    var action = $location.path().replace(/^\//, '');
    server[action].get().success(function (data) {
      $scope.displayList = data.display_list;
      $scope.selectList = data.select_list;
    });

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
      server[action].put(getDisplay());
    }

    $scope.addDisplay = function (idx) {
      var item = $scope.selectList[idx];
      $scope.selectList.splice(idx, 1);
      $scope.displayList.push(item);
      server[action].put(getDisplay());
    }

    $('#js-plugin-sortable').sortable({
      start: function (e, ui) {
        ui.item.data('start', ui.item.index());
        ui
          .helper
          .outerWidth(ui.item.outerWidth());
      },
      update: function (e, ui) {
        var start = ui.item.data('start');
        var end = ui.item.index();
        var list = $scope.displayList;
        list.splice(end, 0,
          list.splice(start, 1)[0]);
        $scope.$apply();
        server[action].put(getDisplay());
      }
    });
  }
]);