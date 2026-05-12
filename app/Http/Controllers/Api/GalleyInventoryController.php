<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\GalleyInventoryRemark;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\StockIssuance;
use App\Models\StockReceival;
use App\Models\Supplier;
use App\Traits\ApiResponse;
use App\Traits\HandlesExcelImport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GalleyInventoryController extends Controller
{
    use ApiResponse, HandlesExcelImport;

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

    // ── Excel template ────────────────────────────────────────────────────────

    /**
     * Download a pre-filled import template.
     * Items are pre-listed; user fills in Quantity, Unit Cost, Supplier, Date Received, Notes.
     * No Department or Delivery Receipt No columns.
     *
     * GET /api/galley-inventory/template[?only_category=...&exclude_categories=...]
     */
    public function template(Request $request): StreamedResponse
    {
        $user   = $request->user();
        $deptId = $this->resolveDepartmentId($user, $request);

        if (! $deptId) {
            abort(422, 'Could not resolve department. Pass department_id or ensure the GOD department exists.');
        }

        // Build item query (same category filters as index)
        $itemQuery = Item::with(['unit', 'category'])
            ->where('department_id', $deptId)
            ->where('item_type', 'consumable')
            ->orderBy('name');

        if ($request->filled('only_category')) {
            $itemQuery->whereHas('category', fn ($q) =>
                $q->where('name', $request->input('only_category'))
            );
        }

        if ($request->filled('exclude_categories')) {
            $exclude = array_map('trim', explode(',', $request->input('exclude_categories')));
            $itemQuery->whereHas('category', fn ($q) =>
                $q->whereNotIn('name', $exclude)
            );
        }

        $items     = $itemQuery->get();
        $suppliers = Supplier::orderBy('name')->pluck('name')->toArray();

        // Columns: Item Name (pre-filled/locked), Quantity, Supplier, Date Received, Notes
        $headers = ['Item Name', 'Quantity', 'Supplier', 'Date Received', 'Notes'];

        // Pre-fill one row per item; user fills the other columns
        $sampleRows = $items->map(fn ($item) => [
            $item->name, null, null, date('Y-m-d'), null,
        ])->toArray();

        $spreadsheet = $this->createTemplateSpreadsheet(
            $headers,
            $sampleRows,
            "Column guide:\n" .
            "• Item Name     — pre-filled; do not edit.\n" .
            "• Quantity      — required (leave blank to skip an item).\n" .
            "• Supplier      — optional; must match an existing supplier name.\n" .
            "• Date Received — optional; format YYYY-MM-DD. Defaults to today if blank.\n" .
            "• Notes         — optional; free text."
        );

        $dataSheet = $spreadsheet->getActiveSheet();
        $lastRow   = count($items) + 1; // +1 for header row

        if ($lastRow > 1) {
            // Style item name column: grey background to signal it is pre-filled
            $dataSheet->getStyle("A2:A{$lastRow}")->applyFromArray([
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
                'font' => ['bold' => true, 'color' => ['rgb' => '1E293B']],
            ]);

            // Sheet protection: lock item name column, unlock everything else
            $dataSheet->getProtection()->setSheet(true);

            // Unlock all user-editable cells (B2:E through end of data + buffer)
            $bufferRow = max($lastRow, 1000);
            $dataSheet->getStyle("B2:E{$bufferRow}")
                ->getProtection()
                ->setLocked(Protection::PROTECTION_UNPROTECTED);
        }

        // Hidden _ref sheet for supplier dropdown
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('_ref');
        foreach ($suppliers as $i => $name) {
            $refSheet->setCellValue('A' . ($i + 1), $name);
        }
        $refSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
        $spreadsheet->setActiveSheetIndex(0);

        // Supplier dropdown on column C
        if (! empty($suppliers)) {
            $suppCount = count($suppliers);
            $v = $dataSheet->getCell('C2')->getDataValidation();
            $v->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $v->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $v->setAllowBlank(true);
            $v->setShowDropDown(true);
            $v->setShowInputMessage(false);
            $v->setShowErrorMessage(false);
            $v->setFormula1("'_ref'!\$A\$1:\$A\${$suppCount}");
            $v->setSqref('C2:C1000');
        }

        // Filename based on category filter
        if ($request->filled('only_category')) {
            $slug     = strtolower(str_replace(' ', '_', $request->input('only_category')));
            $filename = "{$slug}_inventory_template.xlsx";
        } else {
            $filename = 'galley_inventory_template.xlsx';
        }

        return $this->streamXlsxDownload($spreadsheet, $filename);
    }

    // ── Bulk import ───────────────────────────────────────────────────────────

    /**
     * Import stock receivals from the pre-filled galley template.
     * Columns expected: Item Name, Quantity, Unit Cost, Supplier, Date Received, Notes.
     * Department is auto-resolved (no column in template).
     * Rows with blank Quantity are silently skipped.
     *
     * POST /api/galley-inventory/import
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:5120']);

        $rows = $this->parseUploadedFile($request->file('file'));

        if (empty($rows)) {
            return $this->error('No data rows found in the file.', 422);
        }

        $user   = $request->user();
        $deptId = $this->resolveDepartmentId($user, $request);

        if (! $deptId) {
            return $this->error('Could not resolve department. Pass department_id or ensure the GOD department exists.', 422);
        }

        // Pre-load reference maps scoped to this department
        $itemMap = Item::where('item_type', 'consumable')
            ->where('department_id', $deptId)
            ->get()
            ->keyBy(fn ($i) => strtolower(trim($i->name)));

        $suppMap = Supplier::all()->keyBy(fn ($s) => strtolower(trim($s->name)));

        $created  = [];
        $skipped  = [];
        $errors   = [];
        $imported = 0;

        foreach ($rows as $index => $row) {
            $rowNum   = $index + 2;
            $itemName = trim((string) ($row['item_name'] ?? $row['item'] ?? ''));
            $qtyRaw   = trim((string) ($row['quantity'] ?? ''));
            $suppName = trim((string) ($row['supplier'] ?? ''));
            $dateRaw  = trim((string) ($row['date_received'] ?? ''));
            $notes    = trim((string) ($row['notes'] ?? ''));

            // Skip rows with no item name
            if ($itemName === '') {
                continue;
            }

            // Skip rows where user left Quantity blank or set it to 0
            if ($qtyRaw === '' || (is_numeric($qtyRaw) && (float) $qtyRaw === 0.0)) {
                $skipped[] = ['row' => $rowNum, 'name' => $itemName, 'message' => 'No quantity — skipped.'];
                continue;
            }

            // Validate quantity (must be a positive number)
            if (! is_numeric($qtyRaw) || (float) $qtyRaw < 0) {
                $errors[] = ['row' => $rowNum, 'name' => $itemName, 'message' => 'Quantity must be a positive number.'];
                continue;
            }

            // Resolve item
            $item = $itemMap[strtolower($itemName)] ?? null;
            if (! $item) {
                $errors[] = ['row' => $rowNum, 'name' => $itemName, 'message' => "Item \"{$itemName}\" not found or is not a consumable item in this department."];
                continue;
            }

            // Resolve supplier (optional)
            $suppId = null;
            if ($suppName !== '') {
                $supp = $suppMap[strtolower($suppName)] ?? null;
                if (! $supp) {
                    $errors[] = ['row' => $rowNum, 'name' => $itemName, 'message' => "Supplier \"{$suppName}\" not found."];
                    continue;
                }
                $suppId = $supp->id;
            }

            // Validate date
            $receivedAt = null;
            if ($dateRaw !== '') {
                $parsed = date_create($dateRaw);
                if (! $parsed) {
                    $errors[] = ['row' => $rowNum, 'name' => $itemName, 'message' => "Date Received \"{$dateRaw}\" is not a valid date."];
                    continue;
                }
                $receivedAt = $parsed->format('Y-m-d');
            }

            // Persist
            try {
                DB::transaction(function () use ($item, $deptId, $qtyRaw, $suppId, $receivedAt, $notes, $user) {
                    $stock = InventoryStock::firstOrCreate(
                        ['item_id' => $item->id, 'department_id' => $deptId],
                        ['quantity' => 0]
                    );
                    $stock->increment('quantity', (float) $qtyRaw);

                    StockReceival::create([
                        'item_id'             => $item->id,
                        'department_id'       => $deptId,
                        'quantity'            => (float) $qtyRaw,
                        'supplier_id'         => $suppId,
                        'delivery_receipt_no' => null,
                        'received_by'         => $user->id,
                        'received_at'         => $receivedAt ?? now()->format('Y-m-d'),
                        'notes'               => $notes ?: null,
                    ]);
                });

                $created[] = ['row' => $rowNum, 'name' => $itemName];
                $imported++;
            } catch (\Throwable $e) {
                $errors[] = ['row' => $rowNum, 'name' => $itemName, 'message' => 'Failed to save: ' . $e->getMessage()];
            }
        }

        return $this->success(compact('imported', 'created', 'skipped', 'errors'), 'Import complete.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Resolve the target department ID.
     * - Non-admin users: always their own department.
     * - Admin users: use the provided department_id param, or fall back to the GOD department.
     */
    private function resolveDepartmentId(\App\Models\User $user, Request $request): ?int
    {
        if (! $user->isSystemAdmin()) {
            return (int) $user->department_id ?: null;
        }

        if ($request->filled('department_id')) {
            return (int) $request->input('department_id');
        }

        $god = Department::where('name', 'GOD')->first();

        return $god?->id;
    }
}
