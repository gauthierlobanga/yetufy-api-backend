@php
    $post = $getRecord();
    $media = $post?->getFirstMedia('featured');
@endphp

<div class="text-center p-2 border rounded-lg bg-gray-50 dark:bg-gray-800">
    @if($media && $media->hasGeneratedConversion($conversion))
        <img src="{{ $media->getUrl($conversion) }}"
             alt="{{ $label }}"
             class="w-full h-auto rounded shadow-sm">
        <div class="mt-2 text-xs">
            <span class="font-medium">{{ $label }}</span>
            <span class="text-gray-500">({{ $size }})</span>
        </div>
    @else
        <div class="flex items-center justify-center h-32 bg-gray-100 dark:bg-gray-700 rounded">
            <span class="text-gray-400 text-sm">En attente de génération</span>
        </div>
        <div class="mt-2 text-xs text-gray-500">{{ $label }} ({{ $size }})</div>
    @endif
</div>