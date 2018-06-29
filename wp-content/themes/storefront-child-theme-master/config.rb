http_path = '/'
css_dir = '/'
sass_dir = 'assets/sass'
images_dir = 'assets/img'
javascripts_dir = 'assets/js'
relative_assets = true
output_style = (environment == :production) ? :compressed : :expanded
line_comments = (environment == :production) ? false : true
sourcemap = (environment == :production) ? false : true
