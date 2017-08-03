module.exports = function(grunt) {

  grunt.initConfig({
    jshint: {
      files: ['library/js/**/*.js', '!library/js/justgage.js', '!library/js/renamed.js', '!library/js/raphael-2.1.4.min.js'],
      options: {
        globals: {
          jQuery: true
        }
      }
    },
    watch: {
      files: ['<%= jshint.files %>'],
      tasks: ['jshint', 'uglify']
    },
		uglify: {
			options: {
				ASCIIOnly: true
			},
			all: {
				expand: true,
				cwd: 'library/js/',
				src: [ '**/*.js', '!**/*.min.js' ],
				dest: 'library/js/',
				ext: '.min.js'
			}
		}
  });

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

  grunt.registerTask('default', ['jshint', 'uglify']);

};
