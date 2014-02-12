angular.module('widget')
.config(['$routeProvider', 'piProvider', 'config',
	function ($routeProvider, piProvider, config) {
		//Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

		$routeProvider.otherwise({
			templateUrl: tpl('index'),
			controller: 'indexCtrl'
		});

		piProvider.addTranslations(config.t);
		piProvider.addAjaxInterceptors();
	}
])
.service('server', ['$http', 'config',
	function($http, config) {
		var root = config.urlRoot;

		this.data = config.data;

		this.uninstall = function(name) {
			return $http.post(root + 'delete', {
				name: name
			})
		}

		this.install = function(name) {
			return $http.post(root + 'add', {
				name: name
			});
		}
	}
])
.controller('indexCtrl', ['$scope', 'server',
	function($scope, server) {
		angular.extend($scope, server.data);

		$scope.uninstallAction = function(idx) {
			var block = $scope.active[idx].block;

			server.uninstall(block.name).success(function(data) {
				if (!data.status) return;
				$scope.available.push(block);
				$scope.active.splice(idx, 1);
			});
		}

		$scope.installAction = function(idx) {
			var block = $scope.available[idx];

			server.install(block.name).success(function(data) {
				if (!data.status) return;
				$scope.active.push({
					block: block
				});
				$scope.available.splice(idx, 1);
			});
		}
	}
]);