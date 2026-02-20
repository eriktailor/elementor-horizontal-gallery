<?php
/**
 * Plugin Name: Elementor Horizontal Scroll Gallery
 * Description: A custom Elementor widget to display a reorderable horizontal scroll of images.
 * Version: 1.3.0
 * Author: ErikTailor
 * Author URI: https://eriktailor.hu
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function register_eriktailor_horizontal_gallery( $widgets_manager ) {
    // 1. Only require the file when Elementor is 100% ready
    require_once( __DIR__ . '/widget-horizontal-gallery.php' );

    // 2. Register the widget
    $widgets_manager->register( new \Elementor_Horizontal_Gallery_Widget() );
}
// This hook ensures Elementor's Widget_Base class is fully loaded
add_action( 'elementor/widgets/register', 'register_eriktailor_horizontal_gallery' );