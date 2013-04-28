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
							<li ng-class="{ 'active_link': $state.includes('home') }"><a href="">home</a></li>
							<li class="divider-vertical"></li>
							<li ng-class="{ 'active_link': $state.includes('courses') }"><a href="courses">courses</a></li>
							<li class="divider-vertical"></li>
							<li ng-class="{ 'active_link': $state.includes('blog') }"><a href="blog">blog</a></li>
							<li class="divider-vertical"></li>
							<li ng-class="{ 'active_link': $state.includes('canvas') }"><a href="canvas">canvas</a></li>
							<li class="divider-vertical"></li>
							<li ng-class="{ 'active_link': $state.includes('auth') }"><a href="auth">auth</a></li>
						</ul>
					</div>
				</div>
			</div>
        </header>
		
		<div class="main">
			<div class="container" ui-view></div>
		</div>
		
		<footer ng-controller="FooterCtrl">
			<div class="container">
				<article class="row footer_grid" equalise-heights-dir="section > p">
					<section class="blog_panel span4">
						<h3><a href="blog">Blog</a></h3></h3>
						<p>
							<span ng-repeat="posts in [1, 2, 3, 4]">
								&#8226; <em class="footer_dates">2012/02/01</em> - Awesome Blog Post!<br /><br />
							</span>
						</p>
					</section>
					<section class="fbtwitter_panel span4">
						<h3><a href="<?=$facebook?>">FB</a>.<a href="<?=$twitter?>">Twitter</a></h3>
						<p>
							<span ng-repeat="status in [1, 2, 3, 4]">
								&#8226; <em class="footer_dates">2012/02/01</em> - Awesome Tweet!<br /><br />
							</span>
						</p>
					</section>
					<section class="notices_panel span4">
						<h3>Notices</h3>
						<p>
							We're looking for companies, mentors and advisors. If you are interested in getting involved and checking out our students, check our partners page and contact us.
							<br />
							<br />
							You can contact us at <a href='http://www.google.com/recaptcha/mailhide/d?k=01q-bJV3WQrMYWD2quLJ7VPA==&c=FsmnfqaQraWCMzZB6tsagBZd557LPBLlxh80gaenMSo='>@polycademy.com</a> or phone us at +61 (0)420 925 975
						</p>
					</section>
				</article>
				<p class="copyright"><?=$copyright?></p>
			</div>
			
			<!-- XHR Messages other than GET-->
			<div class="http_messages">
				<em class="http_message" fade-in-out-dir="2000" ng-repeat="httpMessage in httpMessages">{{httpMessage.message}}</em>
			</div>
			
		</footer>
		
		<!-- AJAX Loader Screen -->
		<div class="ajax_loader"></div>
		
		<!-- Client Side Templates -->
		<?
			Template::asset('application/views', 'php', array(
				'application/views/index.html', //CI stuff
				'application/views/layouts/**',  //for server side
				'application/views/errors/**' //this is for CI
			));
		?>
		
		<!-- Pass in PHP variables to Javascript -->
		<script>
			var serverVars = {
				baseUrl: '<?= base_url() ?>',
				csrfCookieName: '<?= $this->config->item('cookie_prefix') . $this->config->item('csrf_cookie_name') ?>',
				sessCookieName: '<?= $this->config->item('cookie_prefix') . $this->config->item('sess_cookie_name') ?>'
			};
		</script>
		
		<!-- Vendor Javascripts -->
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script>window.jQuery || document.write('<script src="js/vendor/jquery-1.9.1.min.js"><\/script>')</script>
		<script src="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/2.3.1/js/bootstrap.min.js"></script>
		<script>$.fn.modal || document.write('<script src="js/vendor/bootstrap.min.js"><\/script>')</script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.0.6/angular.min.js"></script>
		<script>window.angular || document.write('<script src="js/vendor/angular.min.js"><\/script>')</script>
		<script src="js/vendor/angular-resource.min.js"></script>
		<script src="js/vendor/angular-cookies.min.js"></script>
		<script src="js/vendor/angular-ui.min.js"></script>
		<script src="js/vendor/ui-bootstrap-tpls-0.2.0.min.js"></script>
		<script src="js/vendor/angular-ui-states.min.js"></script>
		
		<!-- WebSockets -->
		<script src="http://autobahn.s3.amazonaws.com/js/autobahn.min.js"></script>
		<script>window.ab || document.write('<script src="js/vendor/autobahn.min.js"><\/script>')</script>
		<script src="js/vendor/web-socket-js/web_socket.js"></script>
		<script src="js/vendor/web-socket-js/swfobject.js"></script>
		<script>
			WEB_SOCKET_SWF_LOCATION = "js/vendor/web-socket-js/WebSocketMain.swf";
		</script>
		
		<!-- KineticJS -->
		<script src="js/vendor/kineticjs.min.js"></script>
		
		<!-- Codemirror Scripts-->
		<script src="js/vendor/codemirror/codemirror.js"></script>
		<script src="js/vendor/codemirror/mode/javascript.js"></script>
		
		<!-- Shims and Shivs and Other Useful Things -->
		<!--[if lt IE 9]><script src="js/vendor/es5-shim.min.js"></script><![endif]-->
		<script src="js/vendor/es6-shim.min.js"></script>
		<!--[if lt IE 9]><script src="js/vendor/json3.min.js"></script><![endif]-->
		
		<? if(ENVIRONMENT == 'development'){ ?>
			<?
				Template::asset('js', 'js', array(
					'js/main.min.js',
					'js/vendor/**',
				));
			?>
		<? }elseif(ENVIRONMENT == 'production'){ ?>
			<script src="js/main.min.js"></script>
			<script>
				var _gaq=[['_setAccount','<?= $google_analytics_key ?>'],['_trackPageview']];
				(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
				g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
				s.parentNode.insertBefore(g,s)}(document,'script'));
			</script>
		<? } ?>
		
	</body>
</html>