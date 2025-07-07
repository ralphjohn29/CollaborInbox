<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    /**
     * Show the welcome page.
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        return view('welcome');
    }
    
    /**
     * Simple test method to see if routing works.
     *
     * @return \Illuminate\Http\Response
     */
    public function hello()
    {
        return response('Hello, world! The WelcomeController is working!');
    }
} 