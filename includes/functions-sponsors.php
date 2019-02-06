<?php
/**
 * Globally available functions related to Sponsors
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Return sponsors.
 *
 * @param array $args
 *
 * @return array
 */
function ss_get_sponsors( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'post_type'      => 'sponsors',
		'posts_per_page' => '-1',
        'ss_package'     => 0,
        'ss_content'     => 0
	));

	if ( $args['ss_content'] || $args['ss_package'] ) {
	    $args['suppress_filters'] = false;
        $db = new \Simple_Sponsorships\DB\DB_Sponsors();
        $db->filter_sponsors_query();
    }

    $sponsors = get_posts( apply_filters( 'ss_get_sponsors_args', $args ) );

	if ( $args['ss_content'] || $args['ss_package'] ) {
        $db->unfilter_sponsors_query();
	}

	return $sponsors;
}

/**
 * Get a sponsor object
 * @param int  $sponsor_id Sponsor ID.
 * @param bool $populate If true, it will get the initial data from the table.
 * @return \Simple_Sponsorships\Sponsor
 */
function ss_get_sponsor( $sponsor_id, $populate = true ) {
	return new \Simple_Sponsorships\Sponsor( $sponsor_id, $populate );
}

/**
 * Return Active Sponsors
 *
 * @param array $args Arguments.
 *
 * @return array
 */
function ss_get_active_sponsors( $args = array() ) {
	return ss_get_sponsors( array_merge( $args, array(
		'post_status'    => 'publish',
	)));
}

/**
 * Return Available Sponsors
 *
 * @return array
 */
function ss_get_available_sponsors( $args = array() ) {
	return ss_get_sponsors( wp_parse_args( $args, array(
		'post_status'    => 'publish',
		'meta_key'       => '_available_qty',
		'meta_value_num' => 0,
		'meta_compare'   => '>',
	)));
}

/**
 * Get Sponsors for a Post Type or different content.
 *
 * @param int $post_id Post ID.
 */
function ss_get_sponsors_for_content( $post_id ) {
	$ret = get_post_meta( $post_id, '_ss_sponsor', false );
	if ( ! $ret ) {
		$ret = array();
	}

	return $ret;
}

/**
 * Get Sponsors for a Post Type or different content.
 *
 * @param int $post_id Post ID.
 * @param array|int $sponsor_ids Array of Ids or a single Id.
 */
function ss_update_sponsors_for_content( $post_id, $sponsor_ids ) {
	if ( ! is_array( $sponsor_ids ) ) {
		$sponsor_ids = array( $sponsor_ids );
	}

	// First, let's delete all previous.
	ss_delete_sponsors_for_content( $post_id );

	foreach ( $sponsor_ids as $sponsor_id ) {
		ss_add_sponsor_for_content( $post_id, $sponsor_id );
	}
}

/**
 * Adding a sponsor of a content.
 *
 * @param int $post_id Content ID.
 * @param int $sponsor_id Sponsor ID.
 * @return mixed
 */
function ss_add_sponsor_for_content( $post_id, $sponsor_id ) {
	return add_post_meta( $post_id, '_ss_sponsor', absint( $sponsor_id ) );
}

/**
 * Deleting Sponsors for a content.
 *
 * @param int  $post_id Post ID.
 * @param bool|int|array $sponsors_ids If false, it will delete all. If array or integer, it will delete only specific sponsors.
 */
function ss_delete_sponsors_for_content( $post_id, $sponsors_ids = false ) {
	if ( ! $sponsors_ids ) {
		delete_post_meta( $post_id, '_ss_sponsor' );
	} else {
		if ( ! is_array( $sponsors_ids ) ) {
			$sponsors_ids = array( $sponsors_ids );
		}
		foreach ( $sponsors_ids as $sponsor_id ) {
			delete_post_meta( $post_id, '_ss_sponsor', $sponsor_id );
		}
	}
}

/**
 * Show sponsors under the content.
 * @param $content
 *
 * @return string
 */
function ss_show_sponsors_under_content( $content ) {
	if ( is_singular( array_keys( ss_get_content_types() ) ) && '1' === ss_get_option( 'show_in_content_footer', '0' ) ) {
		$content_id = get_the_ID();
		$sponsors   = ss_get_sponsors_for_content( $content_id );
		if ( $sponsors ) {
			ob_start();
			?>
			<h2><?php esc_html_e( 'Sponsored By', 'simple-sponsorships' ); ?></h2>
			<div class="ss-sponsors">
				<?php
				foreach ( $sponsors as $sponsor_id ) {
					$sponsor = new \Simple_Sponsorships\Sponsor( $sponsor_id, false );
					$has_logo  = has_post_thumbnail( $sponsor->get_id() );
					$link      = $sponsor->get_link();
					?>
					<div class="ss-sponsor">
						<?php

						if ( $has_logo ) {
							if ( $link ) {
								echo '<a href="' . $link . '">';
							}
							echo get_the_post_thumbnail( $sponsor->get_id() );
							if ( $link ) {
								echo '</a>';
							}
						}
						if ( ! $has_logo ) {
							if ( $link ) {
								echo '<a target="_blank" href="' . $link . '">';
							}

							echo $sponsor->get_data( 'post_title' );

							if ( $link ) {
								echo '</a>';
							}
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
			$content .= ob_get_clean();
		}
	}

	return $content;
}