<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class QrCodeController extends Controller
{
    //
    public function index() {
        return Inertia::render("qrcode/scan");
    }
}
