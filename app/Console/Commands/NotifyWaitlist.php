<?php

namespace App\Console\Commands;

use App\Mail\WaitlistOpened;
use App\Models\Setting;
use App\Models\WaitlistEntry;
use App\Services\Site\SiteData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the "registration is open" mail to the waiting list, once per
 * person (within what they consented to). Dry run unless --send.
 */
class NotifyWaitlist extends Command
{
    protected $signature = 'waitlist:notify {--send : Really send; without it only count}';

    protected $description = 'Tell the waiting list that registration is open (once per entry)';

    public function handle(): int
    {
        if (! Setting::get('registration_open')) {
            $this->error('Registration is not open (site setting registration_open). Nothing sent.');

            return self::FAILURE;
        }

        $entries = WaitlistEntry::query()->whereNull('notified_at')->orderBy('id')->get();
        $this->info($entries->count().' aanmeldingen nog niet geïnformeerd.');

        if (! $this->option('send')) {
            $this->line('Dry run: gebruik --send om te versturen.');

            return self::SUCCESS;
        }

        $site = app(SiteData::class);
        $sent = 0;

        foreach ($entries as $entry) {
            $plan = $entry->plan ? \App\Models\Plan::query()->where('key', $entry->plan)->first() : null;
            $register = $plan ? $site->planUrl($plan, $entry->locale) : (string) (Setting::get('signup_url') ?: rtrim((string) config('serverscope.app_url'), '/').'/register');
            // A fresh removal link goes into the mail (the earlier one stops working).
            $token = $entry->issueRemovalToken();
            $entry->save();

            try {
                Mail::to($entry->email)->send(new WaitlistOpened($entry->locale, $register, url("{$entry->locale}/waitlist/remove/{$entry->id}/{$token}")));
            } catch (\Throwable $e) {
                report($e);
                $this->warn("Mislukt voor aanmelding {$entry->id}.");

                continue;
            }

            $entry->forceFill(['notified_at' => now()])->save();
            $sent++;
        }

        $this->info("Verstuurd: {$sent}");

        return self::SUCCESS;
    }
}
