<?php

namespace App\Livewire;

use App\Actions\SubmitInquiry;
use App\Support\Locales;
use App\Support\Profile;
use App\Support\Spam\Turnstile;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $company = '';

    public string $project_type = '';

    public string $budget_range = '';

    public string $message = '';

    /** Honeypot: humans never see or fill it. */
    public string $website = '';

    public string $turnstileToken = '';

    /** Encrypted render time for the time-trap; locked so the client can't rewrite it. */
    #[Locked]
    public string $issuedAt = '';

    /** Livewire update requests bypass the locale prefix, so the page locale travels with the component. */
    #[Locked]
    public string $locale = 'en';

    public bool $sent = false;

    public ?string $failure = null;

    public function mount(): void
    {
        $this->locale = Locales::isSupported(app()->getLocale()) ? app()->getLocale() : Locales::DEFAULT;
        $this->issuedAt = Crypt::encryptString((string) now()->getTimestamp());
        $this->applyLocale();
    }

    public function hydrate(): void
    {
        $this->applyLocale();
    }

    /** Re-applies the page locale on Livewire update requests (they don't pass through SetLocale). */
    private function applyLocale(): void
    {
        $locale = Locales::isSupported($this->locale) ? $this->locale : Locales::DEFAULT;
        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
    }

    /** @return array<string, mixed> */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'max:255', app()->environment('production') ? 'email:rfc,dns' : 'email:rfc'],
            'company' => ['nullable', 'string', 'max:120'],
            'project_type' => ['required', Rule::in(config('portfolio.contact.project_types'))],
            'budget_range' => ['required', Rule::in(config('portfolio.contact.budget_ranges'))],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return __('contact.fields');
    }

    public function submit(SubmitInquiry $submitInquiry, Turnstile $turnstile): void
    {
        $this->failure = null;
        $ip = request()->ip();
        $limits = config('portfolio.contact.rate_limits');
        $ipKey = 'contact:ip:'.sha1((string) $ip);

        // Rate limits are checked before validation so bots can't probe the form for free.
        foreach ([[$ipKey.':10m', $limits['ip_per_10_minutes']], [$ipKey.':day', $limits['ip_per_day']]] as [$key, $max]) {
            if (RateLimiter::tooManyAttempts($key, $max)) {
                $this->failure = __('contact.errors.throttled', ['minutes' => (int) ceil(RateLimiter::availableIn($key) / 60)]);

                return;
            }
        }

        $data = $this->validate();

        $emailKey = 'contact:email:'.sha1(mb_strtolower($data['email']));
        if (RateLimiter::tooManyAttempts($emailKey, $limits['email_per_day'])) {
            $this->failure = __('contact.errors.throttled', ['minutes' => (int) ceil(RateLimiter::availableIn($emailKey) / 60)]);

            return;
        }

        if (! $turnstile->verify($this->turnstileToken, $ip)) {
            $this->failure = __('contact.errors.turnstile');
            $this->dispatch('turnstile-reset');

            return;
        }

        RateLimiter::hit($ipKey.':10m', 600);
        RateLimiter::hit($ipKey.':day', 86400);
        RateLimiter::hit($emailKey, 86400);

        try {
            $submitInquiry->handle($data, $this->locale, $ip, request()->userAgent(), $this->spamReason());
        } catch (Throwable $e) {
            Log::error('Contact form submission failed', ['exception' => $e]);
            $this->failure = __('contact.errors.generic');

            return;
        }

        // Bots caught by the honeypot or time-trap see the same success screen, so they learn nothing.
        $this->sent = true;
        $this->reset(['name', 'email', 'company', 'project_type', 'budget_range', 'message', 'website', 'turnstileToken']);
    }

    public function again(): void
    {
        $this->sent = false;
        $this->issuedAt = Crypt::encryptString((string) now()->getTimestamp());
    }

    private function spamReason(): ?string
    {
        if ($this->website !== '') {
            return 'honeypot';
        }

        try {
            $elapsed = now()->getTimestamp() - (int) Crypt::decryptString($this->issuedAt);
        } catch (Throwable) {
            return 'time-trap';
        }

        $min = (int) config('portfolio.contact.min_seconds');
        $max = (int) config('portfolio.contact.max_minutes') * 60;

        return $elapsed < $min || $elapsed > $max ? 'time-trap' : null;
    }

    public function render(Profile $profile, Turnstile $turnstile): View
    {
        return view('livewire.contact-form', [
            'hours' => $profile->responseTimeHours(),
            'turnstileSiteKey' => $turnstile->enabled() ? $turnstile->siteKey() : null,
            'projectTypes' => collect(config()->array('portfolio.contact.project_types'))->mapWithKeys(fn (string $v) => [$v => __("contact.project_types.{$v}")]),
            'budgets' => collect(config()->array('portfolio.contact.budget_ranges'))->mapWithKeys(fn (string $v) => [$v => __("contact.budgets.{$v}")]),
        ]);
    }
}
