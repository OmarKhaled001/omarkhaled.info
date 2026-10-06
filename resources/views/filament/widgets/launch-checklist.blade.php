<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-rocket-launch"
        :icon-color="count($placeholders) ? 'warning' : 'success'"
        heading="Launch checklist"
        :description="count($placeholders) ? count($placeholders).' setting(s) still hold placeholders — they are hidden from the public site until you replace them.' : 'No placeholders left. Nice.'">
        @if (count($placeholders))
            <ul style="display:grid;gap:.5rem;margin-bottom:1.25rem">
                @foreach ($placeholders as $item)
                    <li style="display:flex;justify-content:space-between;gap:1rem;align-items:center">
                        <span>
                            <x-filament::badge color="warning" size="sm">placeholder</x-filament::badge>
                            <strong style="margin-inline-start:.5rem">{{ $item['label'] }}</strong>
                            <code style="opacity:.6;margin-inline-start:.5rem;font-size:.8rem">{{ \Illuminate\Support\Str::limit($item['value'], 60) }}</code>
                        </span>
                        <x-filament::link :href="$item['url']" size="sm">Edit</x-filament::link>
                    </li>
                @endforeach
            </ul>
        @endif

        <ul style="display:grid;gap:.5rem">
            @foreach ($advisories as $item)
                <li style="display:flex;gap:.6rem;align-items:flex-start">
                    @if ($item['ok'])
                        <x-filament::icon icon="heroicon-m-check-circle" style="width:1.1rem;color:rgb(22 163 74)" />
                    @else
                        <x-filament::icon icon="heroicon-m-exclamation-circle" style="width:1.1rem;color:rgb(217 119 6)" />
                    @endif
                    <span><strong>{{ $item['label'] }}</strong><br><span style="opacity:.7;font-size:.85rem">{{ $item['hint'] }}</span></span>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
