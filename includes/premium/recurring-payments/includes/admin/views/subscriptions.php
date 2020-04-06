<h1><?php echo get_admin_page_title(); ?></h1>
<form method="post">
	<?php
	$list->prepare_items();
	$list->display();
	?>
</form>