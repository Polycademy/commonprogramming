<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" ng-app="App"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" ng-app="App"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" ng-app="App"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" ng-app="App"> <!--<![endif]-->
	<head>
		<base href="<?= base_url() ?>" />
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?= $title ?> - <?= $desc ?></title>
		<meta name="description" content="<?= $meta_desc ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="google-site-verification" content="<?= $google_site_verification ?>" />
		<meta name="fragment" content="!" />
		<link rel="shortcut icon" href="favicon.ico">
		<link rel="apple-touch-icon" href="apple-touch-icon.png">
		<link rel="stylesheet" href="css/main.css">
		<script src="js/vendor/modernizr-2.6.2-respond-1.1.0.min.js"></script>
		
		<!--[if lte IE 8]>
			<script>
				// The ieshiv takes care of our ui.directives, bootstrap module directives and
				// AngularJS's ng-view, ng-include, ng-pluralize and ng-switch directives.
				// However, IF you have custom directives (yours or someone else's) then
				// enumerate the list of tags in window.myCustomTags
				//window.myCustomTags = [ 'yourDirective' ];
			</script>
			<script src="js/vendor/angular-ui-ieshiv.min.js"></script>
		<![endif]-->
		
	</head>
	<body class="ng-cloak" ng-cloak>
		<!--[if lt IE 7]>
			<p class="chromeframe">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">activate Google Chrome Frame</a> to improve your experience.</p>
		<![endif]-->
        <header class="navbar navbar-static-top" ng-controller="HeaderCtrl">
			<div class="container">
				<div class="navbar-inner">
					<a class="logo" href="<?php echo site_url() ?>">
						<img src="img/logo.png" />
					</a>
					<p class="slogan"><?= $desc ?></p>
					<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</a>
					<div class="nav-collapse collapse">
						<ul class="nav">
							<li><a href="">home</a></li>
							<li class="divider-vertical"></li>
							<li><a href="courses">courses</a></li>
							<li class="divider-vertical"></li>
							<li><a href="partners">partners</a></li>
							<li class="divider-vertical"></li>
							<li><a href="http://codeforaustralia.com.au">code for australia</a></li>
							<li class="divider-vertical"></li>
							<li><a href="http://phpbounce.aws.af.cm/">php bounce</a></li>
							<li class="divider-vertical"></li>
							<li><a href="http://polycademy.eventbrite.com.au/">events</a></li>
							<li class="divider-vertical"></li>
							<li><a href="blog">blog</a></li>
						</ul>
					</div>
				</div>
			</div>
        </header>