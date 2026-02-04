<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\RepairJob;
use App\Models\RepairJobPart;
use App\Models\Product;
use App\Models\Staff;
use App\Models\StoreCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RepairJobController extends Controller
{
    /**
     * Get the current store
     */
    protected function getStore()
    {
        return Auth::user()->getEffectiveStore();
    }

    /**
     * Get current staff member if applicable
     */
    protected function getStaff()
    {
        $user = Auth::user();
        
        if ($user->role === 'staff') {
            return $user->staffProfile;
        }
        
        return null;
    }

    /**
     * Display a listing of repair jobs.
     */
    public function index(Request $request)
    {
        $store = $this->getStore();
        
        if (!$store) {
            return redirect()->route('store-owner.stores.create')
                ->with('error', 'Please create a store first.');
        }

        $query = RepairJob::forStore($store->id)
            ->with(['customer', 'technician']);

        // Filter by status
        if ($request->filled('status')) {
            $query->withStatus($request->status);
        }

        // Filter by technician
        if ($request->filled('technician_id')) {
            $query->assignedTo($request->technician_id);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Search by ticket number or customer name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('device_brand', 'like', "%{$search}%")
                  ->orWhere('device_model', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        // Default sort by created_at desc
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $repairJobs = $query->paginate(20)->withQueryString();
        
        // Get technicians for filter dropdown
        $technicians = $store->technicians()->get();
        
        // Get stats
        $stats = [
            'open' => RepairJob::forStore($store->id)->open()->count(),
            'due_today' => RepairJob::forStore($store->id)->dueToday()->count(),
            'overdue' => RepairJob::forStore($store->id)->overdue()->count(),
            'completed_today' => RepairJob::forStore($store->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->count(),
        ];

        return view('store-owner.repair-jobs.index', compact(
            'repairJobs', 
            'technicians', 
            'stats',
            'store'
        ));
    }

    /**
     * Show the form for creating a new repair job.
     */
    public function create()
    {
        $store = $this->getStore();
        
        if (!$store) {
            return redirect()->route('store-owner.stores.create')
                ->with('error', 'Please create a store first.');
        }

        $customers = $store->customers()->orderBy('name')->get();
        $technicians = $store->technicians()->where('is_active', true)->get();

        return view('store-owner.repair-jobs.create', compact(
            'store', 
            'customers', 
            'technicians'
        ));
    }

    /**
     * Store a newly created repair job.
     */
    public function store(Request $request)
    {
        $store = $this->getStore();
        
        if (!$store) {
            return back()->with('error', 'Store not found.');
        }

        $validated = $request->validate([
            'store_customer_id' => 'nullable|exists:store_customers,id',
            'customer_name' => 'required_without:store_customer_id|nullable|string|max:255',
            'customer_phone' => 'required_without:store_customer_id|nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'device_type' => 'required|in:phone,tablet,laptop,watch,gaming_console,other',
            'device_brand' => 'nullable|string|max:100',
            'device_model' => 'nullable|string|max:100',
            'imei_serial' => 'nullable|string|max:100',
            'device_color' => 'nullable|string|max:50',
            'device_password' => 'nullable|string|max:100',
            'device_accessories' => 'nullable|array',
            'issue_description' => 'required|string|max:2000',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_technician_id' => 'nullable|exists:staff,id',
            'estimated_cost' => 'nullable|numeric|min:0',
            'advance_paid' => 'nullable|numeric|min:0',
            'expected_delivery_at' => 'nullable|date|after:now',
            'warranty_days' => 'nullable|integer|min:0|max:365',
            'internal_notes' => 'nullable|string|max:1000',
        ]);

        // Create customer if not selected
        $customerId = $validated['store_customer_id'] ?? null;
        if (!$customerId && !empty($validated['customer_name'])) {
            $customer = StoreCustomer::create([
                'store_id' => $store->id,
                'name' => $validated['customer_name'],
                'phone' => $validated['customer_phone'] ?? null,
                'email' => $validated['customer_email'] ?? null,
            ]);
            $customerId = $customer->id;
        }

        // Get current staff for received_by
        $staff = $this->getStaff();

        $repairJob = RepairJob::create([
            'store_id' => $store->id,
            'store_customer_id' => $customerId,
            'device_type' => $validated['device_type'],
            'device_brand' => $validated['device_brand'] ?? null,
            'device_model' => $validated['device_model'] ?? null,
            'imei_serial' => $validated['imei_serial'] ?? null,
            'device_color' => $validated['device_color'] ?? null,
            'device_password' => $validated['device_password'] ?? null,
            'device_accessories' => $validated['device_accessories'] ?? null,
            'issue_description' => $validated['issue_description'],
            'priority' => $validated['priority'],
            'assigned_technician_id' => $validated['assigned_technician_id'] ?? null,
            'received_by_id' => $staff?->id,
            'estimated_cost' => $validated['estimated_cost'] ?? null,
            'advance_paid' => $validated['advance_paid'] ?? 0,
            'expected_delivery_at' => $validated['expected_delivery_at'] ?? null,
            'warranty_days' => $validated['warranty_days'] ?? 0,
            'internal_notes' => $validated['internal_notes'] ?? null,
            'status' => 'received',
        ]);

        // Log the initial status
        $repairJob->statusLogs()->create([
            'old_status' => null,
            'new_status' => 'received',
            'changed_by_id' => Auth::id(),
            'notes' => 'Job created',
        ]);

        return redirect()->route('store-owner.repair-jobs.show', $repairJob)
            ->with('success', "Repair job {$repairJob->ticket_number} created successfully!");
    }

    /**
     * Display the specified repair job.
     */
    public function show(RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $repairJob->load([
            'customer',
            'technician',
            'receivedBy',
            'parts.product',
            'parts.addedBy',
            'statusLogs.changedBy',
            'order',
        ]);

        // Get available parts (products) for adding to job
        $spareParts = $store->products()
            ->where('status', 'available')
            ->orderBy('name')
            ->get();

        // Get technicians for reassignment
        $technicians = $store->technicians()->where('is_active', true)->get();

        return view('store-owner.repair-jobs.show', compact(
            'repairJob',
            'spareParts',
            'technicians',
            'store'
        ));
    }

    /**
     * Show the form for editing the specified repair job.
     */
    public function edit(RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $customers = $store->customers()->orderBy('name')->get();
        $technicians = $store->technicians()->where('is_active', true)->get();

        return view('store-owner.repair-jobs.edit', compact(
            'repairJob',
            'store',
            'customers',
            'technicians'
        ));
    }

    /**
     * Update the specified repair job.
     */
    public function update(Request $request, RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'store_customer_id' => 'nullable|exists:store_customers,id',
            'device_type' => 'required|in:phone,tablet,laptop,watch,gaming_console,other',
            'device_brand' => 'nullable|string|max:100',
            'device_model' => 'nullable|string|max:100',
            'imei_serial' => 'nullable|string|max:100',
            'device_color' => 'nullable|string|max:50',
            'device_password' => 'nullable|string|max:100',
            'device_accessories' => 'nullable|array',
            'issue_description' => 'required|string|max:2000',
            'diagnosis_notes' => 'nullable|string|max:2000',
            'repair_notes' => 'nullable|string|max:2000',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_technician_id' => 'nullable|exists:staff,id',
            'estimated_cost' => 'nullable|numeric|min:0',
            'final_cost' => 'nullable|numeric|min:0',
            'advance_paid' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'expected_delivery_at' => 'nullable|date',
            'warranty_days' => 'nullable|integer|min:0|max:365',
            'internal_notes' => 'nullable|string|max:1000',
        ]);

        $repairJob->update($validated);

        return redirect()->route('store-owner.repair-jobs.show', $repairJob)
            ->with('success', 'Repair job updated successfully!');
    }

    /**
     * Update job status
     */
    public function updateStatus(Request $request, RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string',
            'notes' => 'nullable|string|max:500',
            'notify_customer' => 'boolean',
        ]);

        if (!$repairJob->canTransitionTo($validated['status'])) {
            return back()->with('error', 'Invalid status transition.');
        }

        $repairJob->updateStatus(
            $validated['status'],
            Auth::id(),
            $validated['notes'] ?? null,
            $validated['notify_customer'] ?? false
        );

        return back()->with('success', 'Status updated successfully!');
    }

    /**
     * Assign technician to job
     */
    public function assignTechnician(Request $request, RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'assigned_technician_id' => 'nullable|exists:staff,id',
        ]);

        $repairJob->update([
            'assigned_technician_id' => $validated['assigned_technician_id'],
        ]);

        $technicianName = $validated['assigned_technician_id'] 
            ? Staff::find($validated['assigned_technician_id'])?->name 
            : 'Unassigned';

        return back()->with('success', "Technician updated to: {$technicianName}");
    }

    /**
     * Add part to repair job
     */
    public function addPart(Request $request, RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        
        // Check stock
        if ($product->track_inventory && $product->stock_quantity < $validated['quantity']) {
            return back()->with('error', "Insufficient stock. Available: {$product->stock_quantity}");
        }

        $unitPrice = $validated['unit_price'] ?? $product->price;
        $staff = $this->getStaff();

        RepairJobPart::create([
            'repair_job_id' => $repairJob->id,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $validated['quantity'],
            'added_by_id' => $staff?->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Part added: {$product->name} x{$validated['quantity']}");
    }

    /**
     * Remove part from repair job
     */
    public function removePart(RepairJob $repairJob, RepairJobPart $part)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id || $part->repair_job_id !== $repairJob->id) {
            abort(403);
        }

        $partName = $part->part_name;
        $part->delete(); // Stock is restored via model event

        return back()->with('success', "Part removed: {$partName}");
    }

    /**
     * Print job card
     */
    public function printJobCard(RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $repairJob->load(['customer', 'technician', 'receivedBy']);

        return view('store-owner.repair-jobs.print-job-card', compact('repairJob', 'store'));
    }

    /**
     * Show invoice for repair job
     */
    public function invoice(RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        $repairJob->load(['customer', 'technician', 'parts.product']);

        return view('store-owner.repair-jobs.invoice', compact('repairJob', 'store'));
    }

    /**
     * Delete repair job
     */
    public function destroy(RepairJob $repairJob)
    {
        $store = $this->getStore();
        
        if (!$store || $repairJob->store_id !== $store->id) {
            abort(403);
        }

        // Only allow deletion of received/cancelled jobs
        if (!in_array($repairJob->status, ['received', 'cancelled'])) {
            return back()->with('error', 'Cannot delete a job that is in progress or completed.');
        }

        $ticketNumber = $repairJob->ticket_number;
        $repairJob->delete();

        return redirect()->route('store-owner.repair-jobs.index')
            ->with('success', "Repair job {$ticketNumber} deleted.");
    }
}
