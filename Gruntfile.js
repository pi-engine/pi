module.exports = function(grunt) {

  function assetCwd(name) {
    return 'usr/module/' + name + '/asset/';
  }

  function assetCwdBuild(name) {
    return 'usr/module/' + name + '/asset/_build';
  }

  var vendor = 'www/static/vendor/';
  var angularSrc = vendor + 'angular/';
  //Configuration modules, you can change for your need
  var modules = ['system', 'user', 'message'];

  function handlerMouldes(type) {
    var ret = {};
    type = type || '**';
    modules.forEach(function(item) {
      ret[item] = {
        cwd: assetCwdBuild(item),
        src: type,
        dest: assetCwdBuild(item),
        expand: true
      }
    });
    return ret;
  }

  function extend(target, src) {
    for (var i in src) {
      if (src.hasOwnProperty(i)) {
        target[i] = src[i];
      }
    }
  }

  var copyOpts = (function() {
    var ret = {};
    modules.forEach(function(item) {
      ret[item] = {
        cwd: assetCwd(item),
        src: '**',
        dest: assetCwdBuild(item),
        expand: true
      }
    });
    return ret;
  })();

  var uglifyOpts = (function() {
    var ret = {
      options: {
        //banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
      },
      pi: {
        cwd: angularSrc,
        src: ['pi*.js', 'i18n/*.js'],
        expand: true,
        dest: angularSrc,
        ext: '.min.js'
      },
    };
    extend(ret, handlerMouldes('**/*.js'));
    return ret;
  })();

  var cleanOpts = (function() {
    var ret = {
      pi: {
        src: [angularSrc + 'pi*.min.js', angularSrc + 'i18n/*.min.js']
      },
      build: {
        src: ''
      }
    };
    var builds = [];
    modules.forEach(function(item) {
      builds.push(assetCwdBuild(item));
    });
    ret.build.src = builds;
    return ret;
  })();

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: copyOpts,
    uglify: uglifyOpts,
    clean: cleanOpts,
    cssmin: handlerMouldes('**/*.css'),
    snapshot: {
      userTheme: {
        options: {
          //snapshotPath: '',
          url: 'http://pifork.liaowei.com/user/profile/1',
          extension: 'png',
          filename: 'screenshot.png',
          src: 'usr'
        }
      }
    }
  });

  // Load the plugin.
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-snapshot');
 

  // Default task(s).
  grunt.registerTask('default', ['clean', 'copy', 'uglify', 'cssmin']);
  grunt.registerTask('clear', ['clean:build']);
  grunt.registerTask('screenshot', ['snapshot']);
};