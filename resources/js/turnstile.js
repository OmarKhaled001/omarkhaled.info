// Bridges the Cloudflare Turnstile widget to the Livewire contact form (loaded only when enabled).
window.onTurnstile = (token) => {
    const root = document.querySelector('.cf-turnstile')?.closest('[wire\\:id]');
    if (root && window.Livewire) {
        window.Livewire.find(root.getAttribute('wire:id')).set('turnstileToken', token, false);
    }
};

document.addEventListener('livewire:init', () => {
    window.Livewire.on('turnstile-reset', () => window.turnstile?.reset());
});
