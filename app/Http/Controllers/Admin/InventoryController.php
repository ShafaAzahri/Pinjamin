<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    /**
     * Display paginated inventory with search & category filter.
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'units']);

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Category filter
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }

        $items = $query->orderBy('name')->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('admin.inventory.index', compact('items', 'categories'));
    }

    /**
     * Show form for creating a new item.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.inventory.create', compact('categories'));
    }

    /**
     * Store a newly created item with initial units.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'description'   => 'nullable|string|max:1000',
            'image'         => 'nullable|image|max:2048',
            'units'         => 'required|array|min:1',
            'units.*.serial_number' => 'required|string|max:100|distinct|unique:item_units,serial_number',
            'units.*.condition'     => 'required|in:baik,rusak',
        ], [
            'name.required'         => 'Nama barang wajib diisi.',
            'category_id.required'  => 'Kategori wajib dipilih.',
            'category_id.exists'    => 'Kategori tidak ditemukan.',
            'units.required'        => 'Minimal satu unit harus ditambahkan.',
            'units.min'             => 'Minimal satu unit harus ditambahkan.',
            'units.*.serial_number.required' => 'Nomor seri wajib diisi.',
            'units.*.serial_number.unique'   => 'Nomor seri ":input" sudah terdaftar.',
            'units.*.serial_number.distinct' => 'Nomor seri harus unik, ditemukan duplikat.',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('items', 'public');
            }

            $item = Item::create([
                'name'        => $validated['name'],
                'category_id' => $validated['category_id'],
                'description' => $validated['description'] ?? null,
                'image'       => $imagePath,
            ]);

            foreach ($validated['units'] as $unitData) {
                ItemUnit::create([
                    'item_id'       => $item->id,
                    'serial_number' => $unitData['serial_number'],
                    'condition'     => $unitData['condition'],
                    'status'        => 'tersedia',
                ]);
            }
        });

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Barang baru berhasil ditambahkan!');
    }

    /**
     * Show item detail with all units.
     */
    public function show(Item $item)
    {
        $item->load(['category', 'units']);
        return view('admin.inventory.show', compact('item'));
    }

    /**
     * Show form for editing an item.
     */
    public function edit(Item $item)
    {
        $item->load('units');
        $categories = Category::orderBy('name')->get();
        return view('admin.inventory.edit', compact('item', 'categories'));
    }

    /**
     * Update an existing item.
     */
    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'category_id'   => 'required|exists:categories,id',
            'description'   => 'nullable|string|max:1000',
            'image'         => 'nullable|image|max:2048',
        ], [
            'name.required'         => 'Nama barang wajib diisi.',
            'category_id.required'  => 'Kategori wajib dipilih.',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item->update($validated);

        return redirect()->route('admin.inventory.show', $item)
            ->with('success', 'Barang berhasil diperbarui!');
    }

    /**
     * Delete an item (only if no units are actively borrowed).
     */
    public function destroy(Item $item)
    {
        $activeBorrowed = $item->units()->where('status', 'dipinjam')->count();
        
        if ($activeBorrowed > 0) {
            return back()->with('error', 'Tidak bisa menghapus barang yang sedang dipinjam.');
        }

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return redirect()->route('admin.inventory.index')
            ->with('success', 'Barang berhasil dihapus!');
    }

    /**
     * Add a new unit to an existing item.
     */
    public function addUnit(Request $request, Item $item)
    {
        $validated = $request->validate([
            'serial_number' => 'required|string|max:100|unique:item_units,serial_number',
            'condition'     => 'required|in:baik,rusak',
        ], [
            'serial_number.required' => 'Nomor seri wajib diisi.',
            'serial_number.unique'   => 'Nomor seri sudah terdaftar.',
        ]);

        ItemUnit::create([
            'item_id'       => $item->id,
            'serial_number' => $validated['serial_number'],
            'condition'     => $validated['condition'],
            'status'        => 'tersedia',
        ]);

        return back()->with('success', 'Unit baru berhasil ditambahkan!');
    }

    /**
     * Update an existing unit's condition/status.
     */
    public function updateUnit(Request $request, ItemUnit $unit)
    {
        $validated = $request->validate([
            'condition' => 'required|in:baik,rusak',
            'status'    => 'required|in:tersedia,maintenance',
        ]);

        // Prevent changing status of actively borrowed units
        if ($unit->status === 'dipinjam') {
            return back()->with('error', 'Unit yang sedang dipinjam tidak bisa diubah statusnya.');
        }

        $unit->update($validated);

        return back()->with('success', 'Status unit berhasil diperbarui!');
    }

    /**
     * Delete a unit (only if not borrowed).
     */
    public function deleteUnit(ItemUnit $unit)
    {
        if ($unit->status === 'dipinjam') {
            return back()->with('error', 'Unit yang sedang dipinjam tidak bisa dihapus.');
        }

        $unit->delete();

        return back()->with('success', 'Unit berhasil dihapus!');
    }

    // ─── Category CRUD ───────────────────────────────────

    /**
     * List all categories.
     */
    public function categories()
    {
        $categories = Category::withCount('items')->orderBy('name')->get();
        return view('admin.inventory.categories', compact('categories'));
    }

    /**
     * Store a new category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique'   => 'Kategori ini sudah ada.',
        ]);

        Category::create($validated);

        return back()->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    /**
     * Update a category.
     */
    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validated);

        return back()->with('success', 'Kategori berhasil diperbarui!');
    }

    /**
     * Delete a category (only if no items belong to it).
     */
    public function deleteCategory(Category $category)
    {
        if ($category->items()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus kategori yang memiliki barang.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}
