<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Announcement::class);

        $announcements = Announcement::query()
            ->when(! $request->user()->isSuperAdmin(), fn ($query) => $query->where('status', 'active'))
            ->latest()
            ->get();

        return view('announcements.index', compact('announcements'));
    }

    public function store(AnnouncementRequest $request): RedirectResponse
    {
        Announcement::create([...$request->validated(), 'created_by' => $request->user()->id]);

        return redirect()->route('announcements.index')->with('status', 'Announcement created.');
    }

    public function update(AnnouncementRequest $request, Announcement $announcement): RedirectResponse
    {
        $announcement->update($request->validated());

        return redirect()->route('announcements.index')->with('status', 'Announcement updated.');
    }
}
