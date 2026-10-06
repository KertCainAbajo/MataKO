<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use App\Models\Question;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Search users, questions and admin activity from the top bar.
     */
    public function __invoke(Request $request): View
    {
        $term = trim((string) $request->query('q', ''));
        $like = "%{$term}%";

        return view('admin.search', [
            'term' => $term,
            'users' => $term === '' ? collect() : User::where(fn ($query) => $query->where('name', 'like', $like)->orWhere('email', 'like', $like))->limit(10)->get(),
            'questions' => $term === '' ? collect() : Question::where(fn ($query) => $query->where('question', 'like', $like)->orWhere('symptom', 'like', $like))->orderBy('audience')->orderBy('position')->limit(10)->get(),
            'activities' => $term === '' ? collect() : AdminActivity::with('admin:id,name')->where('description', 'like', $like)->latest('created_at')->limit(10)->get(),
        ]);
    }
}
