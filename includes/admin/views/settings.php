<h2><?php echo get_admin_page_title(); ?></h2>
<h2 class="nav-tab-wrapper">
	<?php
	foreach ( $settings_tabs as $tab_id => $tab_name ) {
		$tab_url = add_query_arg( array(
			'settings-updated' => false,
			'tab'              => $tab_id,
		) );

		// Remove the section from the tabs so we always end up at the main section
		$tab_url = remove_query_arg( 'section', $tab_url );

		$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

		echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
		echo esc_html( $tab_name );
		echo '</a>';
	}
	?>
</h2>
<?php

$number_of_sections = count( $sections );
$number = 0;
if ( $number_of_sections > 1 ) {
	echo '<div><ul class="subsubsub">';
	foreach( $sections as $section_id => $section_name ) {
		echo '<li>';
		$number++;
		$tab_url = add_query_arg( array(
			'settings-updated' => false,
			'tab' => $active_tab,
			'section' => $section_id
		) );
		$class = '';
		if ( $section == $section_id ) {
			$class = 'current';
		}
		echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';

		if ( $number != $number_of_sections ) {
			echo ' | ';
		}
		echo '</li>';
	}
	echo '</ul></div>';
}
?>
<div id="tab_container">
    <form method="post" action="options.php">
        <table class="form-table">
			<?php

			settings_fields( 'ss_settings' );

			if ( 'main' === $section ) {
				do_action( 'ss_settings_tab_top', $active_tab );
			}

			do_action( 'ss_settings_tab_top_' . $active_tab . '_' . $section );

			do_settings_sections( 'ss_settings_' . $active_tab . '_' . $section );

			do_action( 'ss_settings_tab_bottom_' . $active_tab . '_' . $section  );

			// For backwards compatibility
			if ( 'main' === $section ) {
				do_action( 'ss_settings_tab_bottom', $active_tab );
			}

			// If the main section was empty and we overrode the view with the next subsection, prepare the section for saving
			if ( true === $override ) {
				?><input type="hidden" name="ss_section_override" value="<?php echo $section; ?>" /><?php
			}
			?>
        </table>
		<?php submit_button(); ?>
    </form>
</div><!-- #tab_container-->