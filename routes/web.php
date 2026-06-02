<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\Api\UplineController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\UserPackageController;
use App\Http\Controllers\User\UserproductCOntroller;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\SuperAdmin\SuperAdminDashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SuperAdmin\PermissionController;
use App\Http\Controllers\SuperAdmin\RolePermissionController;
use App\Http\Controllers\SuperAdmin\UserPermissionController;
use App\Http\Controllers\SuperAdmin\SettingsController;
use App\Http\Controllers\SuperAdmin\TransactionController;
use App\Http\Controllers\SuperAdmin\ProductController;
use App\Http\Controllers\SuperAdmin\PackageController;
use App\Http\Controllers\SuperAdmin\ProductOrderController;
use Lab404\Impersonate\Controllers\ImpersonateController;
use App\Http\Controllers\SuperAdmin\IncentiveSettingController;
use App\Http\Controllers\SuperAdmin\MigrationController;
use App\Http\Controllers\RegistrationAdminController;
use App\Http\Controllers\RegistrationUserController;
use App\Http\Controllers\User\memberPackageController;
use App\Http\Controllers\User\memberReorderController;



Route::impersonate();

Route::group(['middleware' => ['auth']], function () {
    Route::get('/impersonate/take/{id}', [ImpersonateController::class, 'take'])->name('impersonate.take');
    Route::get('/impersonate/leave', [ImpersonateController::class, 'leave'])->name('impersonate.leave');
});

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware(['auth'])->group(function () {
    Route::impersonate();
});

 

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showStep1'])->name('register.step1');
Route::get('/register/step/{step}', [RegisterController::class, 'showStep'])->name('register.step');
Route::post('/register/step/{step}', [RegisterController::class, 'postStep'])->name('register.post');
 

Route::post('/check-upline', [RegisterController::class, 'checkUpline']);

Route::post('/check-sponsor', [RegisterController::class, 'checkSponsor']);



Route::redirect('/register', '/register/step/1')->name('register');

Route::get('/register/step/{step}/{referrer?}', [RegisterController::class, 'showStep'])->name('register.step');




 
// USER ROUTE
Route::middleware(['auth', 'role:user', 'check.pending.product.order'])->group(function () {

Route::get('/user/downline/step/{step}', [RegistrationUserController::class, 'showUserStep'])->name('user.downline.step');
Route::post('/user/downline/step/{step}', [RegistrationUserController::class, 'postUserStep'])->name('user.downline.post');
Route::get('/user/userreg_paymentpage', [RegistrationUserController::class, 'showPaymentPage'])->name('user.userreg_paymentpage');

Route::get('/user/userreg_pay/{id}', [RegistrationUserController::class, 'payPendingUser'])->name('user.userreg_pay');


Route::delete('/user/delete/{id}', [RegistrationUserController::class, 'deletePendingUser'])->name('user.delete');
Route::post('/user/pay/{id}/wallet', [RegistrationUserController::class, 'payWithWallet'])->name('user.pay.wallet');

// Online payment (Paystack)
Route::post('/user/pay/{id}/online', [RegistrationUserController::class, 'payWithOnline'])
    ->name('user.pay.online');


    

 Route::get('/verify-user-paystack/{id}', [RegistrationUserController::class, 'verifyuserPaystackPayment'])
    ->name('user.verifyUserPaystack');





Route::get('/user/dashboard', [UserController::class, 'index'])->name('user.dashboard');
Route::get('/paymentPage', [UserController::class, 'showPaymentPage'])->name('user.paymentPage');
Route::post('/payment/bank', [UserController::class, 'processBankPayment'])->name('payment.bank');
Route::post('/payment/flutterwave', [UserController::class, 'initiateFlutterwavePayment'])->name('payment.flutterwave');
Route::get('/payment/flutterwave/callback', [UserController::class, 'flutterwaveCallback'])->name('payment.flutterwave.callback');
Route::get('/user/package', [UserController::class, 'pacakgepage'])->name('user.package');
Route::get('/user/order_product', [UserController::class, 'productpage'])->name('user.order_product');
Route::get('/tree/expand/{id}', [UserController::class, 'expand'])
        ->name('tree.expand');

        Route::get('/tree/paginate/{id}', [UserController::class, 'paginate'])->name('tree.paginate');


Route::get('/purchase-package/{id}', [UserPackageController::class, 'showPurchaseForm'])->name('user.purchase-package');
 

  Route::post('/user/package/purchase', [UserPackageController::class, 'purchase'])->name('user.package.purchase');


Route::get('/user/topup_wallet', [UserController::class, 'topWalletPage'])->name('user.topup_wallet');

Route::post('/wallet/topup', [UserController::class, 'submitTopup'])->name('user.wallet.topup.submit');

Route::get('/user/withdrawal', [UserController::class, 'withdrawalPage'])->name('user.withdrawal');

Route::get('/user/withdrawal_page', [UserController::class, 'withdrawaluserPage'])->name('user.withdrawal_page');

//Route::post('/user/withdraw', [UserController::class, 'requestWithdrawal'])->name('user.withdraw');

Route::get('/user/myprofile', [UserController::class, 'profilePage'])->name('user.myprofile');

Route::put('updateProfile/{user}', [UserController::class, 'updateProfile'])->name('updateProfile');

 Route::put('updateBankDetails/{user}', [UserController::class, 'updateBankDetails'])->name('updateBankDetails');

Route::post('/verify-account', [RegisterController::class, 'verifyAccount'])->name('verify.account');

Route::post('/verify-useraccount', [UserController::class, 'verifyUserAccount'])->name('verify.useraccount');

Route::post('/verify-account', [UserController::class, 'verifyAccount'])->name('verify.account');

Route::post('/withdraw', [UserController::class, 'withdraw'])->name('user.withdraw');

Route::post('/manualWithdraw', [UserController::class, 'manualWithdraw'])->name('user.manualWithdraw');

Route::get('/package-products/{package_id}/products', [UserPackageController::class, 'selectProducts'])->name('user.package-products');
Route::post('/package/{package_id}/products', [UserPackageController::class, 'saveSelectedProducts'])->name('user.package.products.save');
Route::get('/paystack/callback', [UserPackageController::class, 'handlePaystackCallback'])->name('paystack.callback');

Route::post('/purchase/validate', [UserPackageController::class, 'validatePurchase'])->name('purchase.validate');

Route::post('/purchase/validate', [UserPackageController::class, 'validateBeforePaystack'])->name('purchase.validate');

Route::post('/purchase/finalize', [UserPackageController::class, 'finalizePurchase'])->name('purchase.finalize');

 
Route::get('/user/memberpackage', [MemberPackageController::class, 'showmemberpackage'])->name('user.memberpackage');
 
Route::post('/member/package/search', [memberPackageController::class, 'searchMember'])
    ->name('member.package.search');

   
Route::post('/member/package/check-username', [MemberPackageController::class, 'checkUsername'])
    ->name('member.package.checkUsername');


 
Route::get('/member/package/select/{username}', [MemberPackageController::class, 'packagePage'])
    ->name('member.package.select');

    
Route::get('/member/purchase/{userId}/{packageId}', [MemberPackageController::class, 'showMemberPurchaseForm'])
    ->name('user.member-purchase-package');

// POST route for member package purchase
Route::post('/member/package/purchase', [MemberPackageController::class, 'memberPurchase'])
    ->name('user.package.memberpurchase');




Route::get('/user/member_reorder', [memberReorderController::class, 'showmemberpackage'])->name('user.member_reorder');
 






});

Route::post('/user/update-password', [UserController::class, 'updatePassword'])->name('user.updatePassword');

Route::post('/add-to-cart/{id}', [ProductOrderController::class, 'addToCart'])->name('cart.add');
Route::get('/cart', [ProductOrderController::class, 'viewCart'])->name('cart.view');


Route::get('/cart', [ProductOrderController::class, 'viewCart'])->name('cart.view');
Route::post('/cart/add/{id}', [ProductOrderController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [ProductOrderController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove/{id}', [ProductOrderController::class, 'removeFromCart'])->name('cart.remove');
Route::delete('/cart/remove/{id}', [ProductOrderController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [ProductOrderController::class, 'clear'])->name('cart.clear');

Route::post('/checkout-submit', [ProductOrderController::class, 'checkout'])->name('checkout.submit');

Route::post('/admincheckoutapprove', [ProductOrderController::class, 'admincheckoutapprove'])->name('admincheckoutapprove.submit');


Route::get('/checkout', [ProductOrderController::class, 'checkoutPage'])->name('checkout');

Route::get('/superadmin/admincheckout', [ProductOrderController::class, 'admincheckoutPage'])->name('superadmin.admincheckout');



Route::get('/checkout/callback', [ProductOrderController::class, 'checkoutCallback'])->name('checkout.callback');

Route::get('/checkout/callback', [ProductOrderController::class, 'paystackCallback'])->name('checkout.callback');


Route::get('/user/pendingproductorder', [UserproductCOntroller::class, 'userproductPending'])->name('user.pendingproductorder');

Route::get('/payment/paystack/initiate', [UserController::class, 'initiatePaystackPayment'])
    ->name('payment.paystack.initiate');

 

    Route::get('/paystack/callback', [UserController::class, 'processPaystackRedirect'])->name('paystack.callback');



    Route::get('/payment/paystack/verify', [UserController::class, 'verifyPaystackPayment'])
    ->name('payment.paystack.verify');


    Route::get('/wallet/topup/verify', [UserController::class, 'verifyPaystackTopup'])->name('user.wallet.topup.verify');

Route::post('/wallet/withdraw', [UserController::class, 'withdraw'])->name('user.withdraw');

//MUTED USER ROUTE

Route::middleware(['auth', 'check.muted'])->group(function () {
    // All routes that muted users should not access
});




Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});



 Route::middleware(['auth','role:superadmin','permission:buy package for user'])->group(function(){
    Route::get('/superadmin/migration/migrate_user', [MigrationController::class, 'migratePage'])->name('superadmin.migration.migrate_user');
    Route::post('/superadmin/migrate-user', [MigrationController::class, 'store'])->name('superadmin.migrate.store');

    // AJAX helpers
    Route::get('/superadmin/user-search', [MigrationController::class, 'userSearch'])->name('superadmin.user.search');
    Route::get('/superadmin/user/{id}/last-package', [MigrationController::class, 'lastPackage'])->name('superadmin.user.lastPackage');
});

Route::middleware(['role:superadmin'])->group(function () {
// GET route for showing steps
Route::get('/registeruser/step/{step}', [RegistrationAdminController::class, 'showAdminStep'])
    ->name('superadmin.registeruser.step');

// POST route for submitting steps
Route::post('/registeruser/step/{step}', [RegistrationAdminController::class, 'postAdminStep'])
    ->name('superadmin.registeruser.post');

 




    Route::resource('permissions', PermissionController::class);
    Route::get('/superadmin/dashboard', [SuperAdminDashboardController::class, 'index'])->name('superadmin.dashboard');


     
 
    Route::get('/role-permissions', [RolePermissionController::class, 'index'])->name('role_permissions.index');
    Route::post('/role-permissions', [RolePermissionController::class, 'store'])->name('role_permissions.store');
     Route::get('/superadmin/user-permissions', [UserPermissionController::class, 'index'])->name('user_permissions.index');
    Route::post('/superadmin/user-permissions', [UserPermissionController::class, 'store'])->name('user_permissions.store');

 Route::get('/superadmin/bonuses/regBonus', [TransactionController::class, 'regBonuspage'])
        ->name('superadmin.bonuses.regBonus')
        ->middleware('can:view bonuses');

         Route::get('/superadmin/bonuses/macthingBonus', [TransactionController::class, 'macthingBonuspage'])
        ->name('superadmin.bonuses.macthingBonus')
        ->middleware('can:view matching bonuses');

  Route::post('superadmin/bonuses/approveMatching/{id}', [TransactionController::class, 'approveMatching'])->name('superadmin.bonuses.approveMatching')
        ->middleware('can:approve matching bonuses');


         Route::get('/superadmin/package/pendingpackageOrder', [TransactionController::class, 'pendingpackageOrder'])
        ->name('superadmin.package.pendingpackageOrder')
        ->middleware('can:view pending package order');


              Route::get('/superadmin/package/approvepackageOrder', [TransactionController::class, 'approvepackageOrder'])
        ->name('superadmin.package.approvepackageOrder')
        ->middleware('can:view approve package order');

        Route::get('/superadmin/package-order/{id}/details', [TransactionController::class, 'viewPackageOrderDetails'])
    ->name('superadmin.package.order.details')
    ->middleware('can:view approve package order');




          Route::post('superadmin/package/approveorddrpackage/{id}', [TransactionController::class, 'approveorddrpackage'])->name('superadmin.package.approveorddrpackage')
        ->middleware('can:approve package orders');


  Route::get('/superadmin/transaction/pendingwallettopup', [TransactionController::class, 'pendingwallettoupPayments'])
        ->name('superadmin.transaction.pendingwallettopup')
        ->middleware('can:view pending wallet topup');


        Route::middleware(['auth', 'can:approve wallet topup payments'])->group(function () {
    Route::post('/superadmin/transaction/{id}/approveTopup', [TransactionController::class, 'approveTopup'])
        ->name('superadmin.transaction.approveTopup');
});


  Route::get('/superadmin/transaction/pendingwithdraw', [TransactionController::class, 'pendingWithdraw'])
        ->name('superadmin.transaction.pendingwithdraw')
        ->middleware('can:view pending withdrawals');


          Route::get('/superadmin/transaction/payout', [TransactionController::class, 'payoutpage'])
        ->name('superadmin.transaction.payout')
        ->middleware('can:view payout');


        Route::post('/register/recipient', [RegisterController::class, 'generateRecipient'])
    ->name('register.recipient');


             Route::get('/superadmin/transaction/payout_history', [TransactionController::class, 'payouthistorypage'])
        ->name('superadmin.transaction.payout_history')
        ->middleware('can:view payout');

Route::post('{id}/approvePayout', [TransactionController::class, 'approvePayout'])->name('superadmin.payout.approvePayout');
Route::post('{id}/decline', [TransactionController::class, 'decline'])->name('superadmin.payout.decline'); 
Route::delete('{id}', [TransactionController::class, 'destroy'])->name('superadmin.payout.delete'); 
Route::post('bulk-approve', [TransactionController::class, 'bulkApprove'])->name('superadmin.payout.bulkApprove');

Route::post('/paystack/webhook', [TransactionController::class, 'handlePaystackWebhook']);


 
Route::post('/superadmin/transaction/{id}/approveWithdrawal', [TransactionController::class, 'approveWithdrawal'])
->name('superadmin.transaction.approveWithdrawal')
->middleware('permission:approve withdrawals');


    Route::post('bonuses/{bonus}/pay', [TransactionController::class, 'markAsPaid'])
        ->name('superadmin.bonuses.pay')
        ->middleware('can:mark bonuses as paid');


        Route::get('/superadmin/member/allMembers', [SuperAdminDashboardController::class, 'userList'])
    ->middleware(['auth', 'role:superadmin', 'permission:view users'])
    ->name('superadmin.member.allMembers');

Route::get('/superadmin/member/addMembers', [SuperAdminDashboardController::class, 'addUsers'])
    ->middleware(['auth', 'role:superadmin', 'permission:add users'])
    ->name('superadmin.member.addMembers');


    Route::get('/superadmin/member/edituserprofile/{id}', [SuperAdminDashboardController::class, 'editUsersprofile'])
    ->middleware(['auth', 'role:superadmin', 'permission:edit users profile'])
    ->name('superadmin.member.edituserprofile');
 
    Route::put('updateuserprofile/{id}', [SuperAdminDashboardController::class, 'updateuserprofile'])->name('superadmin.member.updateuserprofile');



Route::get('superadmin/member/{id}/edit-sponsor', [SuperAdminDashboardController::class, 'editSponsor'])
    ->name('superadmin.member.editSponsor')
    ->middleware('permission:edit sponsor');

Route::put('superadmin/member/{id}/update-sponsor', [SuperAdminDashboardController::class, 'updateSponsor'])
    ->name('superadmin.member.updateSponsor')
    ->middleware('permission:edit sponsor');

Route::get('superadmin/member/validate-username', [SuperAdminDashboardController::class, 'validateUsername'])
    ->name('superadmin.member.validateUsername');





    Route::prefix('superadmin')->middleware(['auth', 'permission:view transactions history'])->group(function () {
    Route::get('/registration-history', [TransactionController::class, 'registrationPayments'])->name('superadmin.history.registration');
    Route::get('/wallet-deposit', [TransactionController::class, 'walletDeposits'])->name('superadmin.history.wallet');
    Route::get('/withdrawal-history', [TransactionController::class, 'withdrawals'])->name('superadmin.history.withdrawal-history');
});


//Route::post('registeruser/step/{step}', [SuperAdminDashboardController::class, 'adminpostStep'])->name('superadmin.registeruser.post');

 
 

    Route::get('/superadmin/package/buy_package', [SuperAdminDashboardController::class, 'buyPackage'])
    ->middleware(['auth', 'role:superadmin', 'permission:buy package for user'])
    ->name('superadmin.package.buy_package');


Route::post('/superadmin/package/buy_package', [SuperAdminDashboardController::class, 'buyPackageStore'])
    ->middleware(['auth', 'role:superadmin', 'permission:buy package for user'])
    ->name('superadmin.package.buy_package.store');


    Route::get('/superadmin/user/{id}/package-info', [SuperAdminDashboardController::class, 'getUserPackageInfo'])
    ->middleware(['auth', 'role:superadmin'])
    ->name('superadmin.user.package_info');


 
       Route::get('/superadmin/package/order_product', [SuperAdminDashboardController::class, 'adminorderPage'])
    ->middleware(['auth', 'role:superadmin', 'permission:order for user'])
    ->name('superadmin.package.order_product');

    

    Route::get('/impersonate/{id}', [SuperAdminDashboardController::class, 'impersonate'])
    ->name('superadmin.impersonate')
    ->middleware('role:superadmin');

Route::get('/stop-impersonate', [SuperAdminDashboardController::class, 'stopImpersonate'])
    ->name('superadmin.stopImpersonate')
    ->middleware('role:superadmin');




Route::get('/impersonate/login/{user}/{token}', [SuperAdminDashboardController::class, 'loginAsUser'])
    ->name('impersonate.login')
    ->middleware('signed');

Route::get('/impersonate-login/{user}/{token}', [SuperAdminDashboardController::class, 'impersonateLogin'])
    ->name('impersonate.login')
    ->middleware('signed');








Route::patch('/superadmin/member/{id}/toggle-mute', [SuperAdminDashboardController::class, 'toggleMute'])
    ->middleware(['auth', 'role:superadmin', 'permission:mute members'])
    ->name('superadmin.toggleMute');

Route::get('/superadmin/member/activeMembers', [SuperAdminDashboardController::class, 'activeuserList'])
    ->middleware(['auth', 'role:superadmin', 'permission:view active members'])
    ->name('superadmin.member.activeMembers');

Route::get('/superadmin/member/inactiveMembers', [SuperAdminDashboardController::class, 'inactiveuserList'])
    ->middleware(['auth', 'role:superadmin', 'permission:view inactive members'])
    ->name('superadmin.member.inactiveMembers');

    Route::get('/superadmin/member/pendingMembers', [SuperAdminDashboardController::class, 'pendinguserList'])
    ->middleware(['auth', 'role:superadmin', 'permission:view pending members'])
    ->name('superadmin.member.pendingMembers');




    Route::get('/superadmin/member/mutedMembers', [SuperAdminDashboardController::class, 'mutedUsersList'])
    ->middleware(['auth', 'role:superadmin', 'permission:view muted members'])
    ->name('superadmin.member.mutedMembers');


     Route::get('/superadmin/incentive_settings/incentive_list', [SuperAdminDashboardController::class, 'incentivesHistory'])
    ->middleware(['auth', 'role:superadmin'])->name('superadmin.incentive_settings.incentive_list');



Route::get('/superadmin/product/pendingproductOrder', [TransactionController::class, 'pendingproductOrder'])
        ->name('superadmin.product.pendingproductOrder')
        ->middleware('can:view pending product order');



        
Route::get('/superadmin/product/aproveproductOrder', [TransactionController::class, 'aproveproductOrder'])
        ->name('superadmin.product.aproveproductOrder')
        ->middleware('can:view approve product order');





Route::post('/superadmin/orders/{id}/approve', [TransactionController::class, 'approveBankOrder'])->name('superadmin.orders.approve');



 Route::get('incentive-settings', [IncentiveSettingController::class, 'index'])->name('incentive_settings.index');
    Route::get('incentive-settings/create', [IncentiveSettingController::class, 'create'])->name('incentive_settings.create');
    Route::post('incentive-settings', [IncentiveSettingController::class, 'store'])->name('incentive_settings.store');
    Route::get('incentive-settings/{id}/edit', [IncentiveSettingController::class, 'edit'])->name('incentive_settings.edit');
    Route::put('incentive-settings/{id}', [IncentiveSettingController::class, 'update'])->name('incentive_settings.update');
    Route::delete('incentive-settings/{id}', [IncentiveSettingController::class, 'destroy'])->name('incentive_settings.destroy');




});


// PERMISSION ROUTE
Route::middleware(['auth', 'permission:edit settings'])->group(function () {
Route::get('/superadmin/settings', [SettingsController::class, 'edit'])->name('superadmin.settings.edit');
});
Route::middleware(['auth', 'permission:update settings'])->group(function () {
Route::post('/superadmin/settings', [SettingsController::class, 'update'])->name('superadmin.settings.update');
});



// REGISTRATION PAYMENT ROUTE
Route::middleware(['auth', 'can:view pending registration payments'])->group(function () {
    Route::get('/superadmin/transaction/pendingRegistrationPayment', [TransactionController::class, 'pendingPayments'])
        ->name('superadmin.transaction.pendingRegistrationPayment');
});

Route::middleware(['auth', 'can:view approve registration payments'])->group(function () {
    Route::get('/superadmin/transaction/registrationPayment', [TransactionController::class, 'arpprovePayments'])
        ->name('superadmin.transaction.registrationPayment');
});


Route::middleware(['auth', 'can:approve registration payments'])->group(function () {
    Route::post('/superadmin/transaction/{id}/approve', [TransactionController::class, 'approvePayment'])
        ->name('superadmin.transaction.approve');
});

Route::middleware(['auth', 'can:create product'])->group(function () {
    Route::get('/superadmin/product/create_product', [ProductController::class, 'createProduct'])
        ->name('superadmin.product.create_product');

        
Route::post('/storeProduct', [ProductController::class, 'storeProduct'])
        ->name('storeProduct');

Route::get('/superadmin/product/product_list', [ProductController::class, 'productList'])
        ->name('superadmin.product.product_list');  

Route::get('/superadmin/product/edit_product/{id}', [ProductController::class, 'editProduct'])
        ->name('superadmin.product.edit_product'); 

Route::put('updateProduct/{product}', [ProductController::class, 'updateProduct'])->name('updateProduct');
    Route::delete('product/destroyProduct/{id}', [ProductController::class, 'destroyProduct'])->name('superadmin.product.destroyProduct');






});

Route::middleware(['auth', 'can:create package'])->group(function () {
    Route::get('/superadmin/package/create_package', [PackageController::class, 'createPackage'])
        ->name('superadmin.package.create_package');

Route::post('/storePackage', [PackageController::class, 'storePackage'])
        ->name('storePackage');
        
Route::get('/superadmin/package/package_list', [PackageController::class, 'packageList'])
        ->name('superadmin.package.package_list');  

});

 
 



Route::middleware(['auth', 'can:package list'])->group(function () {

Route::get('/superadmin/package/package_list', [PackageController::class, 'packageList'])
        ->name('superadmin.package.package_list');  
});


Route::middleware(['auth', 'can:edit package'])->group(function () {

Route::get('/superadmin/package/edit_package/{id}', [PackageController::class, 'editPackage'])
        ->name('superadmin.package.edit_package');  
   
    Route::put('updatePackage/{package}', [PackageController::class, 'updatePackage'])->name('updatePackage');
    Route::delete('package/destroyPackage/{id}', [PackageController::class, 'destroyPackage'])->name('superadmin.package.destroyPackage');



});




 












 


require __DIR__.'/auth.php';
