<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of staff members
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();
        $staff = $store->staff()->with('user')->orderBy('name')->get();

        return view('store-owner.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff member
     */
    public function create()
    {
        $roles = Staff::ROLES;
        $permissions = Staff::PERMISSIONS;

        return view('store-owner.staff.create', compact('roles', 'permissions'));
    }

    /**
     * Store a newly created staff member
     */
    public function store(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(array_keys(Staff::ROLES))],
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in(array_keys(Staff::PERMISSIONS)),
            'create_account' => 'boolean',
            'password' => 'required_if:create_account,1|nullable|min:8|confirmed',
        ]);

        // Create staff record
        $staff = Staff::create([
            'store_id' => $store->id,
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? null,
            'is_active' => true,
        ]);

        // Create user account if requested
        if ($request->boolean('create_account') && !empty($validated['email'])) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'staff',
                'phone' => $validated['phone'] ?? null,
                'staff_id' => $staff->id,
                'works_at_store_id' => $store->id,
                'is_active' => true,
            ]);

            $staff->update(['user_id' => $user->id]);
        }

        return redirect()->route('store-owner.staff.index')
            ->with('success', 'Staff member added successfully.');
    }

    /**
     * Show the form for editing a staff member
     */
    public function edit(Staff $staff)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($staff->store_id !== $store->id) {
            abort(403);
        }

        $roles = Staff::ROLES;
        $permissions = Staff::PERMISSIONS;
        $staff->load('user');

        return view('store-owner.staff.edit', compact('staff', 'roles', 'permissions'));
    }

    /**
     * Update the specified staff member
     */
    public function update(Request $request, Staff $staff)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($staff->store_id !== $store->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => ['required', Rule::in(array_keys(Staff::ROLES))],
            'permissions' => 'nullable|array',
            'permissions.*' => Rule::in(array_keys(Staff::PERMISSIONS)),
            'is_active' => 'boolean',
            'create_account' => 'boolean',
            'reset_password' => 'boolean',
            'password' => 'nullable|min:8|confirmed|required_if:create_account,1|required_if:reset_password,1',
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Create user account if requested and staff doesn't have one
        if ($request->boolean('create_account') && !$staff->user && !empty($validated['email'])) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'staff',
                'phone' => $validated['phone'] ?? null,
                'staff_id' => $staff->id,
                'works_at_store_id' => $store->id,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $staff->update(['user_id' => $user->id]);

            return redirect()->route('store-owner.staff.index')
                ->with('success', 'Staff member updated and login account created successfully.');
        }

        // Update associated user if exists
        if ($staff->user) {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'] ?? $staff->user->email,
                'phone' => $validated['phone'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ];

            // Reset password if requested
            if ($request->boolean('reset_password') && !empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $staff->user->update($updateData);
        }

        return redirect()->route('store-owner.staff.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff member
     */
    public function destroy(Staff $staff)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($staff->store_id !== $store->id) {
            abort(403);
        }

        // Deactivate associated user account instead of deleting
        if ($staff->user) {
            $staff->user->update([
                'is_active' => false,
                'staff_id' => null,
                'works_at_store_id' => null,
            ]);
        }

        $staff->delete();

        return redirect()->route('store-owner.staff.index')
            ->with('success', 'Staff member removed successfully.');
    }

    /**
     * Toggle staff active status
     */
    public function toggleStatus(Staff $staff)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($staff->store_id !== $store->id) {
            abort(403);
        }

        $staff->update(['is_active' => !$staff->is_active]);

        if ($staff->user) {
            $staff->user->update(['is_active' => $staff->is_active]);
        }

        return back()->with('success', 'Staff status updated.');
    }
}
