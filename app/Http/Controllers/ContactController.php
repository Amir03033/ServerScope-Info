<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function create(Request $request, string $locale): View
    {
        return view('pages.contact', [
            'locale' => $locale,
            'available' => filled(config('serverscope.contact_recipient')),
            // Pre-filled from links such as the documentation feedback link.
            'subject' => mb_substr(trim((string) $request->query('subject', '')), 0, 150),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $recipient = config('serverscope.contact_recipient');

        // Honeypot: real visitors never see or fill this field. Pretend
        // success so bots learn nothing, but send nothing.
        if (filled($request->input('website'))) {
            return redirect()->to(url($locale.'/contact'))->with('success', trans('site.contact.success', [], $locale));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ], [], trans('site.contact.attributes', [], $locale));

        if (blank($recipient)) {
            return back()->withInput()->with('failure', trans('site.contact.unavailable', [], $locale));
        }

        try {
            Mail::to($recipient)->send(new ContactMessage($validated, $locale));
        } catch (Throwable $exception) {
            // No personal data in the log; the exception is reported as usual.
            report($exception);
            Log::warning('Website contact message could not be delivered', ['locale' => $locale]);

            return back()->withInput()->with('failure', trans('site.contact.failed', [], $locale));
        }

        return redirect()->to(url($locale.'/contact'))->with('success', trans('site.contact.success', [], $locale));
    }
}
