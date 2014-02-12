angular.module('widget')
.config(['$routeProvider', 'piProvider', 'config',
	function ($routeProvider, piProvider, config) {
		//Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

		$routeProvider.otherwise({
			templateUrl: tpl('widgets'),
			controller: 'listCtrl'
		});

		piProvider.addTranslations(config.t);
	}
])
.controller('listCtrl', ['$scope', 'config',
	function($scope, config) {
		angular.forEach(config.data.widgets, function(item) {
			item.editUrl = config.urlRoot + 'edit/id/' + item.id;
			item.deleteUrl = config.urlRoot + 'delete/id/' + item.id;
		});
		angular.extend($scope, config.data);
	}
]);