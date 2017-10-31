# Critical CSS

Inlines a critical CSS file in HTML head, and loads non-critical CSS asynchronously using W3C Spec's preload.

## How it works ##
 * This module looks for a css file inside your theme directory. This css filename should match any of:
    * bundle type (i.e., "article.css")
    * entity id (i.e., "123.css")
    * url (i.e., "my-article.css")
 * If any of the above paths is found, this module loads the CSS file contents inside a _style_ tag placed in the HTML head.
 * Any other CSS file used in HTML head is loaded using [preload](https://www.w3.org/TR/preload/). For browsers not supporting this preload feature, a polyfill is provided.

### Gulp task for generating a css file ###
Before this module can do anything, you should generate a css file containing the critical css for any:
 * bundle type
 * entity id 
 * url
 
This can be acheived by running a Gulp task to automatically extract the critical css of any page.
Using Addy Osmani's [critical](https://github.com/addyosmani/critical) package is highly recommended.
 
Another option is [Filament Group's criticalCSS](https://github.com/filamentgroup/criticalCSS)
 
The extracted critical css must be saved in a directory inside the current theme.
 
#### Sample gulp task using Addy Osmani's critical  ####

```javascript
var gulp = require('gulp');
var fs = require('fs');
var path = require('path');
var gutil = require('gulp-util');
var urljoin = require('url-join');
var replace = require('gulp-replace');
var rimraf = require('rimraf');
var request = require('request');
var rp = require('request-promise');
var critical = require('critical');
var osTmpdir = require('os-tmpdir');
var browserSync = require('browser-sync');
var reload = browserSync.reload;

var config = {
    critical: {
        width: 1280,
        height: 900,
        dest: 'css/critical/',
        urls: {
            "/": "home",
            "/sample-article": "article",
            "/sample-page": "page"
        }
    }
};

var configLocal = {
  "critical": {
    "baseDomain": "http://localhost/"
  }
};


// Para que request funcione con certificados no válidos
process.env.NODE_TLS_REJECT_UNAUTHORIZED = "0";

gulp.task('critical', ['critical:clean'], function (done) {
  Object.keys(config.critical.urls).map(function(url, index) {
    var pageUrl = urljoin( configLocal.critical.baseDomain, url );
    var destCssPath = path.join(process.cwd(), config.critical.dest, config.critical.urls[url] + '.css' );

    return rp({uri: pageUrl, strictSSL: false}).then(function (body) {
        var htmlString = body
            .replace(/href="\//g, 'href="' + urljoin(configLocal.critical.baseDomain, '/'))
            .replace(/src="\//g, 'src="' + urljoin(configLocal.critical.baseDomain, '/'));

        gutil.log('Generating critical css', gutil.colors.magenta(destCssPath), 'from', pageUrl);

        critical.generate({
            base: osTmpdir(),
            html: htmlString,
            src: '',
            dest: destCssPath,
            minify: true,
            width: config.critical.width,
            height: config.critical.height
        });

        if (index+1 === Object.keys(config.critical.urls).length) {
            return done();
        }
    });


  });
});

gulp.task('critical:clean', function (done) {
  return rimraf(config.critical.dest, function() {
    gutil.log('Critical directory', gutil.colors.magenta(config.critical.dest), 'deleted');
    return done();
  });
});

```

### Module configuration ###
Module must be enabled in /admin/config/development/performance/critical-css. This allows for easy enabling/disabling without uninstalling it.


## Limitations ##
This module works only for anonymous users, because its main goal is to speed the load of public pages already cached. Further versions will make possible to use it with logged users.
