<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;

class SettingController extends Controller {
    public function index() {
        return view( 'admin.settings', [
            'secondsPerQuestion' => Setting::secondsPerQuestion(),
            'maxAttempts'        => Setting::maxAttempts(),
        ] );
    }

    public function update( UpdateSettingsRequest $request ) {
        foreach ( $request->validated() as $key => $value ) {
            Setting::set( $key, $value );
        }

        return back()->with( 'success', 'Settings saved successfully!' );
    }
}
