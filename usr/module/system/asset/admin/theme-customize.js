angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
     function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
      templateUrl: tpl('theme-customize'),
      controller: 'themeEditCtrl'
    });
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.factory('service', ['$http', 'config',
  function($http, config) {
    // Returns an Array of @import'd filenames in the order
    // in which they appear in the file.
    function includedLessFilenames() {
      var IMPORT_REGEX = /^@import \"(.*?)\";$/
      var lessLines = __less['bootstrap.less'].split('\n');

      for (var i = 0, imports = []; i < lessLines.length; i++) {
        var match = IMPORT_REGEX.exec(lessLines[i]);
        var isNeed = match && match[1] != 'variables.less' && match[1] != 'glyphicons.less';
        if (isNeed) imports.push(match[1])
      }

      return imports;
    }

    function configData(varsConfig) {
      var originConfig = config.data.config;
      var data;
      if (!angular.isObject(originConfig)) {
        data = { vars: {}, css: [], js: [] };
      } else {
        data = angular.copy(originConfig);
      }

      if (varsConfig) {
        data.vars = varsConfig;
      }

      return data;
    }

    function generateCustomCSS(sections, varsConfig) {
      var ret = [];
      var value;

      angular.forEach(sections, function(section) {
        angular.forEach(section.subsections, function(subsection) {
          angular.forEach(subsection.variables, function(variable) {
            if (angular.isDefined(varsConfig[variable.name])) {
              value = varsConfig[variable.name];
            } else {
              value = variable.defaultValue;
            }
            ret.push(variable.name + ': ' + value);
          });
        });
      });
      //Fixed @badge-line-height bug
      ret.unshift('@badge-line-height: 1');

      return ret.join(';\n') + ';\n';
    }

    var urlRoot = config.urlRoot;

    return {
      //Parse less str to array
      sections: new LessParser(__less["variables.less"]).parseFile(),
      generateCustomData: function() {
        var sections = this.sections;
        var ret = [];
        var data = configData().vars;

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
      },
      compile: function(varsConfig) {
        var lessResult = [generateCustomCSS(this.sections, varsConfig)];
        var imports = includedLessFilenames();
        var parser = new less.Parser;

        angular.forEach(imports, function(item) {
          lessResult.push(__less[item]);
        });
        lessResult = lessResult.join('\n');
        parser.parse(lessResult, function(err, tree) {
          if (err) {
            return console.error(err);
          }
          lessResult = tree.toCSS();
        });

        return $http.post(urlRoot + 'compile', {
          less: lessResult,
          config: configData(varsConfig)
        });
      }
    }
  }
])
.controller('themeEditCtrl', ['$scope', 'service',
  function($scope, service) {
    $scope.sections = service.generateCustomData();

    $scope.compileAction = function() {
      var vars = [];
      var config = {};
      angular.forEach($scope.sections, function(section) {
        angular.forEach(section.subsections, function(subsection) {
          angular.forEach(subsection.variables, function(variable) {
            config[variable.name] = variable.defaultValue;
          });
        });
      });
      service.compile(config);
    }
  }
]);