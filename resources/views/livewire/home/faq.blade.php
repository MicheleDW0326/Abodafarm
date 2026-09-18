<section id="faq" class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8" x-data="{ openIndex: null }">
    <div class="mb-8 text-center">
        <h2 class="text-3xl font-semibold">Pertanyaan Umum</h2>
        <p class="mt-2 text-slate-600">Informasi yang sering ditanyakan sebelum checkout.</p>
    </div>

    <div class="space-y-3">
        @foreach ($faqs as $index => $faq)
            <article class="rounded-xl border border-slate-200 bg-white">
                <button @click="openIndex = openIndex === {{ $index }} ? null : {{ $index }}"
                    class="cursor-pointer flex w-full items-center justify-between px-5 py-4 text-left">
                    <span class="font-semibold">{{ $faq['question'] }}</span>
                    <span class="text-sky-600" x-text="openIndex === {{ $index }} ? '-' : '+'"></span>
                </button>
                <div x-show="openIndex === {{ $index }}" x-collapse class="px-5 pb-4 text-sm text-slate-600">
                    {{ $faq['answer'] }}
                </div>
            </article>
        @endforeach
    </div>
</section>
