<?php
/**
 * Widget to display sponsors
 */

namespace Simple_Sponsorships\Widgets;
use Simple_Sponsorships\DB\DB_Sponsors;
use Simple_Sponsorships\Sponsor;
use Simple_Sponsorships\Templates;

/**
 * Class Widget_Sponsors
 *
 * @package Simple_Sponsorships\Widgets
 */
class Widget_Placeholder extends \WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'ss-widget-placeholder',
			'description' => __( 'Display a Placeholder', 'simple-sponsorships' ),
		);
		parent::__construct( 'ss_widget_placeholder', __( 'Placeholder', 'simple-sponsorships' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;
		$package = ! empty( $instance['package'] ) ? absint( $instance['package'] ) : 0;

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		$link = ss_get_sponsor_page();

		if ( $package ) {
			$link = add_query_arg( 'package', $package, $link );
		}

		echo ss_get_placeholder( $link );

		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title     = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Sponsor Us', 'simple-sponsorships' );
		$package   = ! empty( $instance['package'] ) ? $instance['package'] : 0;
		$packages  = ss_get_available_packages();
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'simple-sponsorships' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'package' ) ); ?>"><?php esc_attr_e( 'Package:', 'simple-sponsorships' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'package' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'package' ) ); ?>">
				<option <?php selected( absint( $package ), 0, true );  ?> value="0"><?php esc_html_e( 'Any Package', 'simple-sponsorships' ); ?></option>
				<?php
				if ( $packages ) {
					foreach ( $packages as $package_object ) {
						?>
						<option <?php selected( absint( $package ), $package_object->get_id(), true );  ?> value="<?php echo esc_attr( $package_object->get_id() ); ?>"><?php echo esc_html( $package_object->get_data('title') ); ?></option>
						<?php
					}
				}
				?>
			</select>
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title']   = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['package'] = ( ! empty( $new_instance['package'] ) ) ? absint( $new_instance['package'] ) : 0;

		return $instance;
	}
}