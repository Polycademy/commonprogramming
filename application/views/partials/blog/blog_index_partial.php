<script type="text/ng-template" id="blog_index.html">
	<textarea ui-codemirror="{theme:'rubyblue', mode:'javascript'}" ng-model="codeMirrorModel"></textarea>
	<div>
		<label class="checkbox">
			<input type="checkbox" ng-model="oneAtATime">
			Open only one at a time
		</label>

		<div accordion close-others="oneAtATime">
			<div accordion-group heading="Static Header">
				This content is straight in the template.
			</div>
			<div accordion-group heading="{{group.title}}" ng-repeat="group in groups">
				{{group.content}}
			</div>
			<div accordion-group heading="Dynamic Body Content">
				<p>The body of the accordion group grows to fit the contents</p>
				<button class="btn btn-small" ng-click="addItem()">Add Item</button>
				<div ng-repeat="item in items">{{item}}</div>
			</div>
		</div>
	</div>
	
	<!-- FOR SUBCONTROLLER DummyIndexCtrl
	<div ng-controller="ChildSubCtrl" ng-repeat="child in children">
		<h2>CHILD NAME: {{child.name}} from {{child.id}}</h2>
		<div ng-controller="PlanSubCtrl" ng-repeat="plan in plans">
			<p>Plan Title: {{plan.planTitle}}</p>
		</div>
	</div>
	-->
	
</script>