<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UtensilInventoryLog;
use App\Models\UtensilInventoryRecord;
use App\Models\UtensilItem;
use App\Traits\ApiResponse;
use App\Traits\HandlesExcelImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UtensilInventoryController extends Controller
{
    use ApiResponse, HandlesExcelImport;

    private static function previousMonth(int $year, int $month): array
    {
        return $month === 1
            ? [$year - 1, 12]
            : [$year, $month - 1];
    }

    /**
     * GET /api/utensil-inventory?category=X&year=Y&month=M
     *
     * Returns computed inventory data for every item in the category.
     *
     * Logic per item:
     *   - If a DB record exists for this month  → use its stored beginning, add, breakages.
     *   - If no DB record yet                   → beginning = previous month's total (or 0).
     *   - total = beginning + add − breakages
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
            'year'     => 'required|integer|min:2020|max:2099',
            'month'    => 'required|integer|min:1|max:12',
        ]);

        $category = $validated['category'];
        $year     = (int) $validated['year'];
        $month    = (int) $validated['month'];

        // All items for this category (ordered)
        $items = UtensilItem::where('category', $category)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        if ($items->isEmpty()) {
            return $this->success([]);
        }

        $itemIds = $items->pluck('id')->all();

        // Current-month records indexed by utensil_item_id
        $currentRecords = UtensilInventoryRecord::where('year', $year)
            ->where('month', $month)
            ->whereIn('utensil_item_id', $itemIds)
            ->get()
            ->keyBy('utensil_item_id');

        // Previous-month records for computing beginning of unsaved rows
        [$prevYear, $prevMonth] = self::previousMonth($year, $month);
        $prevRecords = UtensilInventoryRecord::where('year', $prevYear)
            ->where('month', $prevMonth)
            ->whereIn('utensil_item_id', $itemIds)
            ->get()
            ->keyBy('utensil_item_id');

        $result = [];
        foreach ($items as $item) {
            $current = $currentRecords[$item->id] ?? null;
            $prev    = $prevRecords[$item->id]    ?? null;

            if ($current !== null) {
                $beginning = (int) $current->beginning;
                $add       = (int) $current->add_qty;
                $breakages = (int) $current->breakages;
            } else {
                $beginning = $prev
                    ? (int) $prev->beginning + (int) $prev->add_qty - (int) $prev->breakages
                    : 0;
                $add       = 0;
                $breakages = 0;
            }

            $result[] = [
                'utensil_item_id' => $item->id,
                'item_name'       => $item->name,
                'beginning'       => $beginning,
                'add'             => $add,
                'breakages'       => $breakages,
                'total'           => $beginning + $add - $breakages,
                'has_prev'        => $prev !== null,
            ];
        }

        return $this->success($result);
    }

    /**
     * POST /api/utensil-inventory/save
     *
     * Upserts a monthly record for a specific utensil item.
     * The frontend always sends the full row (beginning + add + breakages).
     */
    public function save(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'utensil_item_id' => 'required|exists:utensil_items,id',
            'year'            => 'required|integer|min:2020|max:2099',
            'month'           => 'required|integer|min:1|max:12',
            'beginning'       => 'required|integer|min:0',
            'add'             => 'required|integer|min:0',
            'breakages'       => 'required|integer|min:0',
        ]);

        $record = UtensilInventoryRecord::updateOrCreate(
            [
                'utensil_item_id' => (int) $validated['utensil_item_id'],
                'year'            => (int) $validated['year'],
                'month'           => (int) $validated['month'],
            ],
            [
                'beginning' => (int) $validated['beginning'],
                'add_qty'   => (int) $validated['add'],
                'breakages' => (int) $validated['breakages'],
            ]
        );

        UtensilInventoryLog::create([
            'utensil_item_id' => $record->utensil_item_id,
            'year'            => $record->year,
            'month'           => $record->month,
            'add_qty'         => (int) $validated['add'],
            'breakages'       => (int) $validated['breakages'],
            'modified_by'     => auth()->id(),
        ]);

        return $this->success([
            'utensil_item_id' => $record->utensil_item_id,
            'beginning'       => $record->beginning,
            'add'             => $record->add_qty,
            'breakages'       => $record->breakages,
            'total'           => $record->beginning + $record->add_qty - $record->breakages,
        ], 'Saved.');
    }

    /**
     * GET /api/utensil-inventory/template?category=X&year=Y&month=M
     *
     * Downloads an Excel file pre-filled with all items for the category and
     * their current data for the selected month. The user fills in quantities
     * and re-imports the file via the import endpoint.
     */
    public function template(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
            'year'     => 'required|integer|min:2020|max:2099',
            'month'    => 'required|integer|min:1|max:12',
        ]);

        $category = $validated['category'];
        $year     = (int) $validated['year'];
        $month    = (int) $validated['month'];

        $items = UtensilItem::where('category', $category)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $itemIds = $items->pluck('id')->all();

        $currentRecords = UtensilInventoryRecord::where('year', $year)
            ->where('month', $month)
            ->whereIn('utensil_item_id', $itemIds)
            ->get()
            ->keyBy('utensil_item_id');

        [$prevYear, $prevMonth] = self::previousMonth($year, $month);
        $prevRecords = UtensilInventoryRecord::where('year', $prevYear)
            ->where('month', $prevMonth)
            ->whereIn('utensil_item_id', $itemIds)
            ->get()
            ->keyBy('utensil_item_id');

        $headers  = ['Item Name', 'Beginning', 'Add', 'Breakages', 'Ending'];
        $dataRows = [];

        foreach ($items as $item) {
            $current = $currentRecords[$item->id] ?? null;
            $prev    = $prevRecords[$item->id]    ?? null;

            if ($current !== null) {
                $beginning = (int) $current->beginning;
                $add       = (int) $current->add_qty;
                $breakages = (int) $current->breakages;
            } else {
                $beginning = $prev
                    ? (int) $prev->beginning + (int) $prev->add_qty - (int) $prev->breakages
                    : 0;
                $add       = 0;
                $breakages = 0;
            }

            $ending = $beginning + $add - $breakages;

            $dataRows[] = [$item->name, $beginning, $add, $breakages, $ending];
        }

        $monthLabel = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $note = "Category: {$category}\nMonth: {$monthLabel}\n\n" .
                "• Do NOT rename or remove the 'Item Name' column — it is used to match rows on import.\n" .
                "• Beginning: starting quantity (pre-filled from last month's total where available).\n" .
                "• Add: quantity added this month.\n" .
                "• Breakages: quantity broken or lost this month.\n" .
                "• Ending: auto-calculated (Beginning + Add − Breakages). Do not edit this column.";

        $spreadsheet = $this->createTemplateSpreadsheet($headers, $dataRows, $note);
        $slug        = str_replace('_', '-', $category);
        $monthNum    = str_pad((string) $month, 2, '0', STR_PAD_LEFT);
        $filename    = "inventory-{$slug}-{$year}-{$monthNum}.xlsx";

        return $this->streamXlsxDownload($spreadsheet, $filename);
    }

    /**
     * POST /api/utensil-inventory/import
     *
     * Reads an uploaded Excel/CSV file (same format as the template) and
     * upserts inventory records for the given category/year/month.
     * Rows whose Item Name does not match an existing item are skipped.
     */
    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file'     => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
            'year'     => 'required|integer|min:2020|max:2099',
            'month'    => 'required|integer|min:1|max:12',
        ]);

        $rows     = $this->parseUploadedFile($request->file('file'));
        $category = $validated['category'];
        $year     = (int) $validated['year'];
        $month    = (int) $validated['month'];

        if (empty($rows)) {
            return $this->error('No data rows found in the file.', 422);
        }

        // Build case-insensitive name → item ID map for this category
        $itemMap = UtensilItem::where('category', $category)
            ->get()
            ->mapWithKeys(fn ($i) => [strtolower(trim($i->name)) => $i->id]);

        $imported = 0;
        $skipped  = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $name   = trim((string) ($row['item_name'] ?? $row['name'] ?? ''));

            if ($name === '') {
                continue; // silently skip blank rows
            }

            $itemId = $itemMap[strtolower($name)] ?? null;
            if ($itemId === null) {
                $skipped[] = ['row' => $rowNum, 'name' => $name, 'message' => 'Item not found — skipped.'];
                continue;
            }

            $beginning = max(0, (int) ($row['beginning'] ?? 0));
            $add       = max(0, (int) ($row['add']       ?? 0));
            $breakages = max(0, (int) ($row['breakages'] ?? 0));

            UtensilInventoryRecord::updateOrCreate(
                ['utensil_item_id' => $itemId, 'year' => $year, 'month' => $month],
                ['beginning' => $beginning, 'add_qty' => $add, 'breakages' => $breakages]
            );

            UtensilInventoryLog::create([
                'utensil_item_id' => $itemId,
                'year'            => $year,
                'month'           => $month,
                'add_qty'         => $add,
                'breakages'       => $breakages,
                'modified_by'     => auth()->id(),
            ]);

            $imported++;
        }

        return $this->success(
            compact('imported', 'skipped'),
            "Import complete — {$imported} record(s) saved."
        );
    }

    /**
     * GET /api/utensil-inventory/history?category=X
     *
     * Returns the change log for a category, grouped by year-month (newest first).
     * Each entry shows: item name, qty added, breakages, who modified, and when.
     */
    public function history(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
        ]);

        $logs = UtensilInventoryLog::query()
            ->join('utensil_items', 'utensil_items.id', '=', 'utensil_inventory_logs.utensil_item_id')
            ->leftJoin('users', 'users.id', '=', 'utensil_inventory_logs.modified_by')
            ->where('utensil_items.category', $validated['category'])
            ->orderByDesc('utensil_inventory_logs.created_at')
            ->select([
                'utensil_inventory_logs.year',
                'utensil_inventory_logs.month',
                'utensil_inventory_logs.add_qty',
                'utensil_inventory_logs.breakages',
                'utensil_inventory_logs.created_at',
                'utensil_items.name as item_name',
                'users.name as modified_by',
            ])
            ->get();

        $grouped = [];
        foreach ($logs as $log) {
            $key = "{$log->year}-{$log->month}";

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'year'    => $log->year,
                    'month'   => $log->month,
                    'label'   => Carbon::createFromDate($log->year, $log->month, 1)->format('F Y'),
                    'records' => [],
                ];
            }

            $grouped[$key]['records'][] = [
                'item_name'   => $log->item_name,
                'add'         => (int) $log->add_qty,
                'breakages'   => (int) $log->breakages,
                'modified_by' => $log->modified_by ?? 'System',
                'date'        => Carbon::parse($log->created_at)->format('M d, Y g:i A'),
            ];
        }

        return $this->success(array_values($grouped));
    }
}
