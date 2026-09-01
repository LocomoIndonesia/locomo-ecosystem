<?php

namespace App\Modules\Info\Controllers;

use App\Http\Controllers\Controller;

class InfoController extends Controller
{
    public function about()
    {
        return view('info::about');
    }

    public function safety()
    {
        return view('info::safety');
    }
}
