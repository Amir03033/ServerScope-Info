<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function create(string $locale): View
    {
        return view('pages.contact', compact('locale'));
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Temporary: store no personal data until a delivery/storage
        // mechanism has been configured.
        Log::info('Website contact submission received', [
            'locale' => $locale,
            'subject' => $validated['subject'],
        ]);

        return back()->with(
            'success',
            $locale === 'nl'
                ? 'Bedankt voor je bericht.'
                : 'Thank you for your message.'
        );
    }
}
