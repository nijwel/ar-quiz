<?php

namespace App\Enums;

/**
 * Difficulty level of a whole exam (quiz).
 */
enum Difficulty: string {
    case Easy     = 'easy';
    case Standard = 'standard';
    case Hard     = 'hard';
    case Expert   = 'expert';

    public static function values(): array {
        return array_column( self::cases(), 'value' );
    }

    public function label(): string {
        return match ( $this ) {
            self::Easy     => 'Easy',
            self::Standard => 'Standard',
            self::Hard     => 'Hard',
            self::Expert   => 'Expert',
        };
    }

    public function banglaLabel(): string {
        return match ( $this ) {
            self::Easy     => 'সহজ',
            self::Standard => 'মাঝারি',
            self::Hard     => 'কঠিন',
            self::Expert   => 'এক্সপার্ট',
        };
    }

    /** Badge / chip classes (work in both light and dark themes). */
    public function badgeClass(): string {
        return match ( $this ) {
            self::Easy     => 'bg-emerald-500/10 text-emerald-300 ring-1 ring-emerald-500/25',
            self::Standard => 'bg-sky-500/10 text-sky-300 ring-1 ring-sky-500/25',
            self::Hard     => 'bg-amber-500/10 text-amber-300 ring-1 ring-amber-500/25',
            self::Expert   => 'bg-rose-500/10 text-rose-300 ring-1 ring-rose-500/25',
        };
    }

    /** Classes for a selected / active state. */
    public function solidClass(): string {
        return match ( $this ) {
            self::Easy     => 'bg-emerald-500 text-snow ring-1 ring-emerald-500 shadow-lg shadow-emerald-500/30',
            self::Standard => 'bg-sky-500 text-snow ring-1 ring-sky-500 shadow-lg shadow-sky-500/30',
            self::Hard     => 'bg-amber-500 text-snow ring-1 ring-amber-500 shadow-lg shadow-amber-500/30',
            self::Expert   => 'bg-rose-500 text-snow ring-1 ring-rose-500 shadow-lg shadow-rose-500/30',
        };
    }

    public function icon(): string {
        return match ( $this ) {
            self::Easy     => 'icon-[tabler--plant-2]',
            self::Standard => 'icon-[tabler--gauge]',
            self::Hard     => 'icon-[tabler--flame]',
            self::Expert   => 'icon-[tabler--skull]',
        };
    }
}
