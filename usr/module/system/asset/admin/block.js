angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
      templateUrl: tpl('blocks'),
      controller: 'BlocksCtrl'
    });

    piProvider.addTranslations(config.t);
  }
])
.service('server', ['$http', 'config', 
  function ($http, config) {
    var root = config.urlRoot;
    var blocks = [];

    /*
    angular.forEach(config.data.blocks, function(item) {
        item.previewUrl = config.previewRoot + '?block=' + item.id;
        item.editUrl = root + 'edit/id/' + item.id + '/name/' + item.module;
        item.deleteUrl = root + 'delete/id/' + item.id + '/name/' + item.module;
        item.cloneUrl = root + 'clone/root/' + item.root + '/name/' + item.module;
        blocks.push(item);
    });
    */

    this.data = {
      blocks: config.data.blocks
    }

    this.blockPages = function (id) {
      return $http.get(root + 'page/id/' + id);
    }

  }
]).controller('BlocksCtrl', ['$scope', 'server',
  function ($scope, server) {
    angular.extend($scope, server.data);
    
    $scope.pageAction = function(block) {
      if (block.pages) return;
      server.blockPages(block.id).success(function(data) {
        var pages = [];
        angular.forEach(data, function(value, key) {
          pages.push(value);
        });
        block.pages = pages;
      });
    }
  }
]);