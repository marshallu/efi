<?php
/**
 * StarterSite class
 * This class is used to add custom functionality to the theme.
 *
 * @package Viridis
 */

namespace App;

use Timber\Site;
use Timber\Timber;
use Twig\Environment;
use Twig\TwigFilter;

/**
 * Class StarterSite.
 */
class StarterSite extends Site {
	/**
	 * StarterSite constructor.
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'acf/init', array( $this, 'register_acf_blocks' ) );
		add_filter( 'allowed_block_types_all', array( $this, 'allowed_block_types' ), 10, 1 );
		add_action( 'acf/init', array( $this, 'default_page_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig/filters', array( $this, 'add_filters_to_twig' ) );
		add_filter( 'timber/twig/functions', array( $this, 'add_functions_to_twig' ) );
		add_filter( 'timber/twig/environment/options', array( $this, 'update_twig_environment_options' ) );

		parent::__construct();
	}

	/**
	 * This enqueues theme styles.
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'viridis', get_template_directory_uri() . '/css/viridis.css', array(), filemtime( get_theme_file_path( '/css/viridis.css' ) ), 'all' );
	}

	/**
	 * This is where you can register custom post types.
	 */
	public function register_post_types() {}

	/**
	 * This is where you can register custom taxonomies.
	 */
	public function register_taxonomies() {}

	/**
	 * Register ACF Blocks.
	 */
	public function register_acf_blocks() {
		$blocks = scandir( dirname( __DIR__ ) . '/blocks/' );
		$blocks = array_values( array_diff( $blocks, array( '..', '.', '.DS_Store', '_base-block' ) ) );

		foreach ( $blocks as $block ) {
			if ( file_exists( dirname( __DIR__ ) . '/blocks/' . $block . '/block.json' ) ) {
				register_block_type( dirname( __DIR__ ) . '/blocks/' . $block . '/block.json' );
			}

			if ( file_exists( dirname( __DIR__ ) . '/blocks/' . $block . '/callback.php' ) ) {
				require_once dirname( __DIR__ ) . '/blocks/' . $block . '/callback.php';
			}
		}
	}

	/**
	 * Limit the blocks available
	 *
	 * @param array $allowed_blocks Array of all blocks.
	 * @return array
	 */
	public function allowed_block_types( $allowed_blocks ) {
		$allowed_blocks = array(
			'acf/basic',
			'acf/billboard',
			'acf/call-to-action',
			'acf/facts-and-stats',
			'acf/iframe-embed',
			'acf/news-and-stories',
			'acf/page-header',
			'acf/rich-text',
		);

		return $allowed_blocks;
	}

	/**
	 * Set the default page template for the viridis theme.
	 */
	public function default_page_template() {
		$post_type_object = get_post_type_object( 'page' );

		$post_type_object->template = array(
			array(
				'acf/page-header',
				array(
					'lock' => array(
						'move'   => true,
						'remove' => true,
					),
				),
			),
		);
	}

	/**
	 * This is where you add some context.
	 *
	 * @param array $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context( $context ) {
		$context['menu']        = Timber::get_menu( 'primary_navigation' );
		$context['footer_menu'] = Timber::get_menu( 'footer_navigation' );
		$context['site']        = $this;

		return $context;
	}

	/**
	 * This is where you can add your theme supports.
	 */
	public function theme_supports() {
		// Register navigation menus.
		register_nav_menus(
			array(
				'primary_navigation' => _x( 'Main menu', 'Primary Navigation', 'viridis' ),
				'footer_navigation'  => _x( 'Footer menu', 'Footer Navigation', 'viridis' ),
			)
		);

		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support( 'menus' );
	}

	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @link https://timber.github.io/docs/v2/hooks/filters/#timber/twig/filters
	 * @param array $filters an array of Twig filters.
	 */
	public function add_filters_to_twig( $filters ) {
		$additional_filters = array(
			'focal_point' => array(
				'callable' => array( $this, 'focal_point' ),
			),
		);

		return array_merge( $filters, $additional_filters );
	}

	/**
	 * Return the object-position for images.
	 *
	 * @param int $id The image ID.
	 *
	 * @return string
	 */
	public function focal_point( $id ) {
		if ( ! function_exists( 'fcp_get_focalpoint' ) ) {
			return '50% 50%';
		}

		$id = absint( $id );

		if ( get_post_type( $id ) !== 'attachment' ) {
			return '50% 50%';
		}

		$focus        = fcp_get_focalpoint( $id ); // phpcs:ignore
		$left_percent = $focus->leftPercent ? $focus->leftPercent : 50; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$top_percent  = $focus->topPercent ? $focus->topPercent : 50; // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		return $left_percent . '% ' . $top_percent . '%;';
	}

	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @link https://timber.github.io/docs/v2/hooks/filters/#timber/twig/functions
	 * @param array $functions an array of existing Twig functions.
	 */
	public function add_functions_to_twig( $functions ) {
		$additional_functions = array(
			'get_theme_mod' => array(
				'callable' => 'get_theme_mod',
			),
		);

		return array_merge( $functions, $additional_functions );
	}

	/**
	 * Updates Twig environment options.
	 *
	 * @see https://twig.symfony.com/doc/2.x/api.html#environment-options
	 *
	 * @param array $options an array of environment options.
	 *
	 * @return array
	 */
	public function update_twig_environment_options( $options ) {
		return $options;
	}
}
