<?php
/**
 * WP-CLI Migration Script for Statamic to WordPress
 *
 * Usage: wp eval-file web/app/themes/viridis/cli/migrate-statamic.php /path/to/entries.json
 */

if (!defined('WP_CLI')) {
    return;
}

// Get the JSON file path from command line arguments
$json_file = $args[0] ?? null;

if (!$json_file || !file_exists($json_file)) {
    WP_CLI::error('Please provide a valid path to entries.json');
}

$entries = json_decode(file_get_contents($json_file), true);

if (!$entries) {
    WP_CLI::error('Failed to parse entries.json');
}

WP_CLI::log('Starting migration of ' . count($entries) . ' pages...');

/**
 * Convert ProseMirror/Bard content to HTML
 */
function convert_bard_to_html($content) {
    if (!is_array($content)) {
        return '';
    }

    $html = '';

    foreach ($content as $node) {
        switch ($node['type']) {
            case 'paragraph':
                $inner = isset($node['content']) ? convert_bard_inline($node['content']) : '';
                $html .= '<p>' . $inner . '</p>';
                break;
            case 'heading':
                $level = $node['attrs']['level'] ?? 2;
                $inner = isset($node['content']) ? convert_bard_inline($node['content']) : '';
                $html .= "<h{$level}>{$inner}</h{$level}>";
                break;
            case 'bulletList':
                $html .= '<ul>' . convert_bard_list_items($node['content'] ?? []) . '</ul>';
                break;
            case 'orderedList':
                $html .= '<ol>' . convert_bard_list_items($node['content'] ?? []) . '</ol>';
                break;
            default:
                if (isset($node['content'])) {
                    $html .= convert_bard_to_html($node['content']);
                }
        }
    }

    return $html;
}

/**
 * Convert inline content (text with marks)
 */
function convert_bard_inline($content) {
    if (!is_array($content)) {
        return '';
    }

    $html = '';

    foreach ($content as $node) {
        if ($node['type'] === 'text') {
            $text = htmlspecialchars($node['text'] ?? '', ENT_QUOTES, 'UTF-8');

            // Apply marks
            if (isset($node['marks'])) {
                foreach ($node['marks'] as $mark) {
                    switch ($mark['type']) {
                        case 'bold':
                            $text = '<strong>' . $text . '</strong>';
                            break;
                        case 'italic':
                            $text = '<em>' . $text . '</em>';
                            break;
                        case 'link':
                            $href = $mark['attrs']['href'] ?? '#';
                            $text = '<a href="' . esc_attr($href) . '">' . $text . '</a>';
                            break;
                        case 'underline':
                            $text = '<u>' . $text . '</u>';
                            break;
                    }
                }
            }

            $html .= $text;
        }
    }

    return $html;
}

/**
 * Convert list items
 */
function convert_bard_list_items($items) {
    $html = '';

    foreach ($items as $item) {
        if ($item['type'] === 'listItem') {
            $inner = isset($item['content']) ? convert_bard_to_html($item['content']) : '';
            // Remove wrapping <p> tags inside list items for cleaner output
            $inner = preg_replace('/<\/?p>/', '', $inner);
            $html .= '<li>' . $inner . '</li>';
        }
    }

    return $html;
}

/**
 * Create Gutenberg block content from page_builder data
 */
function create_gutenberg_blocks($page_builder) {
    if (!is_array($page_builder)) {
        return '';
    }

    $blocks = [];

    foreach ($page_builder as $block) {
        if (!isset($block['enabled']) || $block['enabled'] === false) {
            continue;
        }

        switch ($block['type']) {
            case 'basic':
                $blocks[] = create_basic_block($block);
                break;
            case 'iframe_embed':
                $blocks[] = create_iframe_embed_block($block);
                break;
            case 'billboard':
                $blocks[] = create_billboard_block($block);
                break;
            case 'call_to_action':
                $blocks[] = create_cta_block($block);
                break;
            case 'facts_and_stats':
                $blocks[] = create_facts_stats_block($block);
                break;
            case 'page_header':
                $blocks[] = create_page_header_block($block);
                break;
            case 'news_and_stories':
                $blocks[] = create_news_stories_block($block);
                break;
        }
    }

    return implode("\n\n", $blocks);
}

/**
 * Create ACF Basic block
 */
function create_basic_block($data) {
    $block_data = [
        'name' => 'acf/basic',
        'data' => [
            'heading' => $data['heading'] ?? '',
            'heading_size' => 'h2',
            'heading_color' => 'gray',
            'media_type' => $data['media_type'] ?? 'none',
            'content' => convert_bard_to_html($data['content'] ?? []),
            'content_side' => $data['content_side'] ?? 'left',
            'background_color' => $data['background_color'] ?? 'white',
        ],
        'mode' => 'preview',
    ];

    // Handle image
    if (isset($data['image']) && !empty($data['image'])) {
        // Store the image filename - will need to be mapped to attachment ID after upload
        $block_data['data']['_image_filename'] = $data['image'];
    }

    return efi_serialize_acf_block($block_data);
}

/**
 * Create ACF Iframe Embed block
 */
function create_iframe_embed_block($data) {
    $block_data = [
        'name' => 'acf/iframe-embed',
        'data' => [
            'iframe_code' => $data['iframe_code'] ?? '',
        ],
        'mode' => 'preview',
    ];

    return efi_serialize_acf_block($block_data);
}

/**
 * Create ACF Billboard block
 */
function create_billboard_block($data) {
    $block_data = [
        'name' => 'acf/billboard',
        'data' => [
            'heading' => $data['heading'] ?? '',
            'heading_size' => $data['heading_size'] ?? 'h1',
            'heading_color' => $data['heading_color'] ?? 'white',
            'subheading' => $data['subheading'] ?? '',
            'dark_overlay' => $data['dark_overlay'] ?? false,
        ],
        'mode' => 'preview',
    ];

    return efi_serialize_acf_block($block_data);
}

/**
 * Create ACF Call to Action block
 */
function create_cta_block($data) {
    $block_data = [
        'name' => 'acf/call-to-action',
        'data' => [
            'heading' => $data['heading'] ?? '',
            'heading_size' => $data['heading_size'] ?? 'h2',
            'heading_color' => $data['heading_color'] ?? 'gray',
            'background_color' => $data['background_color'] ?? 'gray-100',
        ],
        'mode' => 'preview',
    ];

    return efi_serialize_acf_block($block_data);
}

/**
 * Create ACF Facts and Stats block
 */
function create_facts_stats_block($data) {
    $block_data = [
        'name' => 'acf/facts-and-stats',
        'data' => [
            'heading' => $data['heading'] ?? '',
            'heading_size' => $data['heading_size'] ?? 'h2',
            'heading_color' => $data['heading_color'] ?? 'gray',
        ],
        'mode' => 'preview',
    ];

    return efi_serialize_acf_block($block_data);
}

/**
 * Create ACF Page Header block
 */
function create_page_header_block($data) {
    $block_data = [
        'name' => 'acf/page-header',
        'data' => [
            'type' => $data['type'] ?? 'image',
            'subheading' => $data['subheading'] ?? '',
            'dark_overlay' => $data['dark_overlay'] ?? false,
        ],
        'mode' => 'preview',
    ];

    return efi_serialize_acf_block($block_data);
}

/**
 * Create ACF News and Stories block
 */
function create_news_stories_block($data) {
    $block_data = [
        'name' => 'acf/news-and-stories',
        'data' => [
            'heading' => $data['heading'] ?? '',
            'heading_size' => $data['heading_size'] ?? 'h2',
            'heading_color' => $data['heading_color'] ?? 'gray',
            'feed_url' => $data['feed_url'] ?? '',
        ],
        'mode' => 'preview',
    ];

    return efi_serialize_acf_block($block_data);
}

/**
 * Serialize block data for WordPress
 */
function efi_serialize_acf_block($block_data) {
    $name = $block_data['name'];
    $attrs = $block_data;
    unset($attrs['name']);

    $attrs_json = json_encode($attrs, JSON_UNESCAPED_SLASHES);

    return "<!-- wp:{$name} {$attrs_json} /-->";
}

// Process each entry
foreach ($entries as $entry) {
    $data = json_decode($entry['data'], true);

    if (!$data) {
        WP_CLI::warning('Failed to parse data for entry: ' . $entry['slug']);
        continue;
    }

    $title = $data['title'] ?? $entry['slug'];
    $slug = $entry['slug'];
    $status = $entry['published'] ? 'publish' : 'draft';

    // Check if page already exists
    $existing = get_page_by_path($slug);
    if ($existing) {
        WP_CLI::warning("Page already exists: {$slug} (ID: {$existing->ID}) - skipping");
        continue;
    }

    // Create page header block from page-level fields
    $page_header_block = '';
    if (isset($data['type']) && isset($data['background_image'])) {
        $page_header_data = [
            'type' => $data['type'],
            'subheading' => $data['subheading'] ?? '',
            'dark_overlay' => $data['dark_overlay'] ?? false,
            'background_image' => $data['background_image'],
            'video_file' => $data['video_file'] ?? null,
            'video_poster_image' => $data['video_poster_image'] ?? null,
            'buttons' => $data['page_header_buttons'] ?? [],
        ];

        $header_block = [
            'name' => 'acf/page-header',
            'data' => [
                'type' => $page_header_data['type'],
                'subheading' => $page_header_data['subheading'],
                'dark_overlay' => $page_header_data['dark_overlay'],
                '_background_image_filename' => $page_header_data['background_image'],
            ],
            'mode' => 'preview',
        ];

        $page_header_block = efi_serialize_acf_block($header_block);
    }

    // Create content blocks
    $content_blocks = create_gutenberg_blocks($data['page_builder'] ?? []);

    // Combine all blocks
    $post_content = trim($page_header_block . "\n\n" . $content_blocks);

    // Prepare page data
    $page_data = [
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $post_content,
        'post_status'  => $status,
        'post_type'    => 'page',
    ];

    // Handle home page (set as front page)
    if ($entry['uri'] === '/') {
        $page_data['post_name'] = 'home';
    }

    // Create the page
    $page_id = wp_insert_post($page_data);

    if (is_wp_error($page_id)) {
        WP_CLI::error('Failed to create page: ' . $title . ' - ' . $page_id->get_error_message());
        continue;
    }

    // Set SEO meta if Yoast is active
    if (function_exists('wpseo_auto_complete_meta')) {
        if (!empty($data['seo_title'])) {
            update_post_meta($page_id, '_yoast_wpseo_title', $data['seo_title']);
        }
        if (!empty($data['seo_description'])) {
            update_post_meta($page_id, '_yoast_wpseo_metadesc', $data['seo_description']);
        }
    }

    // If this is the home page, set it as the front page
    if ($entry['uri'] === '/') {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $page_id);
        WP_CLI::success("Set page '{$title}' (ID: {$page_id}) as the front page");
    }

    WP_CLI::success("Created page: {$title} (ID: {$page_id}) - Status: {$status}");
}

WP_CLI::success('Migration complete!');

// Output note about images
WP_CLI::log('');
WP_CLI::log('NOTE: Images referenced in blocks need to be uploaded manually to the Media Library.');
WP_CLI::log('The following images were referenced:');
WP_CLI::log('  - hero-image-new.jpeg (page headers)');
WP_CLI::log('  - edwards-long-1750098169.jpeg (About Us basic block)');
WP_CLI::log('  - mission-research-1750098193.jpg (Home basic block)');
WP_CLI::log('');
WP_CLI::log('After uploading, update the ACF block fields to select the correct images.');
