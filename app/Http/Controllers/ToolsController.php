<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingsResource;

class ToolsController extends Controller
{
    public function index(){
        return view('tools.index');
    }
    public function simpleInterestCalculator()
    {
        return view('tools.simpleInterestCalculator.index');
    }
    public function sipCalculator()
    {
        return view('tools.sip-calculator');
    }
}
