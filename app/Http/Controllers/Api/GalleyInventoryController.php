<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GalleyInventoryRemark;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\StockIssuance;
use App\Models\StockReceival;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleyInventoryController extends Controller
{
    use ApiResponse;

    /**
     * Return daily inventory summary per item for a given date.
     *
     * Logic (per item, per department):
     *   ending_inventory  = current_stock + issuances_after_date − receivals_after_date
     *   add               = sum(receivals on date)
     *   consumed          = sum(issuances on date)
     *   beginning         = ending − add + consumed   (i.e. ending of previous day)
     *   total             = beginning + add
     *
     * GET /api/galley-inventory?date=YYYY-MM-DD[&department_id=N]
     */
    public function index(Request $request): JsonResponse
    {
        $user   = $request->user();
        $date   = $request->input('date', now()->toDateString());
        $carbon = Carbon::parse($date)->startOfDay();

        $deptId = $user->isSystemAdmin()
            ? (int) $request->input('department_id', 0)
            : (int) $user->department_id;

        if (! $deptId) {
            return $this->error('department_id is required for system administrators.', 422);
        }

        // Fetch ALL consumable items for this department (even those with no stock yet)
        $itemQuery = Item::with(['unit', 'category'])
            ->where('department_id', $deptId)
            ->where('item_type', 'consumable')
            ->orderBy('name');

        // Optional: restrict to a single category (e.g. "BOTTLED WATER")
        if ($request->filled('only_category')) {
            $itemQuery->whereHas('category', fn ($q) =>
                $q->where('name', $request->input('only_category'))
            );
        }

        // Optional: exclude one or more categories (comma-separated)
        if ($request->filled('exclude_categories')) {
            $exclude = array_map('trim', explode(',', $request->input('exclude_categories')));
            $itemQuery->whereHas('category', fn ($q) =>
                $q->whereNotIn('name', $exclude)
            );
        }

        $items = $itemQuery->get();

        if ($items->isEmpty()) {
            return $this->success([]);
        }

        $itemIds = $items->pluck('id')->all();

        // Current live stock quantities (may not exist for items never received)
        $currentStocks = InventoryStock::where('department_id', $deptId)
            ->whereIn('item_id', $itemIds)
            ->pluck('quantity', 'item_id');

        // ----- grouped aggregate queries (one query each) -----

        // Issuances STRICTLY AFTER the date (to reconstruct historical ending)
        $issuancesAfter = StockIssuance::select('item_id', DB::raw('SUM(quantity) as total'))
            ->where('from_department_id', $deptId)
            ->whereIn('item_id', $itemIds)
            ->whereDate('issued_at', '>', $carbon)
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        // Receivals STRICTLY AFTER the date
        $receivalsAfter = StockReceival::select('item_id', DB::raw('SUM(quantity) as total'))
            ->where('department_id', $deptId)
            ->whereIn('item_id', $itemIds)
            ->whereDate('received_at', '>', $carbon)
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        // Receivals ON the date (add)
        $receivalsOn = StockReceival::select('item_id', DB::raw('SUM(quantity) as total'))
            ->where('department_id', $deptId)
            ->whereIn('item_id', $itemIds)
            ->whereDate('received_at', $carbon)
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        // Issuances ON the date (consumed)
        $issuancesOn = StockIssuance::select('item_id', DB::raw('SUM(quantity) as total'))
            ->where('from_department_id', $deptId)
            ->whereIn('item_id', $itemIds)
            ->whereDate('issued_at', $carbon)
            ->groupBy('item_id')
            ->pluck('total', 'item_id');

        // Remarks for the date
        $remarks = GalleyInventoryRemark::where('department_id', $deptId)
            ->whereIn('item_id', $itemIds)
            ->whereDate('date', $carbon)
            ->pluck('remarks', 'item_id');

        // ----- build result -----
        $result = [];
        foreach ($items as $item) {
            $id = $item->id;

            $currentQty   = (float) ($currentStocks[$id] ?? 0);
            $issAfter     = (float) ($issuancesAfter[$id] ?? 0);
            $recAfter     = (float) ($receivalsAfter[$id] ?? 0);
            $add          = (float) ($receivalsOn[$id] ?? 0);
            $consumed     = (float) ($issuancesOn[$id] ?? 0);

            $ending    = $currentQty + $issAfter - $recAfter;
            $beginning = $ending - $add + $consumed;
            $total     = $beginning + $add;

            $result[] = [
                'item_id'             => $id,
                'item_name'           => $item->name,
                'unit'                => $item->unit->abbreviation ?? '',
                'category'            => $item->category->name ?? '',
                'beginning_inventory' => round($beginning, 2),
                'add'                 => round($add, 2),
                'total'               => round($total, 2),
                'consumed'            => round($consumed, 2),
                'ending_inventory'    => round($ending, 2),
                'remarks'             => $remarks[$id] ?? null,
            ];
        }

        // Sort by category then item name
        usort(
            $result,
            fn($a, $b) =>
            [$a['category'], $a['item_name']] <=> [$b['category'], $b['item_name']]
        );

        return $this->success($result);
    }

    /**
     * Save or update a remark for a specific item on a given date.
     *
     * POST /api/galley-inventory/remark
     * Body: { item_id, date, remarks, department_id? }
     */
    public function saveRemark(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'item_id'       => 'required|exists:items,id',
            'date'          => 'required|date',
            'remarks'       => 'nullable|string|max:1000',
            'department_id' => $user->isSystemAdmin() ? 'required|exists:departments,id' : 'nullable|exists:departments,id',
        ]);

        $deptId = $user->isSystemAdmin()
            ? $validated['department_id']
            : $user->department_id;

        $remark = GalleyInventoryRemark::updateOrCreate(
            [
                'item_id'       => $validated['item_id'],
                'department_id' => $deptId,
                'date'          => $validated['date'],
            ],
            ['remarks' => $validated['remarks']]
        );

        return $this->success($remark, 'Remark saved.');
    }
}
