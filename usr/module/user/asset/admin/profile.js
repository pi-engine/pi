angular.module('user')
.config(['$routeProvider', 'piProvider', 'config',
  function ($routeProvider, piProvider, config) {
    //Get template url
    function tpl(name) {
      return config.assetRoot + name + '.html';
    }
    $routeProvider.when('/field', {
      templateUrl: tpl('profile-field'),
      controller: 'fieldCtrl',
      resolve: {
        data: ['$q', '$rootScope', 'server',
          function($q, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server.getField().success(function(data) {
              angular.forEach(data.compounds, function(compound) {
                angular.forEach(compound.fields, function(field) {
                  field.compound = compound.name;
                });
              });
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).when('/dress', {
      templateUrl: tpl('profile-dress'),
      controller: 'dressCtrl',
      resolve: {
        data: ['$q', '$rootScope', 'server',
          function($q, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server.getDress().success(function(data) {
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).when('/privacy', {
      templateUrl: tpl('profile-privacy'),
      controller: 'privacyCtrl',
      resolve: {
        data: ['$q', '$rootScope', 'server',
          function($q, $rootScope, server) {
            var deferred = $q.defer();
            $rootScope.alert = 2;
            server.getPrivacy().success(function(data) {
              deferred.resolve(data);
              $rootScope.alert = '';
            });
            return deferred.promise;
          }
        ]
      }
    }).otherwise({
      redirectTo: '/field'
    });
    piProvider.setHashPrefix();
    piProvider.addTranslations(config.t);
    piProvider.addAjaxInterceptors();
  }
])
.service('server', ['$http', 'config',
  function ($http, config) {
    var urlRoot = config.urlRoot;

    this.getField = function() {
      return $http.get(urlRoot + 'field');
    }

    this.updateTitle = function(data) {
      return $http.post(urlRoot + 'updateField', data);
    }

    this.getPrivacy = function() {
      return $http.get(urlRoot + 'privacy');
    }

    this.setPrivacy = function(data) {
      return $http.post(urlRoot + 'setPrivacy', data);
    }

    this.getDress = function() {
      return $http.get(urlRoot + 'dressup');
    }

    this.saveDressUp = function(displays) {
      return $http.post(urlRoot + 'saveDressUp', {
        displays: displays
      });
    }

    this.toggleRequired = function(field) {
      return $http.post(urlRoot + 'required', {
        required: field.is_required,
        field: field.name,
        compound: field.compound
      });
    }
  }
])
.controller('fieldCtrl', ['$scope', 'server', 'data',
  function ($scope, server, data) {
    angular.extend($scope, data);

    $scope.$on('piHoverInputSave', function(event, data) {
      server.updateTitle(data);
    });

    $scope.requiredAction = function(field) {
      server.toggleRequired(field);
    };
  }
])
.controller('dressCtrl', ['$scope', '$route', '$timeout', 'config', 'server', 'data', 
  function ($scope, $route, $timeout, config, server, data) {
    angular.forEach(data.compounds, function(item) {
      item.$isEditing = 0;
    });
    angular.forEach(data.displays, function(item) {
      item.$isEditing = 0;
    });
    angular.extend($scope, data);

    var isSaved = 1;

    $scope.displaysOpts = {
      handle: '.panel-heading'
    };

    $scope.$watch('displays', function(newValue, oldValue) {
      if (newValue !== oldValue) {
        $scope.saveAlert = { message: config.t.SAVE_TIP, type: 'warning' };
        isSaved = 0;
      }
      var customGroup = [];
      angular.forEach(newValue, function(item) {
        if (!item.name) customGroup.push(item.title);
      });
      $scope.customGroup = customGroup;
    }, true);

    $scope.addDisplayGroup = function(idx) {
      var compound = $scope.compounds[idx];
      $scope.displays.push(compound);
      $scope.compounds.splice(idx, 1);
    }

    $scope.AddCustomDisplay = function() {
      var title = $scope.entity;
      var unique = true;
      if (!title) return;
      angular.forEach($scope.displays, function(item) {
        if (item.title == title) return unique = false;
      });
      if (!unique) return;
      $scope.displays.push({
        title: title,
        $isEditing: 1,
        fields: []
      });
      $scope.entity = '';
    }

    $scope.AddGroupField = function(title, idx) {
      var field = $scope.profile[idx];
      angular.forEach($scope.displays, function(item) {
        if (item.title == title) {
          item.$isEditing = 1;
          item.fields.push(field);
          $scope.profile.splice(idx, 1);
          return false;
        }
      });
    }

    $scope.removeGroupField = function(fields, idx) {
      var field = fields[idx];
      $scope.profile.push(field);
      fields.splice(idx, 1);
    }

    $scope.removeDisplay = function(idx) {
      var display = $scope.displays[idx];
      $scope.displays.splice(idx, 1);
      if (display.name) {
        $scope.compounds.push(display);
      } else {
        $scope.profile = $scope.profile.concat(display.fields);
      }
    }

    $scope.toggleGroup = function(display) {
      display.$isEditing = !display.$isEditing;
    }

    $scope.saveAction = function() {
      server.saveDressUp($scope.displays);
      $scope.saveAlert = '';
      isSaved = 1;
    }

    $scope.cancelAction = function() {
      $route.reload();
    }

    $scope.checkCustomGroup = function() {
      if ($scope.customGroup.length) return;
      $scope.$parent.alert = {
        status: 0,
        message: config.t.CHECK_GROUP
      };
    }

    var leavingPageText = config.t.LEAVE_CONFIRM;

    window.onbeforeunload = function() {
      if (!isSaved) {
        return leavingPageText;
      }
    }

    $scope.$on('$locationChangeStart', function(event, next, current) {
        if (!isSaved) {
          if(!confirm(leavingPageText)) {
            event.preventDefault();
          }
        }
    });

  }
])
.controller('privacyCtrl', ['$scope', 'server', 'data',
  function($scope, server, data) {
    angular.extend($scope, data);

    $scope.setPrivacyAction = function(item) {
      server.setPrivacy(item);
    }

    $scope.forcePrivacyAction = function(item) {
      var old = item.is_forced;
      item.is_forced = old ? 0 : 1;
      server.setPrivacy(item).error(function() {
        item.is_forced = old;
      });
    }
  }
]);
