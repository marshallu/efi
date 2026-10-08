<?php
/**
 * Search results page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Viridis
 */

use Timber\Timber;

$templates = array( 'templates/search.twig', 'templates/archive.twig', 'templates/index.twig' );

$context = Timber::context(
	array(
		'title' => 'Search results for ' . get_search_query(),
	)
);

Timber::render( $templates, $context );
