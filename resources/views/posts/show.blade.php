<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">{{ $post->title }}</h2>
    </x-slot>

    <div class="p-6 max-w-5xl mx-auto">
        <div class="text-sm text-gray-500 mb-4">
            by {{ optional($post->user)->name ?? 'Unknown' }}
    @if($post->published_at)
      / 公開: {{ optional($post->published_at)->format('Y-m-d H:i') }}
    @endif
        </div>

        @if($post->thumbnail_path)
    <img src="{{ $post->thumbnail_url }}" alt="" class="w-full max-w-3xl h-auto rounded mb-4">
@endif


        <div class="prose max-w-none whitespace-pre-wrap">{!! $post->body !!}</div>
    </div>
</x-app-layout>
