<div class='container'>

	<div class='span-24 height-8'>&nbsp;</div>

	<div class='span-24'>
		<div class='span-8'>&nbsp;</div>
		<div class='span-8'>

			<form name='generatrix-admin-login-form' action='<?php href('/admin/login') ?>' method='post' class='generatrix-admin-form'>

				<input type='hidden' name='method' value='generatrix.admin.login' />

				<div class='span-3'><div class='generatrix-admin-label'>Username</div></div>
				<div class='span-5 last'><input type='text' class='generatrix-admin-input-text' name='username' value='' /></div>

				<div class='span-3'><div class='generatrix-admin-label'>Password</div></div>
				<div class='span-5 last'><input type='password' class='generatrix-admin-input-text' name='password' value='' /></div>

				<div class='span-3'><div class='generatrix-admin-label'>&nbsp;</div></div>
				<div class='span-5 last'>
					<input type='submit' name='submit' value='Login' class='generatrix-admin-input-submit' />
					or
					<a href='<?php echo href('/') ?>'>Cancel</a>
				</div>

			</form>

		</div>
		<div class='span-8 last'>&nbsp;</div>
	</div>

</div>
