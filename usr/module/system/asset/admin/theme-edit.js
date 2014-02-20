angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
     function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
      templateUrl: tpl('theme-edit'),
      controller: 'themeEditCtrl'
    });
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.factory('service', ['$http', 'config',
  function($http, config) {
    var urlRoot = config.urlRoot;
    return {
      init: config.data,
      compile: function(vars) {
        var less = vars + __less['mixins.less'];
        return $http.post(urlRoot + 'compile', {
          less: less
        });
      }
    }
  }
])
.controller('themeEditCtrl', ['$scope', 'service',
  function($scope, service) {
    function generateData(data) {
      var sections = new LessParser(__less["variables.less"]).parseFile();
      var ret = [];

      angular.forEach(sections, function(section) {
        if (section.customizable) {
          ret.push(section);
        }
      });
      angular.forEach(ret, function(section) {
        angular.forEach(section.subsections, function(subsection) {
          angular.forEach(subsection.variables, function(variable) {
            if (angular.isDefined(data[variable.name])) {
              variable.defaultValue = data[variable.name];
            }
          });
        });
      });
      return ret;
    }
    
    $scope.sections = generateData(service.init.vars);

    $scope.compileAction = function() {
      var vars = [];
      angular.forEach($scope.sections, function(section) {
        angular.forEach(section.subsections, function(subsection) {
          angular.forEach(subsection.variables, function(variable) {
            vars.push(variable.name + ': ' + variable.defaultValue);
          });
        });
      });
      service.compile(vars.join(';\n'));
    }
  }
]);
