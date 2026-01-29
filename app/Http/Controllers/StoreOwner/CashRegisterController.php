<?php

namespace App\Http\Controllers\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\CashRegisterSession;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashRegisterController extends Controller
{
    /**
     * Display cash register status / open session modal
     */
    public function index()
    {
        $store = auth()->user()->getEffectiveStore();
        $user = auth()->user();

        // Check for any open session
        $currentSession = CashRegisterSession::getAnyOpenSession($store->id);

        // Get last closed session for suggested opening amount
        $lastClosedSession = CashRegisterSession::getLastClosedSession($store->id);
        $suggestedOpeningCash = $lastClosedSession ? $lastClosedSession->closing_cash : 0;

        // Get all sessions (paginated)
        $sessions = $store->cashRegisterSessions()
            ->with('staff')
            ->orderBy('opened_at', 'desc')
            ->paginate(15);

        // Today's stats - show stats from all currently active/today's sessions
        $todaySessions = $store->cashRegisterSessions()
            ->where(function($query) {
                $query->whereDate('opened_at', today())
                    ->orWhereNull('closed_at'); // Include currently open session
            })
            ->get();
        
        $todayStats = [
            'session_count' => $todaySessions->count(),
            'total_sales' => $todaySessions->sum(function($session) {
                return ($session->total_cash_sales ?? 0) + 
                       ($session->total_card_sales ?? 0) + 
                       ($session->total_upi_sales ?? 0) + 
                       ($session->total_other_sales ?? 0);
            }),
            'cash_sales' => $todaySessions->sum('total_cash_sales'),
            'card_sales' => $todaySessions->sum('total_card_sales'),
        ];

        return view('store-owner.cash-register.index', compact(
            'store',
            'currentSession',
            'lastClosedSession',
            'suggestedOpeningCash',
            'sessions',
            'todayStats'
        ));
    }

    /**
     * Open a new cash register session
     */
    public function open(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();
        $user = auth()->user();

        // Check if there's already an open session
        $existingSession = CashRegisterSession::getAnyOpenSession($store->id);
        if ($existingSession) {
            return redirect()->route('store-owner.cash-register.index')
                ->with('error', 'There is already an open cash register session. Please close it first.');
        }

        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        CashRegisterSession::create([
            'store_id' => $store->id,
            'staff_id' => $user->id,
            'opening_cash' => $validated['opening_cash'],
            'notes' => $validated['notes'] ?? null,
            'opened_at' => now(),
        ]);

        return redirect()->route('store-owner.pos.index')
            ->with('success', 'Cash register opened successfully. You can now start processing orders.');
    }

    /**
     * Close the current cash register session
     * Closing cash is now automatically calculated
     */
    public function close(Request $request, CashRegisterSession $session)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($session->store_id !== $store->id) {
            abort(403);
        }

        if ($session->closed_at) {
            return redirect()->route('store-owner.cash-register.index')
                ->with('error', 'This session is already closed.');
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        // Closing cash is automatically calculated - no manual input
        $session->closeSession($validated['notes'] ?? null);

        return redirect()->route('store-owner.cash-register.index')
            ->with('success', 'Cash register closed successfully.');
    }

    /**
     * Add cash in/out transaction
     */
    public function addCash(Request $request, CashRegisterSession $session)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($session->store_id !== $store->id) {
            abort(403);
        }

        if ($session->closed_at) {
            return redirect()->route('store-owner.cash-register.index')
                ->with('error', 'Cannot add transactions to a closed session.');
        }

        $validated = $request->validate([
            'type' => 'required|in:cash_in,cash_out',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:255',
        ]);

        $session->addTransaction(
            $validated['type'],
            'cash',
            $validated['amount'],
            null,
            $validated['notes']
        );

        return redirect()->route('store-owner.cash-register.index')
            ->with('success', ucfirst(str_replace('_', ' ', $validated['type'])) . ' recorded successfully.');
    }

    /**
     * Get cash register session details
     */
    public function show(CashRegisterSession $session)
    {
        $store = auth()->user()->getEffectiveStore();

        if ($session->store_id !== $store->id) {
            abort(403);
        }

        $session->load(['user', 'transactions.order']);

        return view('store-owner.cash-register.show', compact('session'));
    }

    /**
     * Cash register reports
     */
    public function reports(Request $request)
    {
        $store = auth()->user()->getEffectiveStore();
        $period = $request->input('period', 'month');

        // Get date range
        $dates = $this->getDateRange($period, $request);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // Get sessions in date range
        $sessions = $store->cashRegisterSessions()
            ->with('user')
            ->whereBetween('opened_at', [$startDate, $endDate])
            ->orderBy('opened_at', 'desc')
            ->paginate(20);

        // Summary stats
        $stats = [
            'total_sessions' => $store->cashRegisterSessions()
                ->whereBetween('opened_at', [$startDate, $endDate])
                ->count(),
            'total_cash_sales' => $store->cashRegisterSessions()
                ->whereBetween('opened_at', [$startDate, $endDate])
                ->sum('total_cash_sales'),
            'total_card_sales' => $store->cashRegisterSessions()
                ->whereBetween('opened_at', [$startDate, $endDate])
                ->sum('total_card_sales'),
            'total_upi_sales' => $store->cashRegisterSessions()
                ->whereBetween('opened_at', [$startDate, $endDate])
                ->sum('total_upi_sales'),
            'total_difference' => $store->cashRegisterSessions()
                ->whereBetween('opened_at', [$startDate, $endDate])
                ->whereNotNull('closed_at')
                ->sum('cash_difference'),
        ];

        return view('store-owner.cash-register.reports', compact(
            'store',
            'sessions',
            'stats',
            'period',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Check if POS needs cash register session - API endpoint
     */
    public function checkSession()
    {
        $store = auth()->user()->getEffectiveStore();
        $user = auth()->user();

        $openSession = CashRegisterSession::getAnyOpenSession($store->id);
        $lastClosedSession = CashRegisterSession::getLastClosedSession($store->id);

        return response()->json([
            'has_open_session' => $openSession !== null,
            'session' => $openSession,
            'suggested_opening_cash' => $lastClosedSession ? $lastClosedSession->closing_cash : 0,
            'staff_name' => $user->name,
        ]);
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period, Request $request): array
    {
        $now = Carbon::now();

        return match ($period) {
            'today' => [
                'start' => $now->copy()->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'yesterday' => [
                'start' => $now->copy()->subDay()->startOfDay(),
                'end' => $now->copy()->subDay()->endOfDay(),
            ],
            'week' => [
                'start' => $now->copy()->startOfWeek(),
                'end' => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
            'custom' => [
                'start' => Carbon::parse($request->input('start_date', $now->copy()->startOfMonth())),
                'end' => Carbon::parse($request->input('end_date', $now))->endOfDay(),
            ],
            default => [
                'start' => $now->copy()->startOfMonth(),
                'end' => $now->copy()->endOfMonth(),
            ],
        };
    }
}
