<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateBrandingRequest;
use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller {
    public function index() {
        return view( 'admin.settings', [
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
            'maxAttempts'        => Setting::maxAttempts(),
            'showAnswerReview'   => Setting::showAnswerReview(),
            'logoUrl'            => Setting::logoUrl(),
            'faviconUrl'         => Setting::faviconUrl(),
        ] );
    }

    public function update( UpdateSettingsRequest $request ) {
        foreach ( $request->validated() as $key => $value ) {
            Setting::set( $key, $value );
        }

        return back()->with( 'success', 'Settings saved successfully!' );
    }

    public function updateBranding( UpdateBrandingRequest $request ) {
        $this->syncFile( 'logo_path', $request->file( 'logo' ), $request->boolean( 'remove_logo' ) );
        $this->syncFile( 'favicon_path', $request->file( 'favicon' ), $request->boolean( 'remove_favicon' ) );

        return back()->with( 'success', 'Branding updated successfully!' );
    }

    /**
     * Replace or remove an uploaded branding file, deleting the old one from disk.
     */
    private function syncFile( string $settingKey, ?UploadedFile $upload, bool $remove ): void {
        if ( !$upload && !$remove ) {
            return;
        }

        $oldPath = Setting::get( $settingKey );
        $newPath = $upload?->store( 'branding', 'public' );

        Setting::set( $settingKey, $newPath );

        if ( $oldPath ) {
            Storage::disk( 'public' )->delete( $oldPath );
        }
    }
}
