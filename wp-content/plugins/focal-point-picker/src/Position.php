<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\FocalPointPicker;

/**
 * Represents the default value for a focal point
 */
final readonly class Position
{
    public float $left;
    public float $top;

    public function __construct(
        mixed $left = null,
        mixed $top = null
    ) {
        $this->left = self::sanitize($left);
        $this->top = self::sanitize($top);
    }

    /**
     * Sanitize a value between zero to one
     */
    private static function sanitize(mixed $value): float
    {
        if (!\is_numeric($value) && empty($value)) {
            return 0.5;
        }

        $value = \floatval($value);

        if ($value > 1) {
            $value /= 100;
        }

        return \round($value, 2);
    }
}
