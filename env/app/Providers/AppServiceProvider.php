<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
 
use App\Models\Package;
use App\Models\Bonus;
use App\Models\product;
use App\Models\Userpackage;
use App\Models\product_order;




class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
 
    }

    /**
     * Bootstrap any application services.
     */
 
    
    public function boot()
{
   View::composer('*', function ($view) {
    if (auth()->check() && auth()->user()->hasRole('superadmin')) {
        $pendingBonuses = Bonus::where('type', 'registration')
            ->where('is_paid', false)
            ->latest()
            ->take(5)
            ->get();

        $pendingBonusCount = Bonus::where('type', 'registration')
            ->where('is_paid', false)
            ->count();

        $view->with(compact('pendingBonuses', 'pendingBonusCount'));
    }
});

   View::share('packagelist', Package::all());

   
   View::share('productlist', product::all());
 
    View::composer('*', function ($view) {
        $view->with('authUser', Auth::user());
    });


View::composer('*', function ($view) {
     $user = auth()->user();
    try {
        $notice = null;

        if (Auth::check()) {
  // or auth()->id() if logged in
App\Models\userpackage::where('user_id', $user->id)
    ->where('package_order_status', 'pending')
    ->latest('id')
    ->first();

            if ($pending) {
                $notice = [
                    'package_id' => $pending->package_id,
                    'link'       => route('user.package-products', ['package_id' => $pending->package_id]),
                ];
            }
        }

        // Always share, even if null, so Blade never has an undefined var
        $view->with('pendingPackageSelection', $notice);
    } catch (\Throwable $e) {
        Log::error('Pending package banner composer error: '.$e->getMessage());
        $view->with('pendingPackageSelection', null);
    }
});

    View::composer('*', function ($view) {
        $pendingProductOrder = null;
 $user = auth()->user();
        if (Auth::check()) {
            $pendingProductOrder = product_order::where('user_id', $user->id)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->first();
        }

        $view->with('pendingProductOrder', $pendingProductOrder);
    });


        View::composer('*', function ($view) {
        $view->with('isImpersonating', session()->has('impersonate_user'));
    });

}







}
