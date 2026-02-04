<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\RepairJob;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TechnicianDashboardController extends Controller
{
    /**
     * Get the current staff member
     */
    protected function getTechnician()
    {
        $user = Auth::user();
        
        if ($user->role === 'staff') {
            return $user->staff;
        }
        
        return null;
    }

    /**
     * Show technician dashboard
     */
    public function index()
    {
        $technician = $this->getTechnician();
        
        if (!$technician) {
            return redirect()->route('store-owner.dashboard')
                ->with('error', 'Technician dashboard is only available for staff members.');
        }

        $storeId = $technician->store_id;

        // My open jobs
        $myOpenJobs = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technician->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->count();

        // My jobs due today
        $myDueToday = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technician->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->whereDate('expected_delivery_at', today())
            ->count();

        // My overdue jobs
        $myOverdue = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technician->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->where('expected_delivery_at', '<', today())
            ->count();

        // Completed this week
        $completedThisWeek = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technician->id)
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Completed today
        $completedToday = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technician->id)
            ->where('status', 'delivered')
            ->whereDate('delivered_at', today())
            ->count();

        // Completed this month
        $completedThisMonth = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technician->id)
            ->where('status', 'delivered')
            ->whereMonth('delivered_at', now()->month)
            ->whereYear('delivered_at', now()->year)
            ->count();

        // Calculate average repair time (in hours)
        $avgRepairTime = $this->calculateAvgRepairTime($technician->id, $storeId);

        // Calculate on-time delivery rate
        $onTimeRate = $this->calculateOnTimeRate($technician->id, $storeId);

        // Average rating (placeholder - would need customer feedback system)
        $avgRating = 4.5; // Placeholder

        // Get my active jobs
        $myActiveJobs = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technician->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->with(['customer'])
            ->orderBy('priority', 'desc') // Urgent first
            ->orderBy('expected_delivery_at', 'asc')
            ->take(10)
            ->get();

        return view('store-owner.technician-dashboard', compact(
            'technician',
            'myOpenJobs',
            'myDueToday',
            'myOverdue',
            'completedThisWeek',
            'completedToday',
            'completedThisMonth',
            'avgRepairTime',
            'onTimeRate',
            'avgRating',
            'myActiveJobs'
        ));
    }

    /**
     * Calculate average repair time in hours
     */
    private function calculateAvgRepairTime(int $technicianId, int $storeId): string
    {
        $jobs = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technicianId)
            ->where('status', 'delivered')
            ->whereNotNull('repair_started_at')
            ->whereNotNull('repaired_at')
            ->select('repair_started_at', 'repaired_at')
            ->orderBy('delivered_at', 'desc')
            ->take(50) // Last 50 jobs
            ->get();

        if ($jobs->isEmpty()) {
            return 'N/A';
        }

        $totalHours = 0;
        foreach ($jobs as $job) {
            $totalHours += $job->repair_started_at->diffInHours($job->repaired_at);
        }

        $avg = $totalHours / $jobs->count();
        return round($avg, 1);
    }

    /**
     * Calculate on-time delivery rate
     */
    private function calculateOnTimeRate(int $technicianId, int $storeId): int
    {
        $totalDelivered = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technicianId)
            ->where('status', 'delivered')
            ->whereNotNull('expected_delivery_at')
            ->count();

        if ($totalDelivered === 0) {
            return 100;
        }

        $onTime = RepairJob::where('store_id', $storeId)
            ->where('assigned_technician_id', $technicianId)
            ->where('status', 'delivered')
            ->whereNotNull('expected_delivery_at')
            ->whereColumn('delivered_at', '<=', 'expected_delivery_at')
            ->count();

        return round(($onTime / $totalDelivered) * 100);
    }
}
