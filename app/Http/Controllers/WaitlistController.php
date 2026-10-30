<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\WaitlistEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Own waiting list (no third-party service). Spam protection: a honeypot
 * field and rate limiting on the route. Visitors can remove their entry
 * with the personal link shown after signing up.
 */
class WaitlistController extends Controller
{
    public function create(Request $request, string $locale): View
    {
        return view('pages.waitlist', [
            'locale' => $locale,
            'plans' => $this->plans($locale),
            'selected' => (string) $request->query('plan', ''),
        ]);
    }

    public function store(Request $request, string $locale): RedirectResponse
    {
        // Honeypot: people never see this field; bots fill it in. Pretend it worked.
        if (filled($request->input('website'))) {
            return redirect()->to(url("{$locale}/waitlist"))->with('waitlist-done', true);
        }

        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:254'],
            'plan' => ['nullable', 'string', Rule::in(array_keys($this->plans($locale)))],
            'consent' => ['accepted'],
        ], [], ['email' => trans('site.waitlist.email', [], $locale), 'consent' => trans('site.waitlist.consent_label', [], $locale)]);

        $entry = WaitlistEntry::query()->firstOrNew(['email' => mb_strtolower(trim($data['email']))]);
        $entry->fill([
            'locale' => $locale,
            'plan' => $data['plan'] ?? null,
            'consent_text' => trans('site.waitlist.consent', [], $locale),
            'consented_at' => now(),
        ]);
        $token = $entry->issueRemovalToken();
        $entry->save();

        return redirect()->to(url("{$locale}/waitlist"))
            ->with('waitlist-done', true)
            ->with('waitlist-remove-url', url("{$locale}/waitlist/remove/{$entry->id}/{$token}"));
    }

    public function confirmRemoval(string $locale, int $entry, string $token): View
    {
        return view('pages.waitlist-remove', ['locale' => $locale, 'valid' => $this->find($entry, $token) !== null, 'entry' => $entry, 'token' => $token]);
    }

    public function destroy(string $locale, int $entry, string $token): RedirectResponse
    {
        $this->find($entry, $token)?->delete();

        // Same answer whether or not the entry still existed.
        return redirect()->to(url("{$locale}/waitlist"))->with('waitlist-removed', true);
    }

    private function find(int $id, string $token): ?WaitlistEntry
    {
        $entry = WaitlistEntry::query()->find($id);

        return $entry?->removalTokenMatches($token) ? $entry : null;
    }

    /**
     * @return array<string, string> plan key => name
     */
    private function plans(string $locale): array
    {
        return rescue(fn () => Plan::query()->where('is_visible', true)->orderBy('sort')->get()
            ->mapWithKeys(fn (Plan $plan) => [$plan->key => $plan->translate('name', $locale)])->all(), [], report: false);
    }
}
