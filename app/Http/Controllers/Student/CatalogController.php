<?php

namespace App\Http\Controllers\Student;

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
     * Display browsable catalog with search & category filter.
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'units']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->orderBy('name')->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        // Get cart from session
        $cart = session('cart', []);

        return view('student.catalog', compact('items', 'categories', 'cart'));
    }

    /**
     * Add a specific unit to the session cart.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'item_unit_id' => 'required|exists:item_units,id',
        ]);

        $unit = ItemUnit::with('item')->findOrFail($request->input('item_unit_id'));

        if ($unit->status !== 'tersedia') {
            return back()->with('error', 'Unit ini sedang tidak tersedia.');
        }

        $maxItems = (int) (Setting::where('key', 'max_items_borrowed')->first()?->value ?? 3);
        $cart = session('cart', []);

        if (count($cart) >= $maxItems) {
            return back()->with('error', "Maksimal {$maxItems} barang dapat dipinjam sekaligus.");
        }

        // Prevent duplicate
        if (in_array($unit->id, $cart)) {
            return back()->with('error', 'Unit ini sudah ada di keranjang.');
        }

        $cart[] = $unit->id;
        session(['cart' => $cart]);

        return back()->with('success', "{$unit->item->name} ({$unit->serial_number}) ditambahkan ke keranjang.");
    }

    /**
     * Remove a unit from the cart.
     */
    public function removeFromCart(Request $request)
    {
        $unitId = $request->input('item_unit_id');
        $cart = session('cart', []);
        $cart = array_values(array_diff($cart, [$unitId]));
        session(['cart' => $cart]);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    /**
     * Show cart / checkout page.
     */
    public function cart()
    {
        $cart = session('cart', []);
        $units = ItemUnit::with('item.category')->whereIn('id', $cart)->get();
        $maxDuration = (int) (Setting::where('key', 'max_loan_duration')->first()?->value ?? 8);

        return view('student.cart', compact('units', 'maxDuration'));
    }

    /**
     * Submit cart as a loan request.
     */
    public function checkout(Request $request)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong. Pilih barang terlebih dahulu.');
        }

        // Check for active/pending loans
        $activeLoans = Loan::where('user_id', Auth::id())
            ->whereIn('status', ['menunggu_persetujuan', 'aktif', 'terlambat'])
            ->count();

        if ($activeLoans > 0) {
            return back()->with('error', 'Anda masih memiliki peminjaman aktif. Selesaikan terlebih dahulu.');
        }

        // Validate all units are still available
        $units = ItemUnit::whereIn('id', $cart)->get();
        foreach ($units as $unit) {
            if ($unit->status !== 'tersedia') {
                session(['cart' => array_values(array_diff($cart, [$unit->id]))]);
                return back()->with('error', "Unit {$unit->serial_number} sudah tidak tersedia. Dihapus dari keranjang.");
            }
        }

        $maxDuration = (int) (Setting::where('key', 'max_loan_duration')->first()?->value ?? 8);

        $request->validate([
            'loan_duration_hours' => "required|integer|min:1|max:{$maxDuration}",
        ]);

        DB::transaction(function () use ($cart, $request) {
            $loan = Loan::create([
                'user_id'             => Auth::id(),
                'status'              => 'menunggu_persetujuan',
                'loan_duration_hours' => $request->input('loan_duration_hours'),
            ]);

            foreach ($cart as $unitId) {
                LoanItem::create([
                    'loan_id'      => $loan->id,
                    'item_unit_id' => $unitId,
                ]);
            }
        });

        // Clear cart
        session()->forget('cart');

        return redirect()->route('student.loans')
            ->with('success', 'Permintaan peminjaman berhasil dikirim! Menunggu persetujuan Admin.');
    }
}
