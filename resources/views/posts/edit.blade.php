<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">記事を編集</h2>
    </x-slot>

    <div class="p-6 max-w-2xl mx-auto">
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="post-form" method="POST"
              action="{{ route('user.posts.update', [$post->user->username, $post->slug]) }}"
              enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block mb-1">タイトル</label>
                <input name="title" class="border p-2 w-full" value="{{ old('title', $post->title) }}">
            </div>

            <div class="mb-2">
                <label class="block mb-1">本文</label>

                <!-- カスタムツールバー（H1〜H6 + pxサイズ） -->
                <div id="toolbar" class="mb-2 flex flex-wrap gap-2 items-center">
                    <select class="ql-header">
                        <option selected></option>
                        <option value="1">H1</option>
                        <option value="2">H2</option>
                        <option value="3">H3</option>
                        <option value="4">H4</option>
                        <option value="5">H5</option>
                        <option value="6">H6</option>
                    </select>

                    <select class="ql-size">
                        <option value="12px">12px</option>
                        <option value="14px">14px</option>
                        <option value="16px" selected>16px</option>
                        <option value="18px">18px</option>
                        <option value="24px">24px</option>
                        <option value="32px">32px</option>
                    </select>

                    <button class="ql-bold"></button>
                    <button class="ql-italic"></button>
                    <button class="ql-underline"></button>

                    <select class="ql-color"></select>
                    <select class="ql-background"></select>

                    <button class="ql-blockquote"></button>
                    <button class="ql-code-block"></button>

                    <select class="ql-list">
                        <option value="ordered"></option>
                        <option value="bullet"></option>
                    </select>

                    <select class="ql-align"></select>
                    <button class="ql-link"></button>
                    <button class="ql-clean"></button>
                </div>

                <!-- 編集領域 -->
                <div id="editor" class="border rounded"></div>

                <!-- 送信用の hidden input（HTML格納） -->
                <input type="hidden" name="body" id="body" value="{{ old('body', $post->body) }}">
            </div>

            <!-- サムネイル -->
            <div class="mb-4">
                <label class="block mb-1">サムネイル画像（任意）</label>

                @if($post->thumbnail_path)
                    <div class="mb-2">
                        <span class="text-sm text-gray-600">現在の画像</span>
                        <img src="{{ $post->thumbnail_url }}" alt="" class="w-40 h-40 object-cover rounded mt-1">
                    </div>
                @endif

                <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="block">
                <p class="text-xs text-gray-500 mt-1">アップロードすると上書きされます</p>
                @error('thumbnail')
                    <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                @enderror

                <!-- 差し替えプレビュー -->
                <div class="mt-2">
                    <img id="preview" src="#" alt="プレビュー" class="max-h-48 hidden rounded">
                </div>
            </div>

            <label class="inline-flex items-center mb-4">
                <input type="checkbox" name="publish" value="1" class="mr-2"
                       {{ old('publish', $post->is_published) ? 'checked' : '' }}>
                公開する
            </label>

            <div class="flex items-center gap-3">
                <button class="bg-blue-600 text-white px-4 py-2 rounded">更新</button>
                <a href="{{ route('user.posts.show', [$post->user->username, $post->slug]) }}"
                   class="underline text-sm">戻る</a>
            </div>
        </form>
    </div>
</x-app-layout>

{{-- Quill CDN --}}
<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

{{-- Quill 初期化 + pxサイズを style 出力 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const Size = Quill.import('attributors/style/size');
  Size.whitelist = ['12px','14px','16px','18px','24px','32px'];
  Quill.register(Size, true);

  const quill = new Quill('#editor', {
    theme: 'snow',
    modules: { toolbar: '#toolbar' }
  });

  // hidden（old or $post->body）の内容を初期表示に反映
  const initial = document.getElementById('body').value || '';
  quill.root.innerHTML = initial;

  // 送信時にエディタHTMLを hidden へ
  document.getElementById('post-form').addEventListener('submit', function () {
    document.getElementById('body').value = quill.root.innerHTML;
  });

  // サムネイル差し替えプレビュー
  const input = document.getElementById('thumbnail');
  input.addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('preview');
    if (file) {
      const reader = new FileReader();
      reader.onload = function(ev) {
        preview.src = ev.target.result;
        preview.classList.remove('hidden');
      }
      reader.readAsDataURL(file);
    } else {
      preview.classList.add('hidden');
      preview.src = '#';
    }
  });
});
</script>

<style>
  .ql-editor { min-height: 240px; padding: .75rem; }
</style>
