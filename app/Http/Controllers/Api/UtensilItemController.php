<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UtensilInventoryRecord;
use App\Models\UtensilItem;
use App\Traits\ApiResponse;
use App\Traits\HandlesExcelImport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UtensilItemController extends Controller
{
    use ApiResponse, HandlesExcelImport;

    /**
     * GET /api/utensil-items?category=X
     * List all items for a category, ordered by sort_order then name.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
        ]);

        $items = UtensilItem::where('category', $validated['category'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return $this->success($items);
    }

    /**
     * POST /api/utensil-items
     * Add a new item to a category.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
            'name'     => 'required|string|max:255',
        ]);

        $exists = UtensilItem::where('category', $validated['category'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return $this->error("Item \"{$validated['name']}\" already exists in this category.", 422);
        }

        $maxOrder = UtensilItem::where('category', $validated['category'])->max('sort_order') ?? 0;

        $item = UtensilItem::create([
            'category'   => $validated['category'],
            'name'       => $validated['name'],
            'sort_order' => $maxOrder + 1,
        ]);

        return $this->created($item);
    }

    /**
     * PUT /api/utensil-items/{utensilItem}
     * Rename an item. Automatically updates all existing inventory records.
     */
    public function update(Request $request, UtensilItem $utensilItem): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $duplicate = UtensilItem::where('category', $utensilItem->category)
            ->where('name', $validated['name'])
            ->where('id', '!=', $utensilItem->id)
            ->exists();

        if ($duplicate) {
            return $this->error("Item \"{$validated['name']}\" already exists in this category.", 422);
        }

        $utensilItem->update(['name' => $validated['name']]);

        return $this->success($utensilItem, 'Item updated.');
    }

    /**
     * DELETE /api/utensil-items/{utensilItem}
     * Delete an item. Blocked if any inventory records exist (cascadeOnDelete handles the rest).
     * If the item truly has no records, allow deletion.
     */
    public function destroy(UtensilItem $utensilItem): JsonResponse
    {
        $hasRecords = UtensilInventoryRecord::where('utensil_item_id', $utensilItem->id)->exists();

        if ($hasRecords) {
            return $this->error(
                "Cannot delete \"{$utensilItem->name}\" — it has existing inventory records. " .
                'Remove all monthly records for this item first.',
                422
            );
        }

        $utensilItem->delete();

        return $this->success(null, 'Item deleted.');
    }

    /**
     * GET /api/utensil-items/template?category=X
     * Download a simple Excel template for bulk item import.
     */
    public function template(Request $request): StreamedResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
        ]);

        $labels = [
            'canteen_utensils'  => 'Canteen Utensils',
            'vip_dining'        => 'VIP Dining Utensils and Cutleries',
            'storage_room_fdc'  => 'Storage Room FDC',
        ];

        $spreadsheet = $this->createTemplateSpreadsheet(
            ['Item Name'],
            [['Dinner Spoon'], ['Dinner Fork']],
            "Column guide:\n" .
            "• Item Name — required. One item per row.\n" .
            "• Duplicate names (within the same category) are skipped.\n" .
            "• Items already in the database are also skipped."
        );

        $slug     = str_replace('_', '-', $validated['category']);
        $filename = "utensil-items-{$slug}-template.xlsx";

        return $this->streamXlsxDownload($spreadsheet, $filename);
    }

    /**
     * POST /api/utensil-items/import?category=X
     * Bulk-import items from an uploaded Excel / CSV file.
     * Rows with a blank Item Name or names that already exist are skipped.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file'     => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'category' => 'required|in:canteen_utensils,vip_dining,storage_room_fdc',
        ]);

        $rows     = $this->parseUploadedFile($request->file('file'));
        $category = $request->input('category');

        if (empty($rows)) {
            return $this->error('No data rows found in the file.', 422);
        }

        // Pre-load existing names for this category
        $existing = UtensilItem::where('category', $category)
            ->pluck('name')
            ->map(fn ($n) => strtolower(trim($n)))
            ->flip()
            ->toArray();

        $maxOrder = UtensilItem::where('category', $category)->max('sort_order') ?? 0;

        $imported = 0;
        $skipped  = [];
        $errors   = [];
        $created  = [];

        foreach ($rows as $index => $row) {
            $rowNum  = $index + 2;
            $rawName = trim((string) ($row['item_name'] ?? $row['name'] ?? ''));

            if ($rawName === '') {
                continue; // silently skip blank rows
            }

            if (isset($existing[strtolower($rawName)])) {
                $skipped[] = ['row' => $rowNum, 'name' => $rawName, 'message' => 'Already exists — skipped.'];
                continue;
            }

            $maxOrder++;
            UtensilItem::create([
                'category'   => $category,
                'name'       => $rawName,
                'sort_order' => $maxOrder,
            ]);

            $existing[strtolower($rawName)] = true;
            $created[] = ['row' => $rowNum, 'name' => $rawName];
            $imported++;
        }

        return $this->success(
            compact('imported', 'created', 'skipped', 'errors'),
            'Import complete.'
        );
    }
}
