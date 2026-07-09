<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemUnit;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    /**
     * GET /api/catalog
     * Browsable catalog with search & category filter.
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'units' => function ($q) {
            $q->where('status', 'tersedia');
        }]);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->orderBy('name')->paginate(12);

        return response()->json([
            'data' => $items->map(fn($item) => [
                'id'              => $item->id,
                'name'            => $item->name,
                'description'     => $item->description,
                'category'        => $item->category?->name,
                'available_units' => $item->units->count(),
                'units'           => $item->units->map(fn($u) => [
                    'id'            => $u->id,
                    'serial_number' => $u->serial_number,
                    'condition'     => $u->condition,
                    'status'        => $u->status,
                ]),
            ]),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
            ],
        ]);
    }

    /**
     * GET /api/categories
     * List all categories.
     */
    public function categories()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);
        return response()->json(['data' => $categories]);
    }

    /**
     * POST /api/cart/checkout
     * Submit a loan request directly (mobile doesn't use sessions).
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'item_unit_ids'      => 'required|array|min:1',
            'item_unit_ids.*'    => 'required|exists:item_units,id',
            'loan_duration_hours'=> 'required|integer|min:1',
        ]);

        $user = Auth::user();
        if (!$user->ktm_photo) {
            return response()->json(['message' => 'Anda harus mengunggah foto KTM terlebih dahulu sebelum melakukan peminjaman.'], 422);
        }

        if ($user->status !== 'aktif') {
            return response()->json(['message' => 'Akun Anda belum aktif/menunggu verifikasi KTM oleh Admin.'], 422);
        }

        $maxItems    = (int) (Setting::where('key', 'max_items_borrowed')->first()?->value ?? 3);
        $maxDuration = (int) (Setting::where('key', 'max_loan_duration')->first()?->value ?? 8);

        if (count($request->item_unit_ids) > $maxItems) {
            return response()->json(['message' => "Maksimal {$maxItems} barang dapat dipinjam sekaligus."], 422);
        }

        if ($request->loan_duration_hours > $maxDuration) {
            return response()->json(['message' => "Durasi peminjaman maksimal {$maxDuration} jam."], 422);
        }

        // Check for active loans
        $activeLoans = Loan::where('user_id', Auth::id())
            ->whereIn('status', ['menunggu_persetujuan', 'aktif', 'terlambat'])
            ->count();

        if ($activeLoans > 0) {
            return response()->json(['message' => 'Anda masih memiliki peminjaman aktif. Selesaikan terlebih dahulu.'], 422);
        }

        // Validate all units are available
        $units = ItemUnit::whereIn('id', $request->item_unit_ids)->get();
        foreach ($units as $unit) {
            if ($unit->status !== 'tersedia') {
                return response()->json(['message' => "Unit {$unit->serial_number} sudah tidak tersedia."], 422);
            }
        }

        $loan = DB::transaction(function () use ($request) {
            $loan = Loan::create([
                'user_id'             => Auth::id(),
                'status'              => 'menunggu_persetujuan',
                'loan_duration_hours' => $request->loan_duration_hours,
            ]);

            foreach ($request->item_unit_ids as $unitId) {
                LoanItem::create([
                    'loan_id'      => $loan->id,
                    'item_unit_id' => $unitId,
                ]);
            }

            return $loan;
        });

        return response()->json([
            'message' => 'Permintaan peminjaman berhasil dikirim! Menunggu persetujuan Admin.',
            'loan_id' => $loan->id,
        ], 201);
    }
}
