<script type="text/ng-template" id="courses_index.html">
	<div class="course_list">
		<p>Demonstration of a course list! And delete button! And update button pointing to another controller!</p>
		<span ng-show="coursesError">{{coursesError}}</span>
		<ul ng-repeat="course in courses">
			<li>{{course.id}}</li>
			<li>{{course.name}}</li>
			<li>{{course.startingDate}}</li>
			<li>{{course.daysDuration}}</li>
			<li>{{course.times}}</li>
			<li>{{course.numberOfApplications}}</li>
			<li>{{course.numberOfStudents}}</li>
		</ul>
	</div>
	<div class="specific_course">
		<p>Demonstration of course 2</p>
		<span ng-show="specificCourseError">{{specificCourseError}}</span>
		<p ng-show="specificCourse">{{specificCourse.id}} and {{specificCourse.name}}</p>
	</div>
	<div class="specific_course">
		<p>Demonstration of course 2</p>
		<p>{{specificCourse.id}} and {{specificCourse.name}}</p>
	</div>
	<div class="course_insert">
		<p>Demonstration of inserting a new course!</p>
		<form class="form-horizontal" name="courseForm" ng-submit="submitForm()">
			<div class="control-group">
				<label for="courseName" class="control-label required">Enter Course Name</label>
				<div class="controls controls-row">
					<input name="courseName" id="courseName" ng-model="courseForm.name" required />
				</div>
			</div>
			<input name="courseStartingDate" ng-model="courseForm.startingDate" required />
			<input name="courseDaysDuration" ng-model="courseForm.daysDuration" required />
			<input name="courseTimes" ng-model="courseForm.times" required />
			<input name="courseNumberOfApplications" ng-model="courseForm.numberOfApplications" />
			<input name="courseNumberOfStudents" ng-model="courseForm.numberOfStudents" />
			<button type="submit" name="submit" value="true">Submit!</button>
		</form>
		<ul>
			<li>Dual Binding of the Course Name = {{courseForm.name}}</li>
			<li>courseForm.name.$valid = {{courseForm.name.$valid}}</li>
			<li>courseForm.name.$error = {{courseForm.name.$error}}</li>
			<li>courseForm.$valid = {{courseForm.$valid}}</li>
			<li>courseForm.$error.required = {{!!courseForm.$error.required}}</li>
		</ul>
	</div>
</script>