<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PostController;

use App\Models\User;
use App\Models\Post;

/*
|--------------------------------------------------------------------------
| 通常ルート（/login, /register など）
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Breeze 認証
require __DIR__.'/auth.php';

// プロフィール（要ログイン）
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// /dashboard → /{username}/dashboard リダイレクト
Route::get('/dashboard', function () {
    return redirect()->route('user.dashboard', auth()->user()->username);
})->middleware(['auth', 'verified'])->name('dashboard');

// （任意）全体公開一覧を使うなら有効化
// Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

// 旧: /posts/{slug} → 新URL /{username}/{slug} へ 301
Route::get('/posts/{post:slug}', function (Post $post) {
    return redirect()->route('user.posts.show', [$post->user->username, $post->slug], 301);
})->name('posts.show.legacy');

/*
|--------------------------------------------------------------------------
| ユーザー名付きURL（最後にまとめる）
|  - 予約語（/login 等）をユーザー名と誤認しないように where()
|  - 並び順が超重要
|--------------------------------------------------------------------------
*/

$reserved = 'login|logout|register|password|email|verify|profile|dashboard|posts|api|storage|sanctum';

// 1) /{username}/dashboard（本人のみ）
Route::middleware(['auth', 'verified'])->group(function () use ($reserved) {
    Route::get('/{user:username}/dashboard', function (User $user) {
        abort_unless(auth()->id() === $user->id, 403);

        $posts = $user->posts()
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('dashboard', compact('posts'));
    })->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
      ->name('user.dashboard');

    // 2) 作成フォーム / 保存（本人のみ）
    Route::get('/{user:username}/posts/create', function (User $user) {
        abort_unless(auth()->id() === $user->id, 403);
        return view('posts.create');
    })->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
      ->name('user.posts.create');

    Route::post('/{user:username}/posts', [PostController::class, 'storeForUser'])
        ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
        ->name('user.posts.store');

    // ユーザーのヘッダー画像更新（本人のみ）
    Route::post('/{user:username}/header-image', [ProfileController::class, 'updateHeaderImage'])
        ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
        ->name('user.header.update');

    // 4) 編集/更新/削除 & 公開/非公開（本人のみ）
    Route::scopeBindings()->group(function () use ($reserved) {
        Route::get('/{user:username}/posts/{post:slug}/edit', [PostController::class, 'editForUser'])
            ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
            ->name('user.posts.edit');

        Route::put('/{user:username}/posts/{post:slug}', [PostController::class, 'updateForUser'])
            ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
            ->name('user.posts.update');

        Route::delete('/{user:username}/posts/{post:slug}', [PostController::class, 'destroyForUser'])
            ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
            ->name('user.posts.destroy');

        Route::post('/{user:username}/posts/{post:slug}/publish',   [PostController::class, 'publish'])
            ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
            ->name('user.posts.publish');

        Route::post('/{user:username}/posts/{post:slug}/unpublish', [PostController::class, 'unpublish'])
            ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
            ->name('user.posts.unpublish');
    });
});

// 3) /{username}/posts（公開済みのみ表示：一般公開）
Route::get('/{user:username}/posts', function (User $user) {
    $posts = $user->posts()
        ->where('is_published', true)
        ->orderByDesc('published_at')
        ->paginate(12);

    // ユーザーもビューに渡してヘッダー画像を表示できるように
    return view('posts.index', [
        'posts' => $posts,
        'user'  => $user,
    ]);
})->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
  ->name('user.posts.index');

// 5) /{username}/{slug}（公開詳細：scoped bindings）
Route::scopeBindings()->group(function () use ($reserved) {
    Route::get('/{user:username}/{post:slug}', [PostController::class, 'showUserPost'])
        ->where('user', "^(?!($reserved)$)[A-Za-z0-9._-]{1,30}$")
        ->name('user.posts.show');
});
