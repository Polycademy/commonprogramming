<script type="text/ng-template" id="auth_index.html">
	<form name="loginForm" ng-submit="login()">
		<label for="username">USERNAME:</label>
		<input id="username" type="text" ng-model="loginForm.username" />
		<span>{{validationErrors.username}}</span>
		<label for="password">PASSWORD:</label>
		<input id="password" type="password" ng-model="loginForm.password" />
		<span>{{validationErrors.password}}</span>
		<label for="rememberMe">REMEMBER ME:</label>
		<input id="rememberMe" type="checkbox" ng-model="loginForm.rememberMe" />
		<span>{{validationErrors.rememberMe}}</span>
		<button type="submit" name="submit" value="true">Submit!</button>
	</form>
	<div ng-show="loginErrors">
		<h4>Login Errors</h4>
		<ul>
			<li ng-repeat="error in loginErrors">{{error}}</li>
		</ul>
	</div>
	<button ng-click="logout()">Log out!</button>
</script>