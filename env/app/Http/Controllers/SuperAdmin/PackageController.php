<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use App\Models\package;

class PackageController extends Controller
{
   
 public function createPackage()
    {
        if (!auth()->user()->can('create product')) {
            abort(403, 'Unauthorized action.');
        }
 
        return view('superadmin.package.create_package');
    }


    

       public function storePackage(Request $request)
    {

      
        $request->validate([
             'packageName' => 'required|string|max:255',
             'price' => 'required|string|max:255',
             'bottle' => 'required|string|max:255',
             'cpts' => 'required|string|max:255',
              'apc' => 'required|string|max:255',
            'package_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ]);

        try {
            $input = $request->only([
                'packageName', 'description', 'bottle', 'cpts',
                'price', 'status','apc',   
            ]);

            

            // Sanitize input
            foreach ($input as $key => $value) {
                $input[$key] = Purify::clean($value);
            }

            if ($request->hasFile('package_image')) {
    $input['package_image'] = uploadImage($request->file('package_image'));
}

            package::create($input);

            return redirect()->back()->with('success', 'Package created successfully!');
        } catch (\Exception $e) {
            Log::error('Package creation failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    
    

       public function packageList()
    {
          if (!auth()->user()->can('package list')) {
            abort(403, 'Unauthorized action.');
        }
        $packageList = package::orderByDesc('id')->get();
        return view('superadmin.package.package_list', compact('packageList'));
    }


        public function editPackage(string $id)
    {
         if (!auth()->user()->can('edit package')) {
            abort(403, 'Unauthorized action.');
        }
        $package = package::findOrFail($id);
        return view('superadmin.package.edit_package', compact('package'));
    }


        public function updatePackage(Request $request, package $package)
    {
        $request->validate([
           'packageName' => 'required|string|max:255',
             'price' => 'required|string|max:255',
             'bottle' => 'required|string|max:255',
             'cpts' => 'required|string|max:255',
              'apc' => 'required|string|max:255',
            'package_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5048',
        ]);

        try {
            $data = $request->only([
             'packageName', 'description', 'bottle', 'cpts',
                'price', 'status','apc',     
            ]);

            foreach ($data as $key => $value) {
                $data[$key] = Purify::clean($value);
            }
           if ($request->hasFile('package_image')) {
    $data['package_image'] = uploadImage($request->file('package_image'));
}

            $package->update($data);

            return redirect()->back()->with('success', 'package updated successfully!');
        } catch (\Exception $e) {
            Log::error('package update failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }


    public function destroyPackage(string $id)
    {
        package::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Package deleted successfully!');
    }
}
