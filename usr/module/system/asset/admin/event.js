angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
     function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
      templateUrl: tpl('event'),
      controller: 'PermCtrl'
    });
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.service('server', ['$http', 'config',
  function($http, config) {
    var urlRoot = config.urlRoot;
    function parseEvents() {
      var ret = [];
      angular.forEach(config.events, function(value, key) {
        value['name'] = key;
        value['type'] = 'event';
        ret.push(value);
      });
      return ret;
    }

    this.data = {
      events: parseEvents(config.events),
      listeners: config.listeners
    };

    this.toggleAction = function(id, type) {
      return $http.post(urlRoot + 'active', {
        id: id,
        type: type
      })
    }
  }
])
.controller('PermCtrl', ['$scope', 'server',
  function($scope, server) {
   angular.extend($scope, server.data);
   
   $scope.toggleAction = function(item, type) {
    server.toggleAction(item.id, item.type || 'listener').success(function(res) {
      if (!res.status) return;
      item.active = !item.active;
    });
   }
  }
]);