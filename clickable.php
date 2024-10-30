<?php
/**
 * Plugin Name: Clickable
 * Description: Converts URLs in a post content to clickable links
 * Plugin URI: https://wordpress.org/plugins/clickable
 * Author: Codexpert, Inc
 * Author URI: https://codexpert.io
 * Version: 0.11
 * Requires at least: 5.0
 * Tested up to: 6.5.3
 * Requires PHP: 7.1
 * Text Domain: clickable
 * Domain Path: /languages
 *
 * Clickable is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Clickable is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

add_filter( 'the_content', 'clickable_content' );
function clickable_content( $content ) {
    if ( ! is_singular() ) {
        return $content;
    }

    global $post;

    // Transform URLs into clickable links and add paragraphs automatically
    $content = wpautop( make_clickable( $post->post_content ) );

    // Create a new DOMDocument and load the HTML content
    $dom = new DOMDocument();
    libxml_use_internal_errors( true );
    $dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
    libxml_clear_errors();

    // Find all <a> tags in the document
    $links = $dom->getElementsByTagName( 'a' );

    // Iterate over each link to add target="_blank" if it's external
    foreach ( $links as $link ) {
        $href = $link->getAttribute( 'href' );

        // Check if the link is external
        if ( strpos( $href, get_home_url() ) === false ) {
            $link->setAttribute( 'target', '_blank' );
        }
    }

    // Save the updated HTML and return it
    $content = $dom->saveHTML();

    return $content;
}