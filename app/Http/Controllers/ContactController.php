<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Contact form, delivered by e-mail to CONTACT_MAIL_TO. Spam protection:
 * a honeypot field and rate limiting on the route. Messages are not
 * stored on this site. Without CONTACT_MAIL_TO the page says so instead
 * of silently dropping messages.
 */
class ContactController extends Controller
{
    public function create(string $locale): View
    {
        return view('pages.contact', ['locale' => $locale, 'available' => filled(config('serverscope.contact_mail_to'))]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        // Honeypot: people never see this field; bots fill it in. Pretend it worked.
        if (filled($request->input('website'))) {
            return back()->with('success', trans('site.contact.success', [], $locale));
        }

        $to = config('serverscope.contact_mail_to');

        if (blank($to)) {
            return back()->withInput()->with('error', trans('site.contact.unavailable', [], $locale));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        try {
            Mail::to($to)->send(new ContactMessage($validated, $locale));
        } catch (\Throwable $e) {
            // No personal data in the log.
            Log::error('Contact form could not be delivered', ['locale' => $locale, 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', trans('site.contact.send_failed', [], $locale));
        }

        return back()->with('success', trans('site.contact.success', [], $locale));
    }
}
