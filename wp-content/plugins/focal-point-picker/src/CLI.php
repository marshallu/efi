<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\FocalPointPicker;

use WP_CLI;

class CLI
{
    public static function init(): void
    {
        if (self::isWPCLI()) {
            WP_CLI::add_command(
                'fcp apply-default-position',
                [self::class, 'applyDefaultPositionCommand']
            );
        }
    }

    /**
     * Check if currentl in WP_CLI
     */
    private static function isWPCLI(): bool
    {
        return! \defined('WP_CLI')
            ? false
            : WP_CLI;

    }

    /**
     * Apply the default focal point position to image attachments.
     *
     * This command sets the focal point metadata for image attachments to the
     * default position configured in the plugin. It can process specific
     * attachment IDs or all image attachments in the database.
     *
     * ## OPTIONS
     *
     * [<attachment-ids>...]
     * : One or more attachment IDs to update. Space-separated.
     *
     * [--all]
     * : Apply default position to all image attachments.
     *
     * [--yes]
     * : Skip prompt before applying the default position to all attachments.
     *
     * ## EXAMPLES
     *
     *     # Apply the default position to specific attachments
     *     $ wp fcp apply-default-position 123 456 789
     *     Success: Applied default focal point position to 3 attachments.
     *
     *     # Apply default position to all image attachments
     *     $ wp fcp apply-default-position --all
     *     Warning: This will update all image attachments. Are you sure? [y/n]
     *     Success: Applied default focal point position to 42 attachments.
     *
     * @param array<int, string> $args Positional arguments (attachment IDs)
     * @param array<string, mixed> $assocArgs Associative arguments (flags)
     */
    public static function applyDefaultPositionCommand(array $args, array $assocArgs): void
    {
        $applyAll = $assocArgs['all'] ?? false;

        // Validate that either IDs or --all flag is provided
        if (empty($args) && !$applyAll) {
            WP_CLI::error('Please provide attachment IDs or use the --all flag.');
            return;
        }

        // Prevent using both IDs and --all flag
        if (!empty($args) && $applyAll) {
            WP_CLI::error('Cannot specify both attachment IDs and --all flag. Choose one.');
            return;
        }

        $defaultPosition = FocalPointPicker::getDefaultPosition();
        $imageIDs = [];

        // Handle --all flag
        if ($applyAll) {
            $imageIDs = self::getAllImageIDs();

            if (empty($imageIDs)) {
                WP_CLI::warning('No image attachments found.');
                return;
            }

            WP_CLI::confirm(
                \sprintf(
                    "⚠️  Apply the default focal point position (top: %.2f, left: %.2f) to all your %d image%s?",
                    $defaultPosition->left,
                    $defaultPosition->top,
                    \count($imageIDs),
                    \count($imageIDs) === 1 ? '' : 's'
                ),
                $assocArgs
            );
        } else {
            // Validate provided attachment IDs
            $imageIDs = \array_map('intval', $args);
            $invalidIDs = self::getInvalidImageIDs($imageIDs);

            if (!empty($invalidIDs)) {
                WP_CLI::error(
                    \sprintf(
                        'The following IDs are not valid image attachments: %s',
                        \implode(', ', $invalidIDs)
                    )
                );
            }
        }

        // Apply default position to attachments
        $successCount = 0;

        foreach ($imageIDs as $attachmentId) {
            \delete_post_meta($attachmentId, 'focalpoint');

            $result = \update_post_meta(
                $attachmentId,
                'focalpoint',
                [
                    'left' => $defaultPosition->left,
                    'top' => $defaultPosition->top,
                ]
            );

            if ($result !== false) {
                $successCount++;
            }
        }

        WP_CLI::success(
            \sprintf(
                'Applied the default focal point position (%.2f, %.2f) to %d image%s.',
                $defaultPosition->left,
                $defaultPosition->top,
                $successCount,
                $successCount === 1 ? '' : 's'
            )
        );
    }

    /**
     * Get all image attachment IDs from the database.
     *
     * @return array<int, int> Array of attachment IDs
     */
    private static function getAllImageIDs(): array
    {
        $query = new \WP_Query([
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
            'ignore_sticky_posts' => true
        ]);

        return $query->posts;
    }

    /**
     * Validate that the provided IDs are valid image attachments.
     *
     * @param array<int, int> $imageIDs
     * @return array<int, int> Array of invalid IDs
     */
    private static function getInvalidImageIDs(array $imageIDs): array
    {
        $invalidIDs = [];

        foreach ($imageIDs as $id) {
            if (!\wp_attachment_is_image($id)) {
                $invalidIDs[] = $id;
            }
        }

        return $invalidIDs;
    }
}
