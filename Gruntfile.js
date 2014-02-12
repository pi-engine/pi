module.exports = function(grunt) {
  var fs = require('fs');
  var path = require('path');
  var util = require('util');
  var config = require('./Gruntfile-config.js');

  function assetModuleCwd(name) {
    return path.join('usr/module', name, 'asset');
  }

  function assetThemeCwd(name) {
    return path.join('usr/theme', name, 'asset');
  }

  function assetModuleCwdBuild(name) {
    return path.join('usr/module', name, 'asset/_build');
  }

  function assetThemeCwdBuild(name) {
    return path.join('usr/theme', name, 'asset/_build');
  }

  function wwwAssetModuleCwd(name) {
    return path.join('www/asset', 'module-' + name);
  }

  function wwwAssetThemeCwd(name) {
    return path.join('www/asset', 'theme-' + name);
  }

  function vender(name) {
    return path.join('www/static/vendor', name);
  }
 
  var modules = (function() {
    var modules = [];
    if (config.modules == 'all') {
      //Auto read usr/module file name
      fs.readdirSync('usr/module')
        .forEach(function(path) {
          if (~path.indexOf('.')) return;
          modules.push(path);
        });
    } else if (util.isArray(config.modules)) {
      modules = config.modules;
    }
    
    return modules; 
  })();

  var themes = (function() {
    var themes = [];
    if (config.themes == 'all') {
      //Auto read usr/module file name
      fs.readdirSync('usr/theme')
        .forEach(function(path) {
          if (~path.indexOf('.')) return;
          themes.push(path);
        });
    } else if (util.isArray(config.themes)) {
      themes = config.themes;
    }
    
    return themes;   
  })();

  /**
   * 1. Copy for build
   * 2. publish www/asset to module asset, it will be useful when you develop in windows.After
   *    you done module asset, you can use 'grunt back'.
   */
  var copyOpts = (function() {
    var ret = {
      build: {
        files: []
      },
      publishBack: {
        files: []
      }
    };
    modules.forEach(function(item) {
      ret.build.files.push({
        cwd: assetModuleCwd(item),
        src: ['**'],
        dest: assetModuleCwdBuild(item),
        expand: true
      });
      ret.publishBack.files.push({
        cwd: wwwAssetModuleCwd(item),
        src: ['**'],
        dest: assetModuleCwd(item),
        expand: true
      });
      ret.publishBack.files.push({
        cwd: path.join('www/public/', 'module-' + item),
        src: ['**'],
        dest: path.join('usr/module/', item, 'public'),
        expand: true
      });
    });
    themes.forEach(function(item) {
      ret.build.files.push({
        cwd: assetThemeCwd(item),
        src: ['**'],
        dest: assetThemeCwdBuild(item),
        expand: true
      });
      ret.publishBack.files.push({
        cwd: wwwAssetThemeCwd(item),
        src: ['**'],
        dest: assetThemeCwd(item),
        expand: true
      });
    });
    return ret;
  })();

  /**
   * Clear modules or themes asset build files
   */
  var cleanOpts = (function() {
    var ret = {
      pi: {
        src: [vender('angular') + 'pi*.min.js', vender('angular') + 'i18n/*.min.js']
      },
      build: {}
    };
    var builds = [];
    modules.forEach(function(item) {
      builds.push(assetModuleCwdBuild(item));
    });
    themes.forEach(function(item) {
      builds.push(assetThemeCwdBuild(item));
    });
    ret.build.src = builds;
    return ret;
  })();

  var uglifyOpts = (function() {
    var ret = {
      options: {
        //banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
      },
      pi: {
        cwd: vender('angular'),
        src: ['pi*.js', 'i18n/*.js'],
        expand: true,
        dest: vender('angular'),
        ext: '.min.js'
      },
      modules: {
        files: []
      },
      themes: {
        files: []
      }
    };
    modules.forEach(function(item) {
      ret.modules.files.push({
        cwd: assetModuleCwdBuild(item),
        src: '**/*.js',
        dest: assetModuleCwdBuild(item),
        expand: true
      });
    });
    themes.forEach(function(item) {
      ret.themes.files.push({
        cwd: assetThemeCwdBuild(item),
        src: '**/*.js',
        dest: assetThemeCwdBuild(item),
        expand: true
      });
    });
    return ret;
  })();

  var cssminOpts = (function() {
    var list = [];
    modules.forEach(function(item) {
      list.push({
        cwd: assetModuleCwdBuild(item),
        src: '**/*.css',
        dest: assetModuleCwdBuild(item),
        expand: true
      });
    });
    themes.forEach(function(item) {
      list.push({
        cwd: assetThemeCwdBuild(item),
        src: '**/*.css',
        dest: assetThemeCwdBuild(item),
        expand: true
      });
    });
    return list;
  })();

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: copyOpts,
    uglify: uglifyOpts,
    clean: cleanOpts,
    cssmin: cssminOpts
  });

  // Load the plugin.
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-cssmin');

  //Handler asset files for optimize loading
  grunt.registerTask('default', ['clean', 'copy:build', 'uglify', 'cssmin']);

  //Clear modules and themes asset build
  grunt.registerTask('clear', ['clean:build']);

  //For www/asset and www/public files to usr
  grunt.registerTask('back', ['copy:publishBack']);
};