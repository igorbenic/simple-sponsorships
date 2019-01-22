<?php
/**
 * Template to show the shortcode.
 */


var_dump( $args );

$all     = isset( $args['all'] ) && '1' === $args['all'] ? true : false;
$content = isset( $args['content'] ) ? $args['content'] : 'current';
$logo    = isset( $args['logo'] ) ? $args['logo'] : '1';
$text    = isset( $args['text'] ) ? $args['text'] : '1';

