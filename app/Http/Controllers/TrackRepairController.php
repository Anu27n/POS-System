<?php

namespace App\Http\Controllers;

use App\Models\RepairJob;
use Illuminate\Http\Request;

class TrackRepairController extends Controller
{
    /**
     * Show the tracking form page
     */
    public function index()
    {
        return view('tracking.index');
    }

    /**
     * Search and display repair job status
     */
    public function search(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string',
            'phone' => 'nullable|string'
        ]);

        $ticketNumber = $request->input('ticket_number');
        $phone = $request->input('phone');

        // Search by ticket number
        $query = RepairJob::where('ticket_number', $ticketNumber);

        // Optionally verify with phone number for security
        if ($phone) {
            $query->where('customer_phone', 'like', '%' . substr($phone, -4));
        }

        $job = $query->with(['store', 'technician', 'statusLogs' => function ($q) {
            $q->orderBy('created_at', 'desc');
        }])->first();

        if (!$job) {
            return back()->with('error', 'No repair job found with this ticket number. Please check and try again.');
        }

        return view('tracking.show', compact('job'));
    }

    /**
     * Direct link to track a specific job
     */
    public function show($ticketNumber)
    {
        $job = RepairJob::where('ticket_number', $ticketNumber)
            ->with(['store', 'technician', 'statusLogs' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }])
            ->firstOrFail();

        return view('tracking.show', compact('job'));
    }
}
