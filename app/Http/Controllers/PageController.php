<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function hello()
    {
        // ここでDBは触らず、まずはビューに値を渡すだけ
        return view('hello', ['message' => 'Hello, Laravel!']);
    }
}
