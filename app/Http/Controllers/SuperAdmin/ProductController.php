<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use App\Models\product;

class ProductController extends Controller
{
   public function createProduct()
    {
        if (!auth()->user()->can('create product')) {
            abort(403, 'Unauthorized action.');
        }
 
        return view('superadmin.product.create_product');
    }


       public function storeProduct(Request $request)
    {
        $request->validate([
             'productName' => 'required|string|max:255',
             'price' => 'required|string|max:255',
           
             
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ]);

        try {
            $input = $request->only([
                'productName', 'description', 'apc', 'cpts',
                'price', 'status',   
            ]);


            // Sanitize input
            foreach ($input as $key => $value) {
                $input[$key] = Purify::clean($value);
            }

            if ($request->hasFile('product_image')) {
    $input['product_image'] = uploadProduct($request->file('product_image'));
}

            product::create($input);

            return redirect()->back()->with('success', 'Productt created successfully!');
        } catch (\Exception $e) {
            Log::error('product creation failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }


       public function productList()
    {
          if (!auth()->user()->can('product list')) {
            abort(403, 'Unauthorized action.');
        }
        $productList = product::orderByDesc('id')->get();
        return view('superadmin.product.product_list', compact('productList'));
    }

      public function editProduct(string $id)
    {
         if (!auth()->user()->can('edit product')) {
            abort(403, 'Unauthorized action.');
        }
        $product = product::findOrFail($id);
        return view('superadmin.product.edit_product', compact('product'));
    }



        public function updateProduct(Request $request, product $product)
    {
        $request->validate([
             'productName', 'description', 'bottle', 'cpts',
            'price', 'status',   
        ]);

        try {
            $data = $request->only([
             'productName', 'description', 'apc', 'cpts',
                'price', 'status',      
            ]);

            foreach ($data as $key => $value) {
                $data[$key] = Purify::clean($value);
            }
          if ($request->hasFile('product_image')) {
    $data['product_image'] = uploadProduct($request->file('product_image'));
}

            $product->update($data);

            return redirect()->back()->with('success', 'Product updated successfully!');
        } catch (\Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }


    public function destroyProduct(string $id)
    {
        product::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Product deleted successfully!');
    }



}
