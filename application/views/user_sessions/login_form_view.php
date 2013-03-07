<div>
	<?= form_open($form_destination) ?>
		<?= ($login_messages) ? $login_messages : false ?>
		<input name="username" type="text" />
		<input name="password" type="password" />
		<button name="submit" type="submit">SUBMIT!</button>
	</form>
</div>