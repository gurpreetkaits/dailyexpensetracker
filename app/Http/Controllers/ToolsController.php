<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingsResource;

class ToolsController extends Controller
{
    public function index()
    {
        return view('tools.simpleInterestCalculator.index');
    }
}
