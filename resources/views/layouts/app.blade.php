<x-layouts::app.sidebar :title="$title ?? null">
    <main class="min-h-screen">
        {{ $slot }}
    </main>
</x-layouts::app.sidebar>
