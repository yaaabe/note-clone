{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            ダッシュボード
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- フラッシュメッセージ --}}
            @if (session('status'))
                <div class="mb-4 p-3 rounded bg-green-50 text-green-700">
                    {{ session('status') }}
                </div>
            @endif


            {{-- ヘッダー画像アップロード --}}
<div class="mb-6 p-4 border rounded bg-white">
    <h3 class="font-semibold mb-3">ヘッダー画像</h3>

    @if(auth()->user()->header_image_url)
        <div class="mb-3">
            <img src="{{ auth()->user()->header_image_url }}" class="w-full max-h-56 object-cover rounded">
        </div>
    @endif

    <form method="POST" action="{{ route('user.header.update', auth()->user()->username) }}" enctype="multipart/form-data" class="flex items-center gap-3">
        @csrf
        <input type="file" name="header_image" accept="image/*" class="block">
        <button class="bg-gray-800 text-white px-4 py-2 rounded">更新</button>
    </form>

    @error('header_image')
        <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
    @enderror
</div>



            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">自分の投稿</h3>

                        {{-- 新規投稿（ユーザー名付き） --}}
                        <a href="{{ route('user.posts.create', auth()->user()->username) }}"
                           class="inline-flex items-center px-3 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">
                            新規投稿を作成
                        </a>
                    </div>

                    @if ($posts->count() === 0)
                        <p>まだ投稿がありません。</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($posts as $post)
                                <li class="border p-3 rounded flex justify-between items-start">
                                    {{-- 左：タイトル・日時など --}}
                                    <div>
                                        <div class="font-medium">
                                            {{-- 詳細（ユーザー名 + スラッグ） --}}
                                            <a href="{{ route('user.posts.show', [$post->user->username, $post->slug]) }}"
                                               class="text-blue-600 underline">
                                                {{ $post->title }}
                                            </a>

                                            @if(!$post->is_published)
                                                <span class="ml-2 inline-block text-xs px-2 py-0.5 rounded bg-gray-200">下書き</span>
                                            @endif
                                        </div>

                                        <div class="text-sm text-gray-500 mt-1">
                                            @if($post->is_published && $post->published_at)
                                                公開: {{ $post->published_at->format('Y-m-d H:i') }}
                                            @else
                                                作成: {{ $post->created_at->format('Y-m-d H:i') }}
                                            @endif
                                        </div>
                                    </div>

                                    {{-- 右：操作ボタン群 --}}
                                    <div class="flex gap-3 items-center">
                                        @can('update', $post)
                                            @if(!$post->is_published)
                                                {{-- 公開する（ユーザー名付き） --}}
                                                <form method="POST"
                                                      action="{{ route('user.posts.publish', [auth()->user()->username, $post->slug]) }}">
                                                    @csrf
                                                    <button class="text-sm underline text-green-700">
                                                        公開する
                                                    </button>
                                                </form>
                                            @else
                                                {{-- 非公開にする（ユーザー名付き） --}}
                                                <form method="POST"
                                                      action="{{ route('user.posts.unpublish', [auth()->user()->username, $post->slug]) }}"
                                                      onsubmit="return confirm('この記事を非公開にしますか？');">
                                                    @csrf
                                                    <button class="text-sm underline text-yellow-700">
                                                        非公開にする
                                                    </button>
                                                </form>
                                            @endif

                                            {{-- 編集（ユーザー名付き） --}}
                                            <a href="{{ route('user.posts.edit', [$post->user->username, $post->slug]) }}"
                                               class="text-sm underline text-blue-600">
                                                編集
                                            </a>
                                        @endcan

                                        @can('delete', $post)
                                            {{-- 削除（ユーザー名付き） --}}
                                            <form method="POST"
                                                  action="{{ route('user.posts.destroy', [$post->user->username, $post->slug]) }}"
                                                  onsubmit="return confirm('この投稿を削除しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm underline text-red-600">
                                                    削除
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-4">
                            {{ $posts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
