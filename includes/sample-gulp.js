var gulp = require('gulp');
var fs = require('fs');
var path = require('path');
var gutil = require('gulp-util');
var urljoin = require('url-join');
var replace = require('gulp-replace');
var request = require('request');
var critical = require('critical');

// Get critical CSS of these urls (url by content type => stylesheet name)
var baseDomain = 'http://localhost/';
var urls = {
  "/": "home",
  "/sample-article": "article",
  "/sample-page": "page"
};
var dest = 'css/critical/';


gulp.task('critical', function () {
  Object.keys(urls).map(function(url, index) {
    var pageUrl = urljoin( baseDomain, url );
    var destCssPath = path.join( dest, urls[url] + '.css' );

    request(pageUrl, function (error, response, body) {
      if (!error && response.statusCode === 200) {
        var htmlString = body
          .replace(/href="\//g, 'href="' + urljoin(baseDomain, '/'))
          .replace(/src="\//g, 'src="' + urljoin(baseDomain, '/'));

        gutil.log('Generating critical css', gutil.colors.magenta(destCssPath), 'from', pageUrl);

        critical.generate({
          base: '/',
          html: htmlString,
          src: '',
          dest: destCssPath,
          minify: true,
          width: 1280,
          height: 900
        });

      }
    })
  });
});
