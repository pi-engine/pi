//phtml init code
/**
  angular
    .module('moduleName', ['ngRoute', 'pi'])
    .constant('config', {
        urlRoot: '<?php echo $this->url('', array('controller' => 'controllerName')); ?>',
        assetRoot: '<?php echo $this->assetModule('ng-template/admin/', '', true, false); ?>',
        navTabs: [
            { text: '<?php _e('Nav1'); ?>', href: '#!/route1' },
            { text: '<?php _e('Nav2'); ?>', href: '#!/route2' },
            { text: '<?php _e('Nav3'); ?>', href: '#!/route3' }
        ],
        t: {
           TEST: '<?php _e('Test'); ?>'
        }
    });
 */

// js file (jsFileName: controllerName.js, moduleName: moduleName + controllerName + 'module')
/**
  If you want get data first, then render template, you can use 'resolve'
  $routeProvider.when('/route1', {
    templateUrl: tpl('index-activated'),
    controller: 'route1Ctrl',
    resolve: {
      data: ['$q', '$route', 'server',
        function($q, $route, server) {
            var deferred = $q.defer();
            server.get().success(function(data) {
              deferred.resolve(data);     you can get this data at controller through injector
            });
            return deferred.promise;
        }
      ]
    }
  });

 */
angular.module('moduleName')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }
    $routeProvider.when('/route1', {
      templateUrl: tpl('index-activated'),
      controller: 'route1Ctrl'
    }).when('/route2', {
      templateUrl: tpl('index-pending'),
      controller: 'route2Ctrl'
    }).when('/route3', {
      templateUrl: tpl('index-new'),
      controller: 'route3Ctrl'
    }).otherwise({
      redirectTo: '/route1'
    });

    //'!' hash url
    piProvider.hashPrefix();
    piProvider.navTabs(config.navTabs);
    //Translations filter
    piProvider.translations(config.t);
    //Global ajax message setup
    piProvider.ajaxSetup();
  }
])
.service('server', ['$http', '$cacheFactory', 'config',
  function ($http, $cacheFactory, config) {
    var urlRoot = config.urlRoot;

    this.get = function(params) {
      return $http.get(urlRoot + 'action', {
        params: params,
        //cache: true
      });
    }

    this.post = function(params) {
      return $http.get(urlRoot + 'action', params);
    }
  }
])
.controller('route1Ctrl', ['$scope', '$rootScope', 'server',
  function ($scope, $rootScope, server) {
    
  }
])
.controller('route2Ctrl', ['$scope', 'server',
  function ($scope, server) {
    
  }
])
.controller('route3Ctrl', ['$scope', 'server',
  function($scope, server) {
    
  }
]);