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
				<ul class="footer_links">
					<li><a href="terms_of_service">Terms of Service & Privacy Policy</a></li>
					<li><a href="refund_policy">Refund Policy</a></li>
				</ul>
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
		<? Template::asset('application/views/partials', 'php', array('footer_partial.php', 'header_partial.php')) ?>
		
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
					'js/vendor',
					'js/vendor/codemirror',
					'js/vendor/codemirror/mode',
					'js/vendor/web-socket-js',
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