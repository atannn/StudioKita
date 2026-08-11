<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:120',
            'body' => 'required|string|max:1000',
            'target_role' => 'required|string|in:owner,all',
        ]);

        Announcement::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'target_role' => $data['target_role'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Pengumuman berhasil dikirim.');
    }
}
