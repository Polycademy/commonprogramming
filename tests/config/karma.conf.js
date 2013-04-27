basePath = '../../';

files = [
  JASMINE,
  JASMINE_ADAPTER,
  'js/vendor/angular.js',
  'js/vendor/angular-*.js',
  'js/vendor/angular-mocks.js',
  'js/**/*.js',
  'tests/client/unit/**/*.js'
];

autoWatch = true;

browsers = ['Chrome'];

junitReporter = {
  outputFile: 'test_out/unit.xml',
  suite: 'unit'
};
