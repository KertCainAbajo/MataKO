<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivity;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.activity', [
            'activities' => AdminActivity::with('admin:id,name')->latest('created_at')->latest('id')->paginate(30),
        ]);
    }
}
