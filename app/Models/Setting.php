<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Simple key/value app settings, cached until changed.
 */
class Setting extends Model {
    protected $fillable = ['key', 'value'];

    /** Defaults used when a setting has never been saved. */
    public const DEFAULTS = [
        'seconds_per_question' => 45,
        'max_attempts'         => 1,
        'show_answer_review'   => 1,
    ];

    private const CACHE_KEY = 'app_settings';

    /** Settings already read during this request, so the cache is hit once per request. */
    private static ?array $loaded = null;

    public static function get( string $key, mixed $default = null ): mixed {
        self::$loaded ??= Cache::rememberForever( self::CACHE_KEY, fn() => self::pluck( 'value', 'key' )->all() );

        return self::$loaded[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    public static function set( string $key, mixed $value ): void {
        self::updateOrCreate( ['key' => $key], ['value' => $value] );

        Cache::forget( self::CACHE_KEY );
        self::$loaded = null;
    }

    public static function secondsPerQuestion(): int {
        return max( 5, (int) self::get( 'seconds_per_question' ) );
    }

    public static function maxAttempts(): int {
        return max( 1, (int) self::get( 'max_attempts' ) );
    }

    /** Whether students may see the correct answers after submitting. */
    public static function showAnswerReview(): bool {
        return (bool) self::get( 'show_answer_review' );
    }

    /** Public URL of the uploaded logo, or null to use the default mark. */
    public static function logoUrl(): ?string {
        return self::publicFileUrl( 'logo_path' );
    }

    /** Public URL of the uploaded favicon, or null to use public/favicon.ico. */
    public static function faviconUrl(): ?string {
        return self::publicFileUrl( 'favicon_path' );
    }

    private static function publicFileUrl( string $key ): ?string {
        $path = self::get( $key );

        // asset() follows the current host, so links work whatever APP_URL says
        return $path ? asset( 'storage/' . $path ) : null;
    }
}
