<h1><?php echo get_admin_page_title(); ?>
	<a href="<?php echo admin_url( 'edit.php?post_type=sponsors&page=ss-packages&ss-action=new-package' ); ?>" class="add-new-h2">
		<?php esc_html_e( 'Add New', 'simple-sponsorships' ); ?>
	</a>
</h1>
<form method="post">
	<?php
	$list->prepare_items();
	$list->display();
	?>
</form>