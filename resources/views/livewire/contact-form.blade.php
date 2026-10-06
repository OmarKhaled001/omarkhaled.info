@php
    $field = 'block w-full rounded-md border border-border-strong bg-surface px-4 py-3 text-[1rem] text-ink placeholder:text-muted/70 transition-colors focus:border-ink focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus aria-[invalid=true]:border-accent-text';
    $label = 'mb-2 block text-sm font-medium';
    $error = 'mt-2 text-sm text-accent-text';
@endphp
<div>
    <div role="status" aria-live="polite" class="sr-only">@if ($sent){{ __('contact.success.title') }}@endif</div>

    @if ($sent)
        <div class="card p-8 sm:p-10" wire:key="contact-success">
            <span class="inline-flex size-12 items-center justify-center rounded-full bg-accent text-on-accent" aria-hidden="true"><x-lucide-check class="size-6" /></span>
            <h2 class="mt-6 text-h3 font-semibold">{{ __('contact.success.title') }}</h2>
            <p class="mt-3 text-muted">{{ __('contact.success.body', ['hours' => $hours]) }}</p>
            <button type="button" wire:click="again" class="btn-secondary mt-8">{{ __('contact.success.again') }}</button>
        </div>
    @else
        <form wire:submit="submit" class="card space-y-6 p-6 sm:p-8" aria-label="{{ __('contact.form.label') }}" novalidate wire:key="contact-form">
            @if ($failure)
                <div class="rounded-md border border-accent-text/40 bg-accent/10 px-4 py-3 text-sm" role="alert">{{ $failure }}</div>
            @endif

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="cf-name" class="{{ $label }}">{{ __('contact.form.name') }}</label>
                    <input id="cf-name" type="text" wire:model="name" autocomplete="name" required maxlength="100" class="{{ $field }}"
                        @error('name') aria-invalid="true" aria-describedby="cf-name-error" @enderror>
                    @error('name')<p id="cf-name-error" class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cf-email" class="{{ $label }}">{{ __('contact.form.email') }}</label>
                    <input id="cf-email" type="email" wire:model="email" autocomplete="email" inputmode="email" dir="ltr" required maxlength="255" class="{{ $field }}"
                        @error('email') aria-invalid="true" aria-describedby="cf-email-error" @enderror>
                    @error('email')<p id="cf-email-error" class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="cf-company" class="{{ $label }}">{{ __('contact.form.company') }} <span class="font-normal text-muted">({{ __('contact.form.optional') }})</span></label>
                <input id="cf-company" type="text" wire:model="company" autocomplete="organization" maxlength="120" class="{{ $field }}"
                    @error('company') aria-invalid="true" aria-describedby="cf-company-error" @enderror>
                @error('company')<p id="cf-company-error" class="{{ $error }}">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="cf-type" class="{{ $label }}">{{ __('contact.form.project_type') }}</label>
                    <select id="cf-type" wire:model="project_type" required class="{{ $field }}"
                        @error('project_type') aria-invalid="true" aria-describedby="cf-type-error" @enderror>
                        <option value="">{{ __('contact.form.select') }}</option>
                        @foreach ($projectTypes as $value => $text)<option value="{{ $value }}">{{ $text }}</option>@endforeach
                    </select>
                    @error('project_type')<p id="cf-type-error" class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="cf-budget" class="{{ $label }}">{{ __('contact.form.budget') }}</label>
                    <select id="cf-budget" wire:model="budget_range" required class="{{ $field }}"
                        @error('budget_range') aria-invalid="true" aria-describedby="cf-budget-error" @enderror>
                        <option value="">{{ __('contact.form.select') }}</option>
                        @foreach ($budgets as $value => $text)<option value="{{ $value }}">{{ $text }}</option>@endforeach
                    </select>
                    @error('budget_range')<p id="cf-budget-error" class="{{ $error }}">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="cf-message" class="{{ $label }}">{{ __('contact.form.message') }}</label>
                <textarea id="cf-message" wire:model="message" rows="6" required minlength="20" maxlength="5000" class="{{ $field }}"
                    aria-describedby="cf-message-hint @error('message') cf-message-error @enderror" @error('message') aria-invalid="true" @enderror></textarea>
                <p id="cf-message-hint" class="mt-2 text-sm text-muted">{{ __('contact.form.message_hint') }}</p>
                @error('message')<p id="cf-message-error" class="{{ $error }}">{{ $message }}</p>@enderror
            </div>

            {{-- Honeypot: off-screen (not display:none, which bots skip), unreachable by keyboard. --}}
            <div class="hp" aria-hidden="true">
                <label for="cf-website">{{ __('contact.form.honeypot') }}</label>
                <input id="cf-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($turnstileSiteKey)
                <div wire:ignore>
                    <div class="cf-turnstile" data-sitekey="{{ $turnstileSiteKey }}" data-callback="onTurnstile" data-language="{{ app()->getLocale() }}"></div>
                </div>
            @endif

            <div class="flex flex-col gap-4 border-t border-border pt-6 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted">{!! __('contact.form.privacy', ['link' => '<a href="'.e(route('privacy')).'" class="underline underline-offset-4 hover:text-ink">'.e(__('contact.form.privacy_link')).'</a>']) !!}</p>
                <button type="submit" class="btn-primary shrink-0" wire:loading.attr="disabled" wire:target="submit">
                    <span wire:loading.remove wire:target="submit">{{ __('contact.form.submit') }}</span>
                    <span wire:loading wire:target="submit">{{ __('contact.form.sending') }}</span>
                    <x-lucide-arrow-right class="icon-dir size-4" aria-hidden="true" wire:loading.remove wire:target="submit" />
                </button>
            </div>
        </form>
    @endif
</div>
