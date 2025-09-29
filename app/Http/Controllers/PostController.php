<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    use AuthorizesRequests;

    /**
     * （任意）全体の公開記事一覧。
     */
    public function index()
    {
        $posts = Post::where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('posts.index', compact('posts'));
    }

    /**
     * ユーザー名 + スラッグの公開詳細ページ
     * 例: /{username}/{slug}
     */
    public function showUserPost(User $user, Post $post)
    {
        // URL の {user} と投稿の所有者が一致しない場合は 404
        abort_if($post->user_id !== $user->id, 404);

        // 未公開は本人のみ閲覧可
        if (!$post->is_published) {
            abort_unless(auth()->check() && auth()->id() === $post->user_id, 404);
        }

        return view('posts.show', compact('post'));
    }

    /**
     * （ユーザー名付き）保存
     * 例: POST /{username}/posts
     */
    public function storeForUser(Request $request, User $user)
    {
        // 本人以外は作成不可
        abort_unless(auth()->id() === $user->id, 403);

        $validated = $request->validate([
            'title'     => ['required','string','max:255'],
            'body'      => ['required','string'],
            'publish'   => ['nullable'],
            'thumbnail' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        // ランダムスラッグ（タイトルから作るなら Str::slug($validated['title']) へ変更検討）
        $slug = Str::random(8);

        $post = new Post();
        $post->user_id      = $user->id;
        $post->title        = $validated['title'];
        $post->slug         = $slug;
        $post->body         = $validated['body'];
        $post->is_published = (bool)($validated['publish'] ?? false);
        $post->published_at = !empty($validated['publish']) ? now() : null;

        // サムネイル保存（/storage/app/public/thumbnails）
        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $post->thumbnail_path = $path;
        }

        $post->save();

        return redirect()
            ->route('user.posts.show', [$user->username, $post->slug])
            ->with('status', '投稿を作成しました。');
    }

    /**
     * （ユーザー名付き）編集フォーム
     * 例: GET /{username}/posts/{slug}/edit
     */
    public function editForUser(User $user, Post $post)
    {
        abort_if($post->user_id !== $user->id, 404);
        $this->authorize('update', $post);

        return view('posts.edit', compact('post'));
    }

    /**
     * （ユーザー名付き）更新
     * 例: PUT /{username}/posts/{slug}
     */
    public function updateForUser(Request $request, User $user, Post $post)
    {
        abort_if($post->user_id !== $user->id, 404);
        $this->authorize('update', $post);

        $validated = $request->validate([
            'title'     => ['required', 'string', 'max:255'],
            'body'      => ['required', 'string'],
            'publish'   => ['nullable'],
            'thumbnail' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $post->title = $validated['title'];
        $post->body  = $validated['body'];

        $willPublish = (bool)($validated['publish'] ?? false);
        $post->is_published = $willPublish;
        $post->published_at = $willPublish ? ($post->published_at ?? now()) : null;

        // 新しいサムネイルが来たら置き換え
        if ($request->hasFile('thumbnail')) {
            if ($post->thumbnail_path && Storage::disk('public')->exists($post->thumbnail_path)) {
                Storage::disk('public')->delete($post->thumbnail_path);
            }
            $path = $request->file('thumbnail')->store('thumbnails', 'public');
            $post->thumbnail_path = $path;
        }

        $post->save();

        return redirect()
            ->route('user.posts.show', [$user->username, $post->slug])
            ->with('status', '投稿を更新しました。');
    }

    /**
     * （ユーザー名付き）削除
     * 例: DELETE /{username}/posts/{slug}
     */
    public function destroyForUser(User $user, Post $post)
    {
        abort_if($post->user_id !== $user->id, 404);
        $this->authorize('delete', $post);

        // サムネイルの実ファイルも削除（存在すれば）
        if ($post->thumbnail_path && Storage::disk('public')->exists($post->thumbnail_path)) {
            Storage::disk('public')->delete($post->thumbnail_path);
        }

        $post->delete();

        return redirect()
            ->route('user.dashboard', $user->username)
            ->with('status', '投稿を削除しました。');
    }

    /**
     * （ユーザー名付き）公開
     * 例: POST /{username}/posts/{slug}/publish
     */
    public function publish(User $user, Post $post)
    {
        $this->authorize('update', $post);
        abort_if($post->user_id !== $user->id, 404);

        if (!$post->is_published) {
            $post->is_published = true;
            $post->published_at = now();
            $post->save();
        }

        return back()->with('status', '公開しました。');
    }

    /**
     * （ユーザー名付き）非公開
     * 例: POST /{username}/posts/{slug}/unpublish
     */
    public function unpublish(User $user, Post $post)
    {
        $this->authorize('update', $post);
        abort_if($post->user_id !== $user->id, 404);

        if ($post->is_published) {
            $post->is_published = false;
            $post->published_at = null;
            $post->save();
        }

        return back()->with('status', '非公開にしました。');
    }

    /**
     * （任意）旧: /posts/{slug} 用の詳細
     */
    public function show(Post $post)
    {
        if (!$post->is_published) {
            abort_unless(auth()->check() && auth()->id() === $post->user_id, 404);
        }
        return view('posts.show', compact('post'));
    }
}
