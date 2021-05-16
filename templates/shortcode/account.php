<?php
/**
 * My Account
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

?>
<div class="ss-account-container">
	<?php
	/**
	 * My Account navigation.
	 *
	 * @since 1.5.0
	 */
	do_action( 'ss_account_navigation' );

	?>
	<div class="ss-account">
		<?php do_action( 'ss_account_content' ); ?>
	</div>
</div>
