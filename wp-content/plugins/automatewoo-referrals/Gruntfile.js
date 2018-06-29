module.exports = function(grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        sass: {
            dist: {
                options: {
                    sourceMap: true
                },
                files: {
                    'assets/css/automatewoo-referrals.css': 'assets/css/automatewoo-referrals.scss',
                    'assets/css/automatewoo-referrals-admin.css': 'assets/css/automatewoo-referrals-admin.scss'
                }
            }
        },

        uglify: {
            main: {
                options: {
                    mangle: false
                },
                files: {
                    'assets/js/automatewoo-referrals.min.js': 'assets/js/automatewoo-referrals.js',
                    'assets/js/automatewoo-referrals-admin.min.js': 'assets/js/automatewoo-referrals-admin.js'
                }
            }
        },

	    autoprefixer: {
		    options: {
			    browsers: ['> 1%', 'last 2 versions', 'Firefox ESR', 'Opera 12.1']
		    },
		    files: {
			    expand: true,
			    src: 'assets/css/*.css'
		    }
	    },

        watch: {
            css: {
                files: 'assets/css/*.scss',
                tasks: ['sass', 'autoprefixer']
            },
            js: {
                files: 'assets/js/*.js',
                tasks: ['uglify:main']
            }
        },


        compress: {
            main: {
                options: {
                    archive: 'automatewoo-referrals.zip'
                },
                files: [
                    { src: [
                        'automatewoo-referrals.php',
                        'CHANGELOG.md',
                        'addon-includes/**',
                        'assets/**',
                        'includes/**',
                        'languages/**',
                        'templates/**',
                        '.gitignore',
                        'license.txt',
                        'wpml-config.xml'
                    ],
                        dest: 'automatewoo-referrals/'
                    }
                ]
            }
        },

    });

    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-autoprefixer');
    grunt.loadNpmTasks('grunt-contrib-compress');


	grunt.registerTask('build', [
		'sass',
		'uglify',
		'autoprefixer'
	]);


    grunt.registerTask('plugin', [
        'sass',
        'uglify',
        'autoprefixer',
        'compress'
    ]);

    // Default task(s).
    grunt.registerTask('default', ['watch']);

};