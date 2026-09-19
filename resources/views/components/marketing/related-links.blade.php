@props(['links' => []])

@if (count($links))
    <aside class="border-t border-slate-200/80 bg-surface py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <h2 class="text-lg font-bold text-slate-900">مواضيع ذات صلة</h2>
            <ul class="mt-4 flex flex-col gap-2 text-sm">
                @foreach ($links as $link)
                    <li>
                        <a href="{{ $link['url'] }}" class="font-medium text-brand-700 transition hover:text-brand-800" wire:navigate>{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </aside>
@endif
