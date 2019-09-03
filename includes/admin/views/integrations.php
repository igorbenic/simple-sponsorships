<?php
/**
 * Integrations Screen.
 */

$integrations        = ss_get_registered_integrations();
$active_integrations = ss_get_active_integrations();

?>
<h1><?php echo get_admin_page_title(); ?></h1>

<div class="wp-list-table widefat plugin-install">
	<div id="the-list">
		<?php
		if ( $integrations ) {
			foreach ( $integrations as $slug => $class ) {
				$integration_object = new $class();
				$integration_object->set_active( in_array( $slug, $active_integrations, true ) );
				?>
				<div class="plugin-card plugin-card-<?php echo $slug; ?>">
					<div class="plugin-card-top">
						<div class="name column-name">
							<h3>
								<?php echo $integration_object->title; ?>
								<img src="<?php echo $integration_object->image; ?>" class="plugin-icon" alt="">

							</h3>
						</div>
						<div class="desc column-description">
							<p><?php echo $integration_object->desc; ?></p>
						</div>
					</div>
					<div class="plugin-card-bottom">

						<?php $integration_object->buttons(); ?>

					</div>
				</div>
				<?php
			}
		}
		?>
	</div>
</div>