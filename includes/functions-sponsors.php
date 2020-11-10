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
        'ss_content'     => 0,
        'post_status'    => apply_filters( 'ss_get_sponsors_default_statuses',
            array( 'publish', 'ss-inactive' )),
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
	if ( is_singular( array_keys( ss_get_content_types() ) ) && is_main_query() && in_the_loop() ) {
		$content_id = get_the_ID();
		$sponsors   = array();

		if ( '1' === ss_get_option( 'show_in_content_footer', '0' ) ) {
			$sponsors = ss_get_sponsors_for_content( $content_id );

			if ( $sponsors ) {
				$show_title = 1 === absint( ss_get_option( 'show_in_content_footer_title', '0' ) );
				$show_text  = 1 === absint( ss_get_option( 'show_in_content_footer_text', '0' ) );
				$logo_size  = ss_get_option( 'show_in_content_footer_size', 'full' );
				$layout     = ss_get_option( 'show_in_content_footer_layout', 'vertical' );
                $classes    = array( 'ss-sponsors-layout-' . $layout );

                if ( $show_text ) {
                    $classes[] = 'ss-sponsors-show-text';
                }

                if ( 'horizontal' === $layout ) {
                    $classes[] = 'ss-sponsors-count-' . count( $sponsors );
                }

				ob_start();
				?>
                <h2><?php esc_html_e( 'Sponsored By', 'simple-sponsorships' ); ?></h2>
                <div class="ss-sponsors <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
					<?php
					foreach ( $sponsors as $sponsor_id ) {
						$sponsor  = new \Simple_Sponsorships\Sponsor( $sponsor_id, false );
						$has_logo = has_post_thumbnail( $sponsor->get_id() );
						$link     = $sponsor->get_link();
						$link     = ss_get_report_link( $link, $sponsor_id, $content_id, get_permalink( $content_id ), get_post_type() );
						?>
                        <div class="ss-sponsor" itemprop="sponsor" itemtype="http://schema.org/Organization">
							<?php

							if ( $has_logo ) {
								if ( $link ) {
									echo '<a target="_blank" itemprop="url" href="' . $link . '">';
								}
								echo get_the_post_thumbnail( $sponsor->get_id(), $logo_size );

								if ( $link ) {
									echo '</a>';
								}
							}

							if ( $show_text ) {
							    echo '<div class="ss-sponsor-content">';
                            }

							if ( ! $has_logo || $show_title ) {
								if ( $link ) {
									echo '<a itemprop="url" target="_blank" href="' . $link . '">';
								}

								echo '<span itemprop="name"">' . $sponsor->get_data( 'post_title' ) . '</span>';

								if ( $link ) {
									echo '</a>';
								}
							}

							if ( $show_text ) {
								echo wpautop( $sponsor->get_data( 'post_content' ) );
								echo '</div>'; // Closing ss-sponsor-content
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

		if ( '1' === ss_get_option( 'show_content_placeholder', '0' ) && ! is_page( ss_get_option( 'account_page', 0 ) ) ) {

			$hide_placeholder = get_post_meta( $content_id, '_ss_hide_placeholder', true );
			$show_placeholder = '1' === $hide_placeholder ? false : true;

			if ( apply_filters( 'ss_content_show_placeholder', $show_placeholder, $content_id, $sponsors ) ) {
				$sponsorship_page = ss_get_sponsor_page();

				if ( ! $sponsorship_page ) {
					return $content;
				}

				$availability = get_post_meta( $content_id, '_ss_availability', true );
                if ( $availability && $availability > 0 ) {
	                $sponsors = $sponsors ? $sponsors : ss_get_sponsors_for_content( $content_id );
	                if ( $sponsors && count( $sponsors ) >= $availability ) {
	                    return $content;
                    }
                }

				$sponsorship_page = add_query_arg( 'ss_content_id', $content_id, $sponsorship_page );
                $content .= ss_get_placeholder( $sponsorship_page );
			}
		}
	}

	return $content;
}


/**
 * Return the Sponsor Page
 *
 * @since 0.8.0
 *
 * @string
 */
function ss_get_sponsor_page(){
	$sponsor_page = ss_get_option( 'sponsor_page', 0 );
    $link         = '';

	if ( $sponsor_page ) {
		$link = get_permalink( $sponsor_page );
	}

	return apply_filters( 'ss_get_sponsor_page', $link );
}

/**
 * Return the placeholder link
 *
 * @param string $link
 * @param string $image
 * @param string $text
 *
 * @return string
 */
function ss_get_placeholder( $link, $image = '', $text = '' ) {
    ob_start();

    if ( ! $image ) {
        $icon_svg = \Simple_Sponsorships\Templates::get_file_contents( trailingslashit( SS_PLUGIN_PATH ) . 'assets/images/svg/id-user.svg', 'placeholder-image' );
	    $image    = ss_get_option( 'content_placeholder_icon', $icon_svg );
    }

    if ( ! $text ) {
        $text = ss_get_option( 'content_placeholder_text', __( 'Become a Sponsor', 'simple-sponsorships' ) );
    }
	?>
    <a class="ss-content-placeholder" href="<?php echo esc_url( $link ); ?>">
		<?php if ( $image ) {
			echo '<div class="ss-placeholder-image">' .  ss_kses_with_svg( $image ) . '</div>';
		} ?>
		<?php echo esc_html( $text ); ?>
    </a>
    <?php
    return ob_get_clean();
}

/**
 * Get the column value for each sponsored content
 *
 * @param \WP_Post $content Post object.
 * @param string   $column Column slug
 *
 * @return string
 */
function ss_get_sponsored_content_table_column_value( $content, $column ) {
	$ret = '';

	switch ( $column ) {
		case 'post_title':
        case 'post_excerpt':
			$ret = $content->$column;
			break;
        case 'link':
            $link = get_permalink( $content );
            if ( $link ) {
                $ret = '<a href="' . esc_url( $link ) . '">' . __( 'View', 'simple-sponsorships' ) . '</a>';
            }
            break;
	}

	$ret = apply_filters( 'ss_get_sponsorships_table_column_value_' . $column, $ret, $content );
	if ( is_array( $ret ) ) {
		$ret = implode( ' ', $ret );
	}
	return $ret;
}
