@props(['items' => []])

@if (count($items))
    <nav aria-label="مسار التنقل" class="mb-6 text-sm text-slate-500">
        <ol class="flex flex-wrap items-center gap-2">
            @foreach ($items as $index => $item)
                <li class="inline-flex items-center gap-2">
                    @if (! $loop->last)
                        <a href="{{ $item['url'] }}" class="transition-colors hover:text-brand-700" wire:navigate>{{ $item['name'] }}</a>
                        <span aria-hidden="true" class="text-slate-300">/</span>
                    @else
                        <span class="font-medium text-slate-700">{{ $item['name'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
