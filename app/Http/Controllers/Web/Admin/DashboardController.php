<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\ContestWinner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
	public function __invoke(Request $request)
	{
		$posts = Post::query()->with('media')->latest()->paginate(10);

		if ($request->ajax()) {
			$html = view('admin._posts', ['posts' => $posts, 'winnerPostIds' => ContestWinner::pluck('post_id')->all()])->render();
			return response()->json([
				'html' => $html,
				'next_page_url' => $posts->nextPageUrl(),
			]) ;
		}

		return view('admin.dashboard', [
			'title' => 'Admin panel',
			'posts' => $posts,
			'winnerPostIds' => ContestWinner::pluck('post_id')->all(),
		]);
	}
} 