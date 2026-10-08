<?php
/**
 * Rich Text block for viridis theme
 *
 * @package viridis
 */

/**
 * This is the callback that displays the block.
 *
 * @param   array  $block      The block settings and attributes.
 * @param   string $content    The block content (empty string).
 * @param   bool   $is_preview True during AJAX preview.
 */
function viridis_rich_text_block( $block, $content = '', $is_preview = false ) {
	$context               = Timber::context();
	$context['block']      = $block;
	$context['fields']     = get_fields() ? get_fields() : array();
	$context['is_preview'] = $is_preview;

	$classes = array( 'viridis-block', 'wp-block-viridis-rich-text' );

	if ( ! empty( $block['className'] ) ) {
		$classes[] = $block['className'];
	}

	if ( ! empty( $context['fields']['background_color'] ) ) {
		$classes[] = 'has-' . $context['fields']['background_color'] . '-background-color';
		$classes[] = 'has-background';
	}

	$context['block_classes'] = implode( ' ', $classes );

	$context['anchor'] = ! empty( $block['anchor'] ) ? esc_attr( $block['anchor'] ) : '';

	Timber::render( 'blocks/rich-text.twig', $context );
}
