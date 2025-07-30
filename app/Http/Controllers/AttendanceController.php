<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Qrcode;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    //
    public function index($token)
    {
        $user = Auth::user();
        $qrCode = Qrcode::where('token', $token)->first();
        $attendance = Attendance::where('name', $user->name)
        ->where('date', now()->toDateString())->first();


        if (!$qrCode) {
            abort(404, 'QR Code tidak ditemukan');
        }
        
        if (now()->gt($qrCode->expires_at)) {
            abort(410, 'QR Code telah kedaluwarsa');
        }

        
        if ($user->shift !== $qrCode->shift || $user->division !== $qrCode->division) {
            abort(403, 'Anda tidak diizinkan untuk mengikuti shift atau divisi ini');
        }


        if ($attendance && now()->gt(Shift::where('name', $qrCode->shift)->first()->end_time)) {

            if ($attendance->check_out) {
                return Inertia::render('qrcode/scanned', [
                    'division' => $qrCode->division,
                    'shift' => $qrCode->shift,
                    'message' => 'Kamu sudah melakukan check-out hari ini.',
                ]);
            }

            $attendance->update([
                'check_out' => Carbon::now(),
            ]);
            
        } else {

            if ($attendance) {
                return Inertia::render('qrcode/scanned', [
                    'division' => $qrCode->division,
                    'shift' => $qrCode->shift,
                    'message' => 'Kamu sudah melakukan check-in hari ini.',
                ]);
            }

            $status = $this->attendStatus($qrCode);
            
            Attendance::create([
                'name' => $user->name,
                'qrcode' => $qrCode->token,
                'shift' => $qrCode->shift,
                'division' => $qrCode->division,
                'date' => now()->toDateString(),
                'check_in' => Carbon::now(),
                'status' => $status,
            ]);

        }
        
        return Inertia::render('qrcode/scanned', [
            'division' => $qrCode->division,
            'shift' => $qrCode->shift,
            'message' => 'Berhasil melakukan check-in atau check-out.',
        ]);
    }

    public function attendStatus($qrCode) {
        $shift = Shift::where('name', $qrCode->shift)->first();

        if (!$shift) {
            abort(404, 'Shift tidak ditemukan');
        }
        if (now()->gt($shift->start_time)) {
            return 'late';
        }

        return 'present';
    }
}
