angular.module('piTabs', [])
.directive('piTabs', function() {
    return {
      template:
        '<div>' + 
          '<div class="clearfix">' +
            '<h4 class="pi-nav-tabs-title" ng-if="title">{{title}}</h3>' +
            '<ul class="nav nav-tabs" ng-class="{\'pull-right\': title}">' +
              '<li ng-repeat="tab in tabs" ng-class="{active:tab.active}" ng-click="select(tab)">' +
                '<a href="javascript:void(0)">{{tab.title}}</a>' +
            '</ul>' +
          '</div>' +
          '<div class="tab-content" ng-click="test()" ng-transclude>' +
          '</div>' +
        '</div>',
      replace: true,
      scope: {},
      transclude: true,
      restrict: 'A',
      controller: 'piTabsCtrl'
    }
})
.directive('piPanel', function() {
  return {
    require: '^piTabs',
    template:
      '<div class="tab-pane" ng-transclude ng-class="{active: active}">' +
      '</div>',
      replace: true,
      transclude: true,
      restrict: 'A',
      link: function(scope, element, attrs, ctrl) {
        var tab = scope[attrs.piPanel];
        ctrl.addTab(tab);
        scope.$watch(attrs.piPanel, function(value) {
          scope.active = value.active;
        }, true);
      }
  }
})
.controller('piTabsCtrl', ['$scope', '$attrs', '$interpolate',
  function ($scope, $attrs, $interpolate) {
    var tabs = $scope.tabs = [];
    if ($attrs.tabsTitle) {
      $scope.title = $interpolate($attrs.tabsTitle)($scope.$root);
    }

    this.addTab = function(tab) {
      tabs.push(tab);
      if (tabs.length === 1 || tab.active) {
        $scope.select(tab);
      }
    }

    $scope.select = function(tab) {
      angular.forEach(tabs, function(tab) {
        tab.active = 0;
      });
      tab.active = 1;
    }
  }
]);