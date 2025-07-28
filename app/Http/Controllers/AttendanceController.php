<?php

namespace App\Http\Controllers;

use App\Models\Qrcode;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    //
    public function index($token)
    {
        $qrCode = Qrcode::where('token', $token)->first();

        if (!$qrCode) {
            abort(404, 'QR Code not found');
        }
        
        if (now()->gt($qrCode->expires_at)) {
            abort(410, 'QR Code has expired');
        }
        
        // Logic to display attendance records
        return Inertia::render('qrcode/scanned', [
            // Pass any necessary data to the view
        ]);
    }
}
