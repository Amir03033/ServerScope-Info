<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use App\Models\Setting;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * /.well-known/security.txt (RFC 9116). Only served when a security
 * contact and a future expiry date are set under Bedrijfsgegevens.
 */
class SecurityTxtController extends Controller
{
    public function __invoke(): Response
    {
        $email = Setting::get('company.security_email');
        $expires = Setting::get('security_txt.expires');

        abort_if(blank($email) || blank($expires), 404);

        $expiresAt = Carbon::parse($expires, 'UTC')->setTime(23, 59, 59);
        abort_if($expiresAt->isPast(), 404);

        $lines = [
            'Contact: mailto:'.$email,
            'Expires: '.$expiresAt->format('Y-m-d\TH:i:s\Z'),
            'Preferred-Languages: nl, en',
            'Canonical: '.url('/.well-known/security.txt'),
        ];

        foreach (['nl', 'en'] as $locale) {
            $policy = rescue(fn () => LegalDocument::query()->where('key', 'disclosure')->where('locale', $locale)->first(), null, report: false);

            if ($policy?->currentVersion()) {
                $lines[] = 'Policy: '.url($policy->path());
            }
        }

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
