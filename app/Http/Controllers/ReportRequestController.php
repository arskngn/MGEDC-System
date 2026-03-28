<?php

namespace App\Http\Controllers;

use App\Models\GeneralSetting;
use App\Models\ReportRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportRequestController extends Controller
{
    public function index()
    {
        $query = ReportRequest::with('user');

        /** @var User $user */
        $user = Auth::user();

        // If not admin, only show their own reports
        if (! $user->hasRole('admin')) {
            $query->where('user_id', Auth::id());
        }

        $perPage = GeneralSetting::first()->records_per_page ?? 10;
        $reports = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return view('pages.report-request', [
            'title' => 'Report & Request',
            'reports' => $reports,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:Report Bug,Feature Request',
            'message' => 'required|string|min:10',
        ]);

        ReportRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'message' => $validated['message'],
            'status' => 'Submitted',
        ]);

        return redirect()->route('report.request')->with('success', 'Your report/request has been submitted successfully!');
    }

    public function update(Request $request, ReportRequest $reportRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only owner or admin can update
        if ($reportRequest->user_id !== Auth::id() && ! $user->hasRole('admin')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $validated = $request->validate([
            'type' => 'required|in:Report Bug,Feature Request',
            'message' => 'required|string|min:10',
            'status' => 'nullable|in:Submitted,In Progress,Resolved,Closed',
        ]);

        $data = [
            'type' => $validated['type'],
            'message' => $validated['message'],
        ];

        // Only admins can update status
        if ($user->hasRole('admin') && isset($validated['status'])) {
            $data['status'] = $validated['status'];
        }

        $reportRequest->update($data);

        return redirect()->route('report.request')->with('success', 'The report/request has been updated successfully!');
    }

    public function destroy(ReportRequest $reportRequest)
    {
        /** @var User $user */
        $user = Auth::user();

        // Only owner or admin can delete
        if ($reportRequest->user_id !== Auth::id() && ! $user->hasRole('admin')) {
            return back()->with('error', 'Unauthorized action.');
        }

        $reportRequest->delete();

        return redirect()->route('report.request')->with('success', 'The report/request has been deleted successfully!');
    }
}
