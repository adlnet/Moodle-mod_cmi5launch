module.exports = function(grunt) {
    grunt.initConfig({
        babel: {
            options: {
                sourceMap: false,
                presets: ['@babel/preset-env']
            },
            dist: {
                files: [{
                    expand: true,
                    cwd: 'amd/src',
                    src: ['*.js'],
                    dest: 'amd/build',
                    ext: '.js'
                }]
            }
        },
        requirejs: {
            compile: {
                options: {
                    baseUrl: "amd/build",
                    name: "settings",  
                    out: "amd/build/settings.min.js",
                    optimize: "uglify"
                }
            },
            tenant: {
                options: {
                    baseUrl: "amd/build",
                    name: "tenant",
                    out: "amd/build/tenant.min.js",
                    optimize: "uglify"
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-babel');
    grunt.loadNpmTasks('grunt-contrib-requirejs');

    grunt.registerTask('default', ['babel', 'requirejs']);
};
