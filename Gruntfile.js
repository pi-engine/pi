module.exports = function(grunt) {

  function assetCwd(name) {
    return 'usr/module/' + name + '/asset/';
  }

  function assetCwdBuild(name) {
    return 'usr/module/' + name + '/asset/_build';
  }

  var vendor = 'www/static/vendor/';
  var angularSrc = vendor + 'angular/';
  

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: {
      user: {
        cwd: assetCwd('user'),
        src: '**',
        dest: assetCwdBuild('user'),
        expand: true
      },
      system: {
        cwd: assetCwd('system'),
        src: '**',
        dest: assetCwdBuild('system'),
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
      system: {
        cwd: assetCwdBuild('system'),
        src: '**/*.js',
        dest: assetCwdBuild('system'),
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
      user: {
        src: assetCwdBuild('user')
      },
      system: {
        src: assetCwdBuild('system')
      },
      pi: {
        src: angularSrc + 'pi*.min.js'
      },
      //clear module asset files
      build: {
        src: [assetCwdBuild('user'), assetCwdBuild('system')] 
      }
    },
    cssmin: {
      user: {
        expand: true,
        cwd: assetCwdBuild('user'),
        src: '**/*.css',
        dest: assetCwdBuild('user'),
        ext: '.css'
      },
      system: {
        expand: true,
        cwd: assetCwdBuild('system'),
        src: '**/*.css',
        dest: assetCwdBuild('system'),
        ext: '.css'
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
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-snapshot');
 

  // Default task(s).
  grunt.registerTask('default', ['clean', 'copy', 'uglify', 'cssmin']);
  grunt.registerTask('clear', ['clean:build']);
  grunt.registerTask('screenshot', ['snapshot']);
};