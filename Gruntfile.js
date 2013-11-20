module.exports = function(grunt) {

  function assetCwd(name) {
    return 'usr/module/' + name + '/asset/';
  }

  function assetCwdBuild(name) {
    return 'usr/module/' + name + '/asset/_build';
  }

  var vendor = 'www/static/vendor/';
  var angularSrc = vendor + 'angular/'
  

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: {
      user: {
        cwd: assetCwd('user'),
        src: '**',
        dest: assetCwdBuild('user'),
        expand: true
      }
    },
    uglify: {
      options: {
        //banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
      },
      user: {
        cwd: assetCwdBuild('user'),
        src: '**/*.js',
        dest: assetCwdBuild('user'),
        expand: true
      },
      pi: {
        cwd: angularSrc,
        src: 'pi*.js',
        expand: true,
        dest: angularSrc,
        ext: '.min.js'
      }
    },
    clean: {
      pi: {
        src: angularSrc + 'pi*.min.js',
      }
    },
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
  grunt.loadNpmTasks('grunt-snapshot');
 

  // Default task(s).
  grunt.registerTask('default', ['copy', 'clean', 'uglify']);
  grunt.registerTask('clear', ['clean']);
  grunt.registerTask('screenshot', ['snapshot']);
};