angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
     function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
      templateUrl: tpl('dashboard'),
      controller: 'DashboardCtrl'
    });
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.directive('noticeFocus', ['$timeout',
  function($timeout) {
    return function(scope, elem, attrs) {
      scope.$watch(attrs.noticeFocus, function (newVal) {
        if (newVal) {
          $timeout(function () {
            elem[0].focus();
          }, 0, false);
        }
      });
    }
  }
])
.service('server', ['$http', 'config',
  function($http, config) {
    var urlRoot = config.urlRoot;

    this.data = config.data;

    this.saveNotice = function(content) {
      return $http.post(urlRoot + 'message', {
        content: content
      });
    }

    this.saveLinks = function(links) {
      return $http.post(urlRoot + 'link', {
        content: links
      });
    }
  }
])
.controller('DashboardCtrl', ['$scope', 'server',
  function($scope, server) {
    angular.extend($scope, server.data);

    $scope.messageContentCopy = $scope.message.content;

    $scope.saveNoticeAction = function() {
      server.saveNotice($scope.messageContentCopy).success(function(data) {
        $scope.message = data;
        $scope.noticeEditing = 0;
        $scope.messageContentCopy = data.message.content;
      });
    }

    $scope.removeLink = function(idx) {
      $scope.links.splice(idx, 1);
    }

    $scope.addLink = function() {
      var item = angular.copy($scope.linkEntity);
      $scope.links.push(item);
      $scope.linkEntity = {};
    }

    $scope.saveLinksAction = function() {
      server.saveLinks($scope.links).success(function(data) {
        $scope.linksEditing = 0;
        $scope.linkAddStatus = 0;
      });
    }
  }
]);