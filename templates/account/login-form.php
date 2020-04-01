<?php

if ( is_user_logged_in() ) {
	return;
}

?>
<div class="ss-login-form">
	<?php

	wp_login_form(array(
		'redirect' => ss_get_account_endpoint_url( 'dashboard' ),
	));

	?>
</div>
