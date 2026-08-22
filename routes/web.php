<?php

use App\Http\Controllers\LeaveController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\Dashboard\BankController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\Dashboard\AccountsController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\DeliveryController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\PassportHolderController;
use App\Http\Controllers\Dashboard\VendorController;
use App\Http\Controllers\Dashboard\PortalManagementController;
use App\Http\Controllers\Dashboard\PayrolController;
use App\Http\Controllers\Dashboard\RouteManagementController;
use App\Http\Controllers\Dashboard\TicketPurchaseController;
use App\Http\Controllers\Dashboard\TicketSellController;
use App\Http\Controllers\Dashboard\SmsMarketingController;
use App\Http\Controllers\Dashboard\EmailMarketingController;
use App\Http\Controllers\Dashboard\WhatsappMarketingController;
use App\Http\Controllers\Dashboard\PassportHolderCategoryController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\Dashboard\TicketSaleController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\SalaryTemplateController;
use App\Http\Controllers\SaleRecordController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\AdvanceSalaryController;
use App\Http\Controllers\AttendanceSettingController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseSubCategoryController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Dashboard\BuyerController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\Dashboard\AttributeController;
use App\Http\Controllers\Dashboard\AttributeValueController;
use App\Http\Controllers\Dashboard\SliderController;
use App\Http\Controllers\Dashboard\HomepageSectionController;
use App\Http\Controllers\Dashboard\BannerController;
use App\Http\Controllers\Dashboard\PageCategoryController;
use App\Http\Controllers\Dashboard\PageController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\LandingController;
use App\Http\Controllers\Front\ShopController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\BuyerAuthController;
use App\Http\Controllers\Front\GoogleAuthController;
use App\Http\Controllers\Front\OtpAuthController;
use App\Http\Controllers\Front\BuyerDashboardController;
use App\Http\Controllers\Front\ProductReviewController;
use App\Http\Controllers\Dashboard\ProductReviewController as DashboardProductReviewController;

// Route::get('/', function () {
//      return Auth::check()
//         ? redirectToRoleDashboard(Auth::user())
//         : redirect()->route('login');
// });

// The storefront front page is a single-page sales funnel: offer, packages,
// gallery, reviews and the order form on one screen, with no login step.
Route::get('/', [LandingController::class, 'index'])->name('home');
Route::post('/order', [LandingController::class, 'place'])->name('landing.order');
Route::get('/order/received/{order}', [LandingController::class, 'thankYou'])->name('landing.thankyou');
// The previous multi-section homepage is still reachable while the funnel beds
// in; it renders from the same admin-managed homepage sections.
Route::get('/home', [HomeController::class, 'index'])->name('home.sections');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
// Live suggestions for the header search box; full results are /shop?q=…
Route::get('/search/suggestions', [\App\Http\Controllers\Front\SearchController::class, 'suggestions'])
    ->name('search.suggestions');
Route::get('/product-details/{slug}', [HomeController::class, 'product_details'])->name('product.details');
Route::get('/track-order', [\App\Http\Controllers\Front\TrackOrderController::class, 'index'])->name('track-order');
// Admin-managed content pages (About us, Refund Policy, …) — these back the footer columns.
Route::get('/page/{slug}', [\App\Http\Controllers\Front\PageController::class, 'show'])->name('page.show');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Front\NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
// Cart routes (frontend)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');

Route::middleware('buyer.guest')->prefix('buyer')->name('buyer.')->group(function () {
    Route::get('/login', [BuyerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [BuyerAuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [BuyerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [BuyerAuthController::class, 'register'])->name('register.store');

    // Sign in with a one-time code sent over SMS.
    Route::post('/login/otp/send', [OtpAuthController::class, 'send'])->name('login.otp.send');
    Route::post('/login/otp/verify', [OtpAuthController::class, 'verify'])->name('login.otp.verify');
});

// Google OAuth. Outside the buyer.guest group because Google redirects back
// here after the session has already been touched.
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Buyer login/register landing page
Route::middleware('buyer.guest')->get('/login', [BuyerAuthController::class, 'showLogin'])->name('login');

// Guest checkout: no auth. Placing an order creates the buyer account when the
// shopper does not have one yet (see CheckoutController::resolveBuyer).
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'place'])->name('checkout.place');
Route::post('/checkout/coupon', [\App\Http\Controllers\Front\CouponController::class, 'apply'])->name('checkout.coupon.apply');
Route::delete('/checkout/coupon', [\App\Http\Controllers\Front\CouponController::class, 'remove'])->name('checkout.coupon.remove');
Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation');

Route::middleware('buyer.auth')->group(function () {
    Route::post('/products/{product:slug}/reviews', [ProductReviewController::class, 'store'])->name('reviews.store');
    Route::post('/wishlist/toggle', [\App\Http\Controllers\Front\WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::prefix('buyer')->name('buyer.')->group(function () {
        Route::post('/logout', [BuyerAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [BuyerDashboardController::class, 'index'])->name('dashboard');
        Route::get('/orders', [BuyerDashboardController::class, 'orders'])->name('orders');
        Route::get('/orders/{order}', [BuyerDashboardController::class, 'order'])->name('orders.show');
        Route::get('/profile', [BuyerDashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [BuyerDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('/invoices', [BuyerDashboardController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{invoice}', [BuyerDashboardController::class, 'invoice'])->name('invoices.show');
        Route::post('/orders/{order}/upload-slip', [BuyerDashboardController::class, 'uploadSlip'])->name('orders.upload-slip');
        Route::post('/orders/{order}/reorder', [BuyerDashboardController::class, 'reorder'])->name('orders.reorder');
        Route::get('/orders/{order}/tracking', [BuyerDashboardController::class, 'trackOrder'])->name('orders.tracking');
        Route::get('/wishlist', [\App\Http\Controllers\Front\WishlistController::class, 'index'])->name('wishlist');

        Route::get('/coupons', [BuyerDashboardController::class, 'coupons'])->name('coupons');
        Route::get('/address', [BuyerDashboardController::class, 'address'])->name('address');
        Route::put('/address', [BuyerDashboardController::class, 'updateAddress'])->name('address.update');
        Route::get('/payments', [BuyerDashboardController::class, 'payments'])->name('payments');
        Route::get('/reviews', [BuyerDashboardController::class, 'reviews'])->name('reviews');
        Route::get('/password', [BuyerDashboardController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [BuyerDashboardController::class, 'updatePassword'])->name('password.update');
    });
});


// Admin login routes
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Super Admin routes
Route::middleware(['auth', 'role:super admin|admin|vendor|agent']) // or a custom role middleware
->prefix('{role}')
->name('role.')
->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('change-password', [LoginController::class, 'updatePassword'])->name('change.password');
    // No show route: there is no single-user page, and the index already carries
    // everything about a user that is worth reading.
    Route::resource('user', UserController::class)->except('show');
    Route::resource('passport-holder', PassportHolderController::class);
    Route::resource('passport-holder-category', PassportHolderCategoryController::class);
    Route::resource('vendor', VendorController::class);
    Route::put('vendor/logs/{log}/restore', [VendorController::class, 'restore'])->name('vendor.restore');
    Route::put('vendor/{id}/toggle-status', [VendorController::class, 'toggleStatus'])->name('vendor.toggleStatus');
    Route::resource('portal-management', PortalManagementController::class);
    Route::resource('banks', BankController::class);
    Route::resource('payrol', PayrolController::class);
    Route::resource('route', RouteManagementController::class);
    Route::resource('sms-marketing', SmsMarketingController::class);
    Route::resource('email-marketing', EmailMarketingController::class);
    Route::resource('whatsapp-marketing', WhatsappMarketingController::class);
    Route::resource('countries', CountryController::class)->except('show');        
    Route::resource('states', StateController::class)->except('show');
    Route::resource('airport', AirportController::class)->except('show');
    Route::get('get-states-by-country', [StateController::class, 'getStatesByCountry'])->name('get-states-by-country');       
    Route::resource('company-settings', CompanyController::class)->except('show');
    Route::resource('departments', DepartmentController::class)->except('show');
    Route::resource('designations', DesignationController::class)->except('show');
    Route::resource('shifts', ShiftController::class)->except('show');
    Route::resource('holidays', HolidayController::class)->except('show');
    Route::resource('attendances', AttendanceController::class)->except('show');
    Route::resource('leaves', LeaveController::class)->except('show');
    Route::resource('leave-types', LeaveTypeController::class)->except('show');
    Route::resource('attendence-settings', AttendanceSettingController::class)->except('show');
    Route::resource('salary-templates', SalaryTemplateController::class)->except('show');
    Route::resource('sales-records', SaleRecordController::class)->except('show');
    Route::resource('employee-salaries', EmployeeSalaryController::class)->except('show');
    Route::get('get-employee-salary', [EmployeeSalaryController::class, 'getEmployeeSalary'])->name('get-employee-salary');
    Route::resource('loans', LoanController::class)->except('show');
    Route::resource('payments', PaymentController::class)->except('show');
    Route::resource('payslips', PayslipController::class)->except('show');    
    Route::resource('advance-salaries', AdvanceSalaryController::class)->except('show');
    Route::resource('commissions', CommissionController::class)->except('show');
    Route::resource('expense-categories', ExpenseCategoryController::class)->except('show');
    Route::resource('expense-subcategories', ExpenseSubCategoryController::class)->except('show');
    Route::get('get-expense-sub-category', [ExpenseSubCategoryController::class, 'getExpenseSubCategory'])->name('get-expense-sub-category');
    Route::resource('expenses', ExpenseController::class)->except('show');
    Route::resource('units', UnitController::class)->except('show');
    Route::resource('brands', BrandController::class)->except('show');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('sub-categories', SubCategoryController::class)->except('show');
    Route::get('get-sub-category', [SubCategoryController::class, 'getSubCategory'])->name('get-sub-category');
    Route::resource('branches', BranchController::class)->except('show');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('buyers', BuyerController::class)->except('show');
    // Declared before the resource so "attributes/reorder" is not swallowed by
    // the {attribute} wildcard.
    Route::post('attributes/reorder', [AttributeController::class, 'reorder'])->name('attributes.reorder');
    Route::resource('attributes', AttributeController::class)->except('show');
    Route::get('attributes/{attribute}/values', [AttributeValueController::class, 'index'])->name('attributes.values.index');
    Route::post('attribute-values', [AttributeValueController::class, 'store'])->name('attribute-values.store');
    Route::post('attribute-values/reorder', [AttributeValueController::class, 'reorder'])->name('attribute-values.reorder');
    Route::put('attribute-values/{attributeValue}', [AttributeValueController::class, 'update'])->name('attribute-values.update');
    Route::delete('attribute-values/{attributeValue}', [AttributeValueController::class, 'destroy'])->name('attribute-values.destroy');
    Route::resource('sliders', SliderController::class)->except('show');
    Route::post('homepage-sections/reorder', [HomepageSectionController::class, 'reorder'])->name('homepage-sections.reorder');
    Route::resource('homepage-sections', HomepageSectionController::class)->except('show');
    Route::resource('banners', BannerController::class)->except('show');
    Route::resource('combo-deals', \App\Http\Controllers\Dashboard\ComboDealController::class)->except('show');
    Route::resource('coupons', \App\Http\Controllers\Dashboard\CouponController::class)->except(['show', 'create', 'edit']);
    Route::get('newsletter-subscribers', [\App\Http\Controllers\Dashboard\NewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index');
    Route::delete('newsletter-subscribers/{id}', [\App\Http\Controllers\Dashboard\NewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy');
    Route::resource('page-categories', PageCategoryController::class)->except('show');
    // Before the resource so "pages/reorder" is not caught by the {page} wildcard.
    Route::post('pages/reorder', [PageController::class, 'reorder'])->name('pages.reorder');
    Route::resource('pages', PageController::class)->except('show');
    // Delivery charge per district — what the funnel adds to every order. No
    // destroy route: orders reference a district, so an area the shop no longer
    // serves is switched off instead of deleted.
    Route::get('delivery-areas', [\App\Http\Controllers\Dashboard\DeliveryAreaController::class, 'index'])->name('delivery-areas.index');
    Route::post('delivery-areas', [\App\Http\Controllers\Dashboard\DeliveryAreaController::class, 'store'])->name('delivery-areas.store');
    Route::put('delivery-areas', [\App\Http\Controllers\Dashboard\DeliveryAreaController::class, 'update'])->name('delivery-areas.update');
    Route::get('website-settings', [SettingController::class, 'index'])->name('website-settings');
    Route::post('website-settings', [SettingController::class, 'store'])->name('website-settings.store');
    
    //buyer contacts load for edit
    Route::get('buyers/contacts/{id}', [BuyerController::class, 'getBuyerContacts'])
    ->name('buyers.contacts.index');

    Route::get('suppliers/bank-accounts/{id}', [SupplierController::class, 'getBankAccounts'])
    ->name('suppliers.bankAccounts');

    Route::resource('products', ProductController::class)->except('show');
    Route::delete('/product/image/delete/{id}', [ProductController::class, 'deleteImage'])
    ->name('product.image.delete');
    Route::get('get-product-edit-modal', [ProductController::class, 'getProductEditModal'])->name('get-product-edit-modal');

    Route::get('product-reviews', [DashboardProductReviewController::class, 'index'])->name('product-reviews.index');
    Route::put('product-reviews/{product_review}/approve', [DashboardProductReviewController::class, 'approve'])->name('product-reviews.approve');
    Route::put('product-reviews/{product_review}/reject', [DashboardProductReviewController::class, 'reject'])->name('product-reviews.reject');
    Route::delete('product-reviews', [DashboardProductReviewController::class, 'destroy'])->name('product-reviews.destroy');
    Route::resource('sales', SaleController::class)->except('show');
    Route::resource('purchases', PurchaseController::class)->except('show');
    Route::resource('warehouses', WarehouseController::class)->except('show');
    Route::resource('stock-transfers', StockTransferController::class)->except('show');
    Route::resource('stock-movements', StockMovementController::class)->except('show');
    Route::resource('return-refs', SaleReturnController::class)->except('show');
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
    Route::post('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{order}/verify-payment', [OrderController::class, 'verifyPayment'])->name('orders.verify-payment');
    Route::post('orders/{order}/send-to-courier', [OrderController::class, 'sendToCourier'])->name('orders.send-to-courier');

    Route::get('/get-sub-category', [ProductController::class, 'getSubCategory'])
    ->name('get-sub-category');

    /*
    | Roles, Accounts and Delivery.
    |
    | The resource parameter for roles is forced to {roles}: this whole group is
    | already prefixed with {role}, and Laravel's default singular parameter for
    | a "roles" resource is also {role}, which would collide.
    */
    Route::resource('roles', RoleController::class)->parameters(['roles' => 'roles'])->except('show');

    Route::get('accounts', [AccountsController::class, 'index'])->name('accounts.index');

    Route::get('delivery', [DeliveryController::class, 'index'])->name('delivery.index');
    Route::post('delivery/{consignment}/retry', [DeliveryController::class, 'retry'])->name('delivery.retry');
    Route::post('delivery/{consignment}/sync', [DeliveryController::class, 'sync'])->name('delivery.sync');

});
