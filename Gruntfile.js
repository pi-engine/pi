module.exports = function(grunt) {

  function assetCwd(name) {
    return 'usr/module/' + name + '/asset/';
  }

  function assetCwdBuild(name) {
    return 'usr/module/' + name + '/asset/_build';
  }
  

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
        
      }
    },
    clean: {
      user: {
        src: assetCwdBuild('user')
      }
    }
  });

  // Load the plugin.
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-uglify');
 

  // Default task(s).
  grunt.registerTask('default', ['copy', 'uglify']);
  grunt.registerTask('clear', ['clean']);
};