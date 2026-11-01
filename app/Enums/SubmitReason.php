<?php

namespace App\Enums;

/**
 * Why an exam attempt was submitted.
 */
enum SubmitReason: string {
    case Manual         = 'manual';
    case TimeUp         = 'time_up';
    case TabHidden      = 'tab_hidden';
    case WindowBlur     = 'window_blur';
    case PageClose      = 'page_close';
    case FullscreenExit = 'fullscreen_exit';
    case Expired        = 'expired';
    case Late           = 'late';

    /** Reasons the browser may send with a submission. */
    public static function clientValues(): array {
        return array_map( fn( self $reason ) => $reason->value, [
            self::Manual, self::TimeUp, self::TabHidden, self::WindowBlur, self::PageClose, self::FullscreenExit,
        ] );
    }

    /** True when the exam was ended because the student left it. */
    public function isViolation(): bool {
        return in_array( $this, [self::TabHidden, self::WindowBlur, self::PageClose, self::FullscreenExit, self::Expired], true );
    }

    public function label(): string {
        return match ( $this ) {
            self::Manual         => 'Submitted',
            self::TimeUp         => 'Time up',
            self::TabHidden      => 'Switched tab',
            self::WindowBlur     => 'Left the window',
            self::PageClose      => 'Closed or reloaded the page',
            self::FullscreenExit => 'Exited fullscreen',
            self::Expired        => 'Abandoned',
            self::Late           => 'Submitted late',
        };
    }

    public function banglaLabel(): string {
        return match ( $this ) {
            self::Manual         => 'নিজে জমা দিয়েছে',
            self::TimeUp         => 'সময় শেষ',
            self::TabHidden      => 'অন্য ট্যাবে গিয়েছে',
            self::WindowBlur     => 'অন্য উইন্ডো/অ্যাপে গিয়েছে',
            self::PageClose      => 'পেজ বন্ধ বা রিলোড করেছে',
            self::FullscreenExit => 'ফুলস্ক্রিন থেকে বের হয়েছে',
            self::Expired        => 'পরীক্ষা অসম্পূর্ণ রেখে চলে গেছে',
            self::Late           => 'সময়ের পরে জমা হয়েছে',
        };
    }

    public function badgeClass(): string {
        return match ( true ) {
            $this === self::Manual => 'bg-emerald-500/10 text-emerald-300 ring-1 ring-emerald-500/25',
            $this === self::TimeUp => 'bg-sky-500/10 text-sky-300 ring-1 ring-sky-500/25',
            default                => 'bg-rose-500/10 text-rose-300 ring-1 ring-rose-500/25',
        };
    }
}
