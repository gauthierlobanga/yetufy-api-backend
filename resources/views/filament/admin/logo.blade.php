@php
    $appName = config('app.name');
    $logoPath = public_path('storage/images/favicon.png');
    $hasLogo = file_exists($logoPath);
@endphp

<a href="{{ filament()->getUrl() }}" class="flex items-center gap-3 outline-none">

    @if ($hasLogo)
        <img src="{{ Storage::url('images/favicon.png') }}" alt="{{ $appName }}" class="h-8 w-auto"
            @if (filament()->hasDarkMode()) x-data="{ dark: document.documentElement.classList.contains('dark') }"
             x-init="$watch('dark', value => $el.classList.toggle('dark-mode', value))" @endif
            loading="eager">
    @endif

    <span class="text-lg font-bold text-gray-900 dark:text-white">
        {{ $appName }}
    </span>
</a>
