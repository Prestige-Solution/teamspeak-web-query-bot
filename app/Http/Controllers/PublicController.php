<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;

class PublicController extends Controller
{
    public function viewPublicIndex(): Factory|View|Application
    {
        return view('frontend.index');
    }
}
