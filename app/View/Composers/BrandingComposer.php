<?php

namespace App\View\Composers;

use App\Models\Setting;
use Illuminate\View\View;

/**
 * Gives the layout and the brand mark the uploaded logo / favicon.
 */
class BrandingComposer {
    public function compose( View $view ): void {
        $view->with( [
            'brandLogoUrl'    => Setting::logoUrl(),
            'brandFaviconUrl' => Setting::faviconUrl(),
        ] );
    }
}
