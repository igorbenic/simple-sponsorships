<?php
/**
 * Widget to display sponsors
 */

namespace Simple_Sponsorships\Widgets;
use Simple_Sponsorships\Sponsor;
use Simple_Sponsorships\Templates;

/**
 * Class Widget_Sponsors
 *
 * @package Simple_Sponsorships\Widgets
 */
class Widget_Sponsors extends \WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'ss-widget-sponsors',
			'description' => __( 'Display Sponsors', 'simple-sponsorships' ),
		);
		parent::__construct( 'ss_widget_sponsors', __( 'Sponsorships: Display Sponsors', 'simple-sponsorships' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		global $post;
		$show_sponsors = ! empty( $instance['sponsors'] ) ? $instance['sponsors'] : 'active';
		$logo          = ! isset( $instance['logo'] ) ? '1' : $instance['logo'];
		$only_logo     = ! isset( $instance['only_logo'] ) ? '0' : $instance['only_logo'];
		$sponsors      = array();

		if ( 'active' === $show_sponsors ) {
			$sponsors = ss_get_active_sponsors();
		}

		if ( ! $sponsors ) {
			return;
		}

		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		foreach ( $sponsors as $sponsor ) {
			$_sponsor = new Sponsor( 0 );
			$_sponsor->populate_from_post( $sponsor );
			Templates::get_template_part( 'widgets/sponsor', null, array( 'sponsor' => $_sponsor, 'show_logo' => $logo, 'only_logo' => $only_logo ) );
		}
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		$title     = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Sponsors', 'simple-sponsorships' );
		$sponsors  = ! empty( $instance['sponsors'] ) ? $instance['sponsors'] : 'active';
		$logo      = ! isset( $instance['logo'] ) ? '1' : $instance['logo'];
		$only_logo = ! isset( $instance['only_logo'] ) ? '0' : $instance['only_logo'];
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'simple-sponsorships' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'sponsors' ) ); ?>"><?php esc_attr_e( 'Sponsors:', 'simple-sponsorships' ); ?></label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'sponsors' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'sponsors' ) ); ?>">
				<option <?php selected( $sponsors, 'active', true );  ?> value="active"><?php esc_html_e( 'All Active Sponsors', 'simple-sponsorships' ); ?></option>
				<option <?php selected( $sponsors, 'current', true );  ?> value="current"><?php esc_html_e( 'Current Content Sponsors', 'simple-sponsorships' ); ?></option>
			</select>
		</p>
		<p>
			<input <?php checked( $logo, '1', true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'logo' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'logo' ) ); ?>" type="checkbox" value="1">
			<label for="<?php echo esc_attr( $this->get_field_id( 'logo' ) ); ?>"><?php esc_attr_e( 'Display Logo', 'simple-sponsorships' ); ?></label>
		</p>
		<p>
			<input <?php checked( $only_logo, '1', true ); ?> id="<?php echo esc_attr( $this->get_field_id( 'only_logo' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'only_logo' ) ); ?>" type="checkbox" value="1">
			<label for="<?php echo esc_attr( $this->get_field_id( 'only_logo' ) ); ?>"><?php esc_attr_e( 'Only Logo', 'simple-sponsorships' ); ?></label>
			<br/><span class="description"><?php esc_html_e( 'It will show Sponsor name if there is no logo', 'simple-sponsorships' ); ?></span>
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
		$instance['title']    = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['sponsors'] = ( ! empty( $new_instance['sponsors'] ) ) ? sanitize_text_field( $new_instance['sponsors'] ) : 'active';
		$instance['logo']     = ( ! empty( $new_instance['logo'] ) ) ? '1' : '0';
		$instance['only_logo']     = ( ! empty( $new_instance['only_logo'] ) ) ? '1' : '0';

		return $instance;
	}
}