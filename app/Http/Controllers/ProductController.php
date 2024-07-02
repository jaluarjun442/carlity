<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use DataTables;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Product::latest()->get();
            return DataTables::of($data)
                ->addColumn('image', function ($row) {
                    return "<img src='" . asset('uploads/product') . '/' . $row['image'] . "' style='width:100px; height:100px;' />";
                })
                ->addColumn('action', function ($row) {
                    $editUrl = route('products.edit', $row->id);
                    $deleteUrl = route('products.destroy', $row->id);

                    $btn = '<a href="' . $editUrl . '" class="edit btn btn-primary btn-sm">Edit</a> ';
                    $btn .= '<a href="' . $deleteUrl . '" class="delete btn btn-danger btn-sm" onclick="event.preventDefault(); 
                             if(confirm(\'Are you sure you want to delete this product?\')) 
                                 document.getElementById(\'delete-form-' . $row->id . '\').submit();">Delete</a>';

                    $btn .= '<form id="delete-form-' . $row->id . '" action="' . $deleteUrl . '" method="POST" style="display: none;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                             </form>';

                    return $btn;
                })
                ->addColumn('status', function ($row) {
                    return $row->status == 1 ? 'Active' : 'Inactive';
                })
                ->rawColumns(['image', 'action'])
                ->make(true);
        }

        return view('products.index');
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_text' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'image' => 'nullable|image|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $image = $request->post('name') . rand(1111111111, 9999999999) . "." . $file->getClientOriginalExtension();
            $file->move("uploads/product/", $image);
        } else {
            $image = "sample.jpg";
        }
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'link_text' => $request->link_text,
            'link_url' => $request->link_url,
            'status' => $request->status,
            'image' => $image,
        ]);
        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link_text' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'status' => 'required|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::delete('uploads/product/' . $product->image);
            }

            $file = $request->file('image');
            $image = $request->name . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move("uploads/product/", $image);

            $product->image = $image;
        }

        $product->name = $request->name;
        $product->description = $request->description;
        $product->link_text = $request->link_text;
        $product->link_url = $request->link_url;
        $product->status = $request->status;
        $product->save();

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
