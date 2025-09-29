<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            {{ isset($user) ? ($user->name.' の公開記事一覧') : '公開記事一覧' }}
        </h2>
    </x-slot>

    {{-- ユーザー専用ページのときだけヘッダーを表示 --}}
    @isset($user)
        @if($user->header_image_url)
            <div class="mb-6">
                <img src="{{ $user->header_image_url }}" alt="" class="w-full max-h-64 object-cover rounded">
            </div>
        @endif
    @endisset

    <div class="p-6">
        @if ($posts->count() === 0)
            <p class="text-gray-600">まだ公開記事はありません。</p>
        @else
            <!-- カードグリッド -->
            <div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($posts as $post)
                    <article class="bg-white rounded-xl shadow-sm border border-gray-100 hover:scale-105 transition">
                        <a href="{{ route('user.posts.show', [$post->user->username, $post->slug]) }}" class="block p-5">

                             @if($post->thumbnail_path)
            <img src="{{ $post->thumbnail_url }}" alt=""
                 class="w-full h-40 object-cover rounded-t-xl">
        @endif


                            <!-- タイトル -->
                            <h3 class="text-lg font-semibold line-clamp-2 break-words">
                                {{ $post->title }}
                            </h3>

                            <!-- メタ情報 -->
                            <div class="mt-2 flex items-center gap-2 text-sm text-gray-500">
                                <span>by {{ optional($post->user)->name ?? 'Unknown' }}</span>
                                <span aria-hidden="true">·</span>
                                <time datetime="{{ optional($post->published_at)?->toIso8601String() }}">
                                    {{ optional($post->published_at)?->format('Y-m-d H:i') }}
                                </time>
                            </div>

                            <!-- 抜粋（本文の先頭を少しだけ） -->
                            @php
                                $excerpt = \Illuminate\Support\Str::limit(strip_tags($post->body), 90);
                            @endphp
                            @if($excerpt)
                                <p class="mt-3 text-sm text-gray-700 line-clamp-3">{{ $excerpt }}</p>
                            @endif

                            <!-- フッター：続きを読む -->
                            <div class="mt-4 text-blue-600 text-sm font-medium underline underline-offset-4">
                                続きを読む
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <!-- ページネーション -->
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
