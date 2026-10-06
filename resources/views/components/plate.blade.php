<svg viewBox="0 0 640 400" direction="ltr" role="img" aria-label="{{ $project->title() }}"
    {{ $attributes->merge(['class' => 'block h-full w-full']) }} preserveAspectRatio="xMidYMid slice">
    <rect width="640" height="400" fill="var(--surface-2)"/>
    <g stroke="var(--grid)" stroke-width="1">
        @for ($x = 40; $x < 640; $x += 40)<line x1="{{ $x }}" y1="0" x2="{{ $x }}" y2="400"/>@endfor
        @for ($y = 40; $y < 400; $y += 40)<line x1="0" y1="{{ $y }}" x2="600" y2="{{ $y }}"/>@endfor
    </g>
    <g fill="none" stroke="var(--border-strong)" stroke-width="1.5">
        <path d="M28 16v24M16 28h24M612 16v24M624 28h-24M28 384v-24M16 372h24M612 384v-24M624 372h-24"/>
    </g>
    <text x="40" y="190" font-family="var(--font-sans)" font-weight="650" font-size="140" letter-spacing="-6" fill="var(--text)" opacity=".92">{{ $number }}</text>
    <text x="44" y="232" font-family="var(--font-mono)" font-size="15" letter-spacing="2" fill="var(--muted)">{{ strtoupper($category) }}</text>
    <g stroke="var(--accent)" stroke-width="1.5" opacity=".7">
        @foreach ($edges as [$a, $b])
            <line x1="{{ $nodes[$a]['x'] }}" y1="{{ $nodes[$a]['y'] }}" x2="{{ $nodes[$b]['x'] }}" y2="{{ $nodes[$b]['y'] }}"/>
        @endforeach
    </g>
    @foreach ($nodes as $node)
        <g>
            <circle cx="{{ $node['x'] }}" cy="{{ $node['y'] }}" r="{{ $node['accent'] ? 9 : 6 }}" fill="{{ $node['accent'] ? 'var(--accent)' : 'var(--surface)' }}" stroke="var(--accent)" stroke-width="1.5"/>
            <text x="{{ $node['x'] }}" y="{{ $node['y'] + $node['labelDy'] }}" text-anchor="middle" paint-order="stroke" stroke="var(--surface-2)" stroke-width="5" font-family="var(--font-mono)" font-size="13" fill="var(--muted)">{{ $node['label'] }}</text>
        </g>
    @endforeach
    <g fill="none" stroke="var(--accent)" stroke-width="1.5">
        <circle cx="588" cy="348" r="10"/><path d="M588 330v36M570 348h36"/>
    </g>
</svg>
