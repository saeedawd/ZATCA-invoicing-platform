@props(['items' => [], 'title' => 'أسئلة شائعة'])

@if (count($items))
    <section class="border-t border-slate-200/80 bg-white py-14 sm:py-16">
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <h2 class="text-2xl font-bold text-slate-900">{{ $title }}</h2>
            <dl class="mt-8 space-y-8">
                @foreach ($items as $item)
                    <div class="border-t border-slate-100 pt-6">
                        <dt class="text-lg font-semibold text-slate-900">{{ $item['question'] }}</dt>
                        <dd class="mt-2 leading-7 text-slate-600">{{ $item['answer'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>
@endif
