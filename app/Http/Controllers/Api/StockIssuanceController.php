<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\StockIssuance;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockIssuanceController extends Controller
{
    use ApiResponse;

    /**
     * List issuances with filters.
     * Filters: department_id (from_department), item_id, issuable_type (employee|department)
     */
    public function index(Request $request): JsonResponse
    {
        $user  = $request->user();
        $query = StockIssuance::with([
            'item.unit',
            'fromDepartment',
            'issuable',
            'issuedBy',
        ]);

        if (! $user->isSystemAdmin()) {
            $query->where('from_department_id', $user->department_id);
        } elseif ($request->filled('from_department_id')) {
            $query->where('from_department_id', $request->from_department_id);
        }

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        if ($request->filled('issuable_type')) {
            $map = [
                'employee'   => \App\Models\Employee::class,
                'department' => \App\Models\Department::class,
            ];
            if (isset($map[$request->issuable_type])) {
                $query->where('issuable_type', $map[$request->issuable_type]);
            }
        }

        return $this->success(
            $query->orderByDesc('created_at')->get()
        );
    }

    public function show(StockIssuance $stockIssuance): JsonResponse
    {
        $stockIssuance->load(['item.unit', 'fromDepartment', 'issuable', 'issuedBy']);

        return $this->success($stockIssuance);
    }

    /**
     * Issue consumable stock from a department to a person or department.
     * Also decrements the InventoryStock.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'item_id'            => 'required|exists:items,id',
            'from_department_id' => $user->isSystemAdmin() ? 'required|exists:departments,id' : 'nullable|exists:departments,id',
            'issuable_type'      => 'required|in:employee,department,others',
            'issuable_id'        => 'required_unless:issuable_type,others|nullable|integer',
            'quantity'           => 'required|numeric|min:0.01',
            'issued_at'          => 'nullable|date',
            'purpose'            => 'nullable|string|max:500',
            'notes'              => 'nullable|string',
            'issued_to_other'    => 'required_if:issuable_type,others|nullable|string|max:500',
        ]);

        if (! $user->isSystemAdmin()) {
            $validated['from_department_id'] = $user->department_id;
        }

        $item = Item::findOrFail($validated['item_id']);
        if ($item->isFixedAsset()) {
            return $this->error('Fixed assets are assigned individually, not issued as stock.', 422);
        }

        // Resolve the polymorphic model (skip for 'others')
        $modelClass   = null;
        $issuableId   = null;
        if ($validated['issuable_type'] !== 'others') {
            $modelMap = [
                'employee'   => \App\Models\Employee::class,
                'department' => \App\Models\Department::class,
            ];
            $modelClass = $modelMap[$validated['issuable_type']];
            $issuableId = $validated['issuable_id'];
            if (! $modelClass::find($issuableId)) {
                return $this->error(ucfirst($validated['issuable_type']) . ' not found.', 422);
            }
        }

        // Check sufficient stock
        $stock = InventoryStock::where('item_id', $validated['item_id'])
            ->where('department_id', $validated['from_department_id'])
            ->first();

        if (! $stock || $stock->quantity < $validated['quantity']) {
            $available = $stock ? $stock->quantity : 0;
            return $this->error("Insufficient stock. Available: {$available}", 422);
        }

        $issuance = DB::transaction(function () use ($validated, $modelClass, $issuableId, $stock, $request) {
            $stock->decrement('quantity', $validated['quantity']);

            return StockIssuance::create([
                'item_id'            => $validated['item_id'],
                'from_department_id' => $validated['from_department_id'],
                'issuable_type'      => $modelClass,
                'issuable_id'        => $issuableId,
                'quantity'           => $validated['quantity'],
                'issued_by'          => $request->user()->id,
                'issued_at'          => $validated['issued_at'] ?? now(),
                'purpose'            => $validated['purpose'] ?? null,
                'notes'              => $validated['notes'] ?? null,
                'issued_to_other'    => $validated['issued_to_other'] ?? null,
            ]);
        });

        $issuance->load(['item.unit', 'fromDepartment', 'issuable', 'issuedBy']);

        return $this->created($issuance, 'Stock issued successfully');
    }

    /**
     * Update only the issued_to_other label on an existing issuance.
     * PUT /api/stock-issuances/{stockIssuance}
     */
    public function update(Request $request, StockIssuance $stockIssuance): JsonResponse
    {
        $validated = $request->validate([
            'issued_to_other' => 'required|string|max:500',
        ]);

        $stockIssuance->update($validated);
        $stockIssuance->load(['item.unit', 'fromDepartment', 'issuable', 'issuedBy']);

        return $this->success($stockIssuance, 'Issuance updated successfully');
    }
}
