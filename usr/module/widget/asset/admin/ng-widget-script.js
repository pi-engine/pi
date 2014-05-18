angular.module('widget')
.config(['$routeProvider', 'piProvider', 'config',
	function ($routeProvider, piProvider, config) {
        function tpl(name) {
          return config.assetRoot + name + '.html';
        }

		$routeProvider.otherwise({
			templateUrl: tpl('widget-script'),
			controller: 'listCtrl'
		});

		piProvider.addTranslations(config.t);
	}
])
.service('server', ['$http', 'config',
    function($http, config) {
        this.data = config.data;
    }
])
.controller('listCtrl', ['$scope', 'server',
    function($scope, server) {
        angular.extend($scope, server.data);
    }
]);