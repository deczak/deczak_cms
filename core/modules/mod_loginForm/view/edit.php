
<input type="hidden" name="cms-object-id" value="<?php echo $object -> object_id; ?>">

<div style="margin:0 auto; max-width:300px;">
<form action="" method="post">
	<input type="hidden" name="cms-risa" value="login">
	<input type="hidden" name="cms-tlon" value="<?php echo $object -> params; ?>">
	<input type="hidden" name="cms-oid" value="<?php echo $object -> object_id; ?>">


	<fieldset class="ui fieldset">
		<div>
		
			<div class="input width-100">
				<label>Username</label>
				<input type="text" name="username"  value="">
			</div>

			<div class="input width-100">
				<label>Password</label>
				<input type="password" name="password"  value="">
			</div>

			<div class="input width-100">
				<label>Login Object-ID</label>
				<input type="text" name="login-object-id"  value="<?php echo $object -> params; ?>">
			</div>

			<div class="input width-100">
				<label>Auto redirect</label>
				<input type="text" name="login-object-redirect"  value="<?php echo $object -> body; ?>">
			</div>


			<div class="input width-100">
				<br>
				<button>Login</button>
			</div>

		</div>

	</fieldset>


</form>





</div>

<style>

</style>