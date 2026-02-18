<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContestWinner;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->session()->has('admin_id')) {
            return redirect()->route('admin.login');
        }
        $winners = ContestWinner::with('post.media')->latest()->paginate(20);
        $posts = Post::query()->latest()->limit(100)->get();
        return view('admin.contest', compact('winners', 'posts'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (!$request->session()->has('admin_id')) {
            return redirect()->route('admin.login');
        }
        $data = $request->validate([
            'post_id' => ['required', 'exists:posts,id'],
            'period_label' => ['nullable', 'string', 'max:120'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date','after_or_equal:period_start'],
        ]);
        ContestWinner::create($data);
        return redirect()->route('admin.contest')->with('status', 'Winner saved');
    }
} 