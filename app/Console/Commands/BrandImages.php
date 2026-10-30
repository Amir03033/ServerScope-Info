<?php

namespace App\Console\Commands;

use App\Support\OgImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Draws the fixed images in public/images (committed to git): the logo for
 * structured data and the share image used where GD is not available.
 */
class BrandImages extends Command
{
    protected $signature = 'site:brand-images';

    protected $description = 'Draw public/images/logo.png and og-default.png again (needs GD)';

    public function handle(): int
    {
        if (! OgImage::available()) {
            $this->error('GD met FreeType ontbreekt; de afbeeldingen in git blijven ongewijzigd.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists(public_path('images'));
        File::put(public_path('images/logo.png'), OgImage::logo());
        File::put(public_path('images/og-default.png'), OgImage::draw(trans('site.home.headline', [], 'nl'), null, 'info.serverscope.nl'));
        // Page images are drawn again on the next visit.
        File::deleteDirectory(OgImage::directory());

        $this->info('public/images/logo.png en public/images/og-default.png zijn bijgewerkt.');

        return self::SUCCESS;
    }
}
