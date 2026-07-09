<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\WebApiController;
use App\Models\Category;
use Illuminate\Http\Request;

class CatalogController extends WebApiController
{
    public function index(Request $request)
    {
        $response = $this->callApi('GET', '/api/catalog', $request->all());
        $categories = Category::orderBy('name')->get();
        $cart = session('cart', []);

        // Adaptasi format response API ke Blade view format
        // Kita buat lengthaware paginator buatan atau mapping data
        $itemsData = $response['data'] ?? [];
        
        // Buat objek koleksi item agar kompatibel dengan Blade
        $items = \App\Models\Item::with(['category', 'units'])
            ->whereIn('id', collect($itemsData)->pluck('id'))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('student.catalog', compact('items', 'categories', 'cart'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'item_unit_id' => 'required|exists:item_units,id',
        ]);

        $unit = \App\Models\ItemUnit::with('item')->findOrFail($request->input('item_unit_id'));

        if ($unit->status !== 'tersedia') {
            return back()->with('error', 'Unit ini sedang tidak tersedia.');
        }

        $maxItems = (int) (\App\Models\Setting::where('key', 'max_items_borrowed')->first()?->value ?? 3);
        $cart = session('cart', []);

        if (count($cart) >= $maxItems) {
            return back()->with('error', "Maksimal {$maxItems} barang dapat dipinjam sekaligus.");
        }

        if (in_array($unit->id, $cart)) {
            return back()->with('error', 'Unit ini sudah ada di keranjang.');
        }

        $cart[] = $unit->id;
        session(['cart' => $cart]);

        return back()->with('success', "{$unit->item->name} ({$unit->serial_number}) ditambahkan ke keranjang.");
    }

    public function removeFromCart(Request $request)
    {
        $unitId = $request->input('item_unit_id');
        $cart = session('cart', []);
        $cart = array_values(array_diff($cart, [$unitId]));
        session(['cart' => $cart]);

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    public function cart()
    {
        $cart = session('cart', []);
        $units = \App\Models\ItemUnit::with('item.category')->whereIn('id', $cart)->get();
        $maxDuration = (int) (\App\Models\Setting::where('key', 'max_loan_duration')->first()?->value ?? 8);

        return view('student.cart', compact('units', 'maxDuration'));
    }

    public function checkout(Request $request)
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return back()->with('error', 'Keranjang kosong. Pilih barang terlebih dahulu.');
        }

        // Jalankan logic API dengan mock request instance secara langsung
        $apiRequest = Request::create('/api/cart/checkout', 'POST', [
            'item_unit_ids'      => array_values($cart),
            'loan_duration'      => (int) $request->input('loan_duration'),
            'loan_duration_type' => $request->input('loan_duration_type', 'hours'),
        ]);
        $apiRequest->setUserResolver(fn() => auth()->user());

        $apiController = new \App\Http\Controllers\Api\CatalogController();
        $apiResponse = $apiController->checkout($apiRequest);
        $response = json_decode($apiResponse->getContent(), true);

        if (isset($response['loan_id'])) {
            session()->forget('cart');
            return redirect()->route('student.loans')->with('success', $response['message']);
        }

        return back()->with('error', $response['message'] ?? 'Terjadi kesalahan saat memproses checkout.');
    }
}
