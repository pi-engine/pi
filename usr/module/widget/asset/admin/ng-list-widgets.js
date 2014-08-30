angular.module('widget')
.config(['$routeProvider', 'piProvider', 'config',
	function ($routeProvider, piProvider, config) {
        function tpl(name) {
          return config.assetRoot + name + '.html';
        }

		$routeProvider.otherwise({
			templateUrl: tpl('list-widgets'),
			controller: 'listCtrl'
		});

		piProvider.addTranslations(config.t);
	}
])
.controller('listCtrl', ['$scope', 'config',
	function($scope, config) {
		angular.extend($scope, config.data);
	}
]);