angular.module('system')
.config(['$routeProvider', 'piProvider', 'config',
  function($routeProvider, piProvider, config) {
     function tpl(name) {
      return config.assetRoot + name + '.html';
    }

    $routeProvider.otherwise({
      templateUrl: tpl('theme-customize'),
      controller: 'themeEditCtrl',
      resolve: {
        variablesLess: ['$q', '$route', '$rootScope', 'admin',
          function($q, $route, $rootScope, admin) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            admin.getLess('variables.less').success(function(res) {
              admin.variablesLess = res;
              deferred.resolve();
              $rootScope.alert = '';
            });
            return deferred.promise;
          }]
      }
    });
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.factory('admin', ['$http', '$rootScope', 'config',
  function($http, $rootScope, config) {
    // Returns an Array of @import'd filenames in the order
    // in which they appear in the file.
    function includedLessFilenames(bootstrapLess) {
      var IMPORT_REGEX = /^@import\s*\"(.*?)\";\s*$/
      var lessLines = bootstrapLess.split('\n');
      var imports = [];

      for (var i = 0; i < lessLines.length; i++) {
        var match = IMPORT_REGEX.exec(lessLines[i]);
        var isNeed = match && match[1] != 'variables.less' && match[1] != 'glyphicons.less';
        if (isNeed) { imports.push(match[1]);  }
      }

      return imports;
    }

    //Convert less to css
    function lessParseCss(str) {
      var parser = new less.Parser;
      var css;

      parser.parse(str, function(err, tree) {
          if (err) {
            return console.error(err);
          }
          try {
            css = tree.toCSS();
          } catch (event) {
            $rootScope.alert = { status: 0, message: event.message };
            css = '';
            console.error(event);
          }

          if (less.env == 'production') {
            css = css
                    .replace(/\n/g, '')
                    .replace(/(;)\s*/g, '$1')
                    .replace(/\s*({)\s*/g, '$1')
                    .replace(/\s*(})\s*/g, '$1')
                    .replace(/\s*(>)\s*/g, '$1')
                    .replace(/(:)\s*/g, '$1');
          }
      });

      return css;
    }

    var urlRoot = config.urlRoot;
    var themeName = config.data.name;
    var custom;

    if (!angular.isObject(config.data.custom)) {
      custom = { vars: {}, css: [], js: [] };
    } else {
      custom = config.data.custom;
    }

    return {
      //Get bootstrap less files
      getLess: function(name) {
        return $http.get(config.bootstrapLessUrl + name, {
          cache: true
        });
      },
      //Parse less str to array
      generateSections: function() {
        var sections = new LessParser(this.variablesLess).parseFile();
        var ret = [];

        angular.forEach(sections, function(section) {
          if (section.customizable) {
            ret.push(section);
          }
        });
        angular.forEach(ret, function(section) {
          angular.forEach(section.subsections, function(subsection) {
            angular.forEach(subsection.variables, function(variable) {
              if (angular.isDefined(custom[variable.name])) {
                variable.defaultValue = custom[variable.name];
              }
            });
          });
        });
        return ret;
      },
      generateCustomLess: function(custom) {
        var sections = new LessParser(this.variablesLess).parseFile();
        var customLess = [];

        angular.forEach(sections, function(section) {
          angular.forEach(section.subsections, function(subsection) {
            angular.forEach(subsection.variables, function(variable) {
              var value = angular.isDefined(custom[variable.name]) ? 
                            custom[variable.name] : variable.defaultValue;
              customLess.push(variable.name + ': ' + value);
            });
          });
        });
        //Fixed @badge-line-height bug
        customLess.unshift('@badge-line-height: 1');

        return customLess.join(';\n') + ';\n';
      },
      compile: function(custom) {
        var getLess = this.getLess;
        var customLess = this.generateCustomLess(custom);

        //In process of compile
        $rootScope.alert = { status: 2, message: config.t.COMPILING };
        getLess('bootstrap.less').success(function(res) {
          var imports = includedLessFilenames(res);
          var length = imports.length;
          var lessResult = [customLess];
          var collection = {};
          var count = 0;
          var done = function() {
            if (count < length) return;
            var parser = new less.Parser;

            angular.forEach(imports, function(item) {
              lessResult.push(collection[item]);
            });
            lessResult = lessParseCss(lessResult.join('\n'));
            if (!lessResult) return;

            $http.post(urlRoot + 'compile', {
              less: lessResult,
              custom: custom,
              name: themeName
            });
          }

          angular.forEach(imports, function(item) {
            getLess(item).success(function(result) {
              collection[item] = result;
              count++;
              done();
            });
          });
        });

    
      },
      reset: function() {
        return $http.post(urlRoot + 'reset', {
          name: themeName
        });
      }
    }
  }
])
.controller('themeEditCtrl', ['$scope', 'admin',
  function($scope, admin) {
    $scope.sections = admin.generateSections();

    $scope.compileAction = function() {
      var custom = {};

      angular.forEach($scope.sections, function(section) {
        angular.forEach(section.subsections, function(subsection) {
          angular.forEach(subsection.variables, function(variable) {
            custom[variable.name] = variable.defaultValue;
          });
        });
      });
      admin.compile(custom);
    }

    $scope.resetAction = function() {
      admin.reset().success(function() {

      });
    }
  }
]);
