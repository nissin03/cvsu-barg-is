<?php

use App\Http\Middleware\AuthUser;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\AddonController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\AddonsController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\SignatureController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AdminCourseController;
use App\Http\Controllers\DataPrivacyController;
use App\Http\Controllers\AccountSetupController;
use App\Http\Controllers\AdminCollegeController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserFacilityController;
use App\Http\Controllers\FacilityReportController;
use App\Http\Controllers\FacilityReservationController;


Auth::routes(['verify' => true]);

Route::get('/', [HomeController::class, 'index'])->name('home.index');

Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])
    ->name('google-auth');

Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])
    ->name('google-auth-callback');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');

Route::get('/user/facilities', [UserFacilityController::class, 'index'])->name('user.facilities.index');
Route::get('/user/facilities/{slug}', [UserFacilityController::class, 'show'])->name('user.facilities.details');
Route::post('/facilities/calculate-price', [UserFacilityController::class, 'calculatePrice'])->name('facilities.calculatePrice');

Route::get('/about-us', [AboutController::class, 'index'])->name('about.index');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/qty/increase/{rowId}', [CartController::class, 'increase_cart_quantity']);
Route::put('/cart/qty/decrease/{rowId}', [CartController::class, 'decrease_cart_quantity']);
Route::put('/cart/update-variant/{rowId}', [CartController::class, 'updateVariant'])->name('cart.item.updateVariant');

Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.empty');



Route::get('/order-confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');

Route::get('/preorders/accept/{preOrder}', [CartController::class, 'acceptPreOrder'])->name('preorders.accept');
Route::get('/preorders/cancel/{preOrder}', [CartController::class, 'cancelPreOrder'])->name('preorders.cancel');




Route::get('/contact-us', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact-us', [HomeController::class, 'contact_store'])->name('home.contact.store');

Route::get('/search', [HomeController::class, 'search'])->name('home.search');



Route::middleware(['auth', 'verified', AuthUser::class])->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-order', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/canceled-order', [UserController::class, 'canceled_order'])
        ->name('user.canceled-orders');
    Route::post('/canceled-order/{orderId}/rebook', [UserController::class, 'rebook_canceled_order'])->name('user.order.rebook');
    Route::get('/canceled-order', [UserController::class, 'canceled_order'])
        ->name('user.canceled-orders');
    Route::post('/canceled-order/{orderId}/rebook', [UserController::class, 'rebook_canceled_order'])->name('user.order.rebook');
    Route::get('/account-order/{order_id}/details', [UserController::class, 'order_details'])->name('user.order.details');
    Route::put('/account-order/cancel-order', [UserController::class, 'order_cancel'])->name('user.order.cancel');

    Route::get('/data-privacy-notice', [DataPrivacyController::class, 'showDataPrivacyNotice'])->name('data-privacy.notice');
    Route::get('/data-privacy-notice/accept', [DataPrivacyController::class, 'accept'])->name('data-privacy.accept');


    Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
    Route::post('/place-an-order', [CartController::class, 'place_an_order'])->name('cart.place.an.order');
    Route::get('/api/slots', [CartController::class, 'getAvailableTimeSlots'])->name('slots.available');
    Route::get('/api/get-time-slots', [CartController::class, 'getAvailableDatesAndTimeSlots']);
    Route::get('/api/get-unavailable-dates', [CartController::class, 'getUnavailableDates']);

    Route::get('/user/profile', [UserController::class, 'show_profile'])->name('user.profile');
    Route::get('/user/profile/edit/{id}', [UserController::class, 'profile_edit'])->name('user.profile.edit');
    Route::put('/user/profile/update', [UserController::class, 'profile_update'])->name('user.profile.update');
    Route::get('/colleges/{college}/courses', [UserController::class, 'getCollegeCourses'])->name('colleges.courses');


    Route::get('/user/profile-image/edit', [UserController::class, 'profile_image_edit'])->name('user.profile.image.edit');
    Route::put('/user/profile-image/update', [UserController::class, 'profile_image_update'])->name('user.profile.image.update');
    Route::delete('/user/profile-image/delete', [UserController::class, 'profile_image_delete'])->name('user.profile.image.delete');

    Route::get('/order-history', [UserController::class, 'order_history'])->name('user.order.history');
    Route::get('/reservation-history', [UserController::class, 'reservation_history'])->name('user.reservation.history');

    Route::post('/reserve', [UserFacilityController::class, 'reserve'])->name('facility.reserve');
    Route::get('/user/checkout', [UserFacilityController::class, 'checkout'])->name('facility.checkout');
    Route::post('user/facilities/place-reservation', [UserFacilityController::class, 'place_reservation'])->name('user.facilities.placeReservation');
    Route::get('/user/reservations', [UserFacilityController::class, 'reservations'])->name('user.reservations');
    Route::get('/user/reservations/{payment_id}/details', [UserFacilityController::class, 'reservation_details'])->name('user.reservation_details');
    Route::get('/user/reservation-history', [UserFacilityController::class, 'reservation_history'])->name('user.reservations_history');

    Route::post('/user/reservation-details/cancel-reservation', [UserController::class, 'account_cancel_reservation'])->name('user.account_cancel_reservation');

    Route::get('/account-rentals', [UserController::class, 'account_rentals'])->name('user.account.rentals');

    // User notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'allNotifications'])->name('notifications.all');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
        Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');
        Route::post('/mark-multiple-as-read', [NotificationController::class, 'markMultipleAsRead'])->name('notifications.mark-multiple-as-read');
        Route::delete('/destroy/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::delete('/destroy-all', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
        Route::get('/count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    });
});

// Route::get('/password/set', [PasswordController::class, 'showSetPasswordForm'])->name('password.set');
// Route::post('/password/set', [PasswordController::class, 'setPassword']);

Route::middleware(['auth'])->group(function () {
    Route::get('/password/set', [PasswordController::class, 'showSetPasswordForm'])->name('password.set');
    Route::post('/password/set', [PasswordController::class, 'setPassword']);
});

Route::middleware(['auth', AuthAdmin::class])
    ->prefix('admin')
    ->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/categories', [AdminController::class, 'categories'])->name('admin.categories');
        Route::get('/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
        Route::post('/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');
        Route::get('/category/edit/{id}', [AdminController::class, 'category_edit'])->name('admin.category.edit');
        Route::put('/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
        Route::delete('/category/{id}/archive', [AdminController::class, 'category_archive'])->name('admin.category.archive');
        Route::get('/archived-categories', [AdminController::class, 'archived_categories'])->name('admin.archived-categories');
        Route::put('/categories/{id}/restore', [AdminController::class, 'restore_categories'])->name('admin.category.restore');

        // api
        Route::get('/api/dashboard-data', [AdminController::class, 'getDashboardData'])->name('admin.api.dashboard-data');
        Route::get('/api/months', [AdminController::class, 'getAvailableMonths'])->name('admin.api.months');
        Route::get('/api/weeks', [AdminController::class, 'getAvailableWeeks'])->name('admin.api.weeks');

        Route::get('/profile', [AdminProfileController::class, 'show_profile'])->name('admin.profile.index');
        Route::put('/profile/update', [AdminProfileController::class, 'update_profile'])->name('admin.profile.update');
        Route::put('/phone/update', [AdminProfileController::class, 'update_phone'])->name('admin.phone.update');
        Route::post('/profile/update-image', [AdminProfileController::class, 'update_profile_image'])->name('admin.profile.update-image');

        Route::get('/facilities', [FacilityController::class, 'index'])->name('admin.facilities.index');
        Route::get('/facilities/search', [FacilityController::class, 'search'])->name('admin.facilities.search');
        Route::get('/facility/create', [FacilityController::class, 'create'])->name('admin.facility.create');
        Route::post('/facility/store', [FacilityController::class, 'store'])->name('admin.facilities.store');
        Route::get('/facility/edit/{id}', [FacilityController::class, 'edit'])->name('admin.facilities.edit');
        Route::put('/facility/update/{id}', [FacilityController::class, 'update'])->name('admin.facilities.update');
        Route::get('/reservation/events/{availability_id}', [FacilityController::class, 'events'])->name('admin.facilities.reservations-events');
        Route::get('/{availability_id}/reservation-history', [FacilityController::class, 'reservationHistory'])->name('admin.facilities.reservations-history');

        Route::get('/facilities/dashboard', [FacilityController::class, 'facilityDashboard'])->name('admin.facilities.dashboard');

        Route::get('/facility/reports', [FacilityReportController::class, 'index'])->name('admin.facility.reports');
        Route::get('/facilities/reports/data', [FacilityReportController::class, 'data'])->name('admin.facility.reports.data');
        Route::get('/facilities/reports/filter-options', [FacilityReportController::class, 'getFilterOptions'])->name('admin.facility.reports.filter-options');
        Route::get('/facilities/reports/summary', [FacilityReportController::class, 'summary'])->name('admin.facility.reports.summary');

        Route::get('/facility/reports/download-facility-pdf', [FacilityReportController::class, 'downloadFacilityPdf'])->name('admin.facility.reports.downloadFacilityPdf');

        Route::post('/prices/store', [FacilityController::class, 'price_store'])->name('prices.store');
        // archive routes
        Route::get('/facility/archive/show', [FacilityController::class, 'showFacilities'])->name('admin.facilities.archive.index');
        Route::delete('/facility/{id}/archive', [FacilityController::class, 'archivedFacilities'])->name('admin.facilities.archive');
        Route::post('/facility/restore', [FacilityController::class, 'restoreFacilities'])->name('admin.facility.restore');

        Route::put('/facilities/reservation/{id}/update-status', [FacilityController::class, 'updateStatus'])
            ->name('admin.facilities.reservation.updateStatus');
        Route::post('/facility/{facilityId}/rooms', [FacilityController::class, 'storeRooms'])
            ->name('facility.rooms.store');
        Route::get('/facility/{facilityId}/rooms', [FacilityController::class, 'getRooms'])
            ->name('facility.rooms.get');

        Route::resource('discounts', DiscountController::class)
            ->except(['show']);

        Route::put('discounts/{discount}/archive', [DiscountController::class, 'archive'])->name('discounts.archive');
        Route::get('discounts/archived', [DiscountController::class, 'archived'])->name('discounts.archived');
        Route::put('discounts/{discount}/restore', [DiscountController::class, 'restore'])->name('discounts.restore');
        Route::post('discounts/restore-bulk', [DiscountController::class, 'restoreBulk'])->name('discounts.restore.bulk');

        // Admin notification routes
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'allNotifications'])->name('admin.notifications.all');
            Route::get('/unread', [NotificationController::class, 'unread'])->name('admin.notifications.unread');
            Route::post('/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('admin.notifications.mark-as-read');
            Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('admin.notifications.mark-all-as-read');
            Route::post('/mark-multiple-as-read', [NotificationController::class, 'markMultipleAsRead'])->name('admin.notifications.mark-multiple-as-read');
            Route::delete('/destroy/{id}', [NotificationController::class, 'destroy'])->name('admin.notifications.destroy');
            Route::delete('/destroy-all', [NotificationController::class, 'destroyAll'])->name('admin.notifications.destroy-all');
            Route::get('/count', [NotificationController::class, 'unreadCount'])->name('admin.notifications.unread-count');
        });

        Route::get('/products', [AdminController::class, 'products'])->name('admin.products');
        Route::get('/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');
        Route::post('/product/store', [AdminController::class, 'product_store'])->name('admin.product.store');
        Route::get('/product/edit/{id}', [AdminController::class, 'product_edit'])->name('admin.product.edit');
        Route::put('/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
        Route::delete('/product/{id}/archived', [AdminController::class, 'archivedProducts'])->name('admin.product.archive');
        Route::get('/archived-products', [AdminController::class, 'showArchivedProducts'])->name('admin.archived-products');
        Route::post('/product/restore', [AdminController::class, 'restoreProducts'])->name('admin.product.restore');
        Route::post('/product/delete', [AdminController::class, 'deleteProducts'])->name('admin.product.delete');
        Route::get('/products/search', [AdminController::class, 'searchProducts'])->name('admin.products.search');

        Route::get('/product-attributes', [AdminController::class, 'prod_attributes'])->name('admin.product-attributes');
        Route::get('/product-attribute/add', [AdminController::class, 'prod_attribute_add'])->name('admin.product-attribute-add');
        Route::post('/product-attributes/store', [AdminController::class, 'prod_attribute_store'])->name('admin.product.attribute.store');
        Route::get('/product-attribute/edit/{id}', [AdminController::class, 'product_attribute_edit'])->name('admin.product.attribute.edit');
        Route::put('/product-attribute/update', [AdminController::class, 'product_attribute_update'])->name('admin.product.attribute.update');
        Route::delete('/product-attribute/{id}/delete', [AdminController::class, 'product_attribute_delete'])->name('admin.product.attribute.delete');

        Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
        Route::get('/orders/filters', [AdminController::class, 'filterOrders'])->name('admin.orders.filter');
        Route::get('/order/{order_id}/details', [AdminController::class, 'order_details'])->name('admin.order.details');
        Route::put('/order/update-status', [AdminController::class, 'update_order_status'])->name('admin.order.status.update');
        Route::post('/order/{order_id}/complete-payment', [AdminController::class, 'completePayment'])->name('admin.order.complete-payment');
        Route::get('/order/{order}/receipt-pdf', [AdminController::class, 'downloadReceipt'])->name('admin.order-receipt.pdf');

        Route::get('/facilities/reservations', [FacilityReservationController::class, 'index'])->name('admin.facilities.reservations');
        Route::get('/facilities/reservations/{id}', [FacilityReservationController::class, 'show'])->name('admin.facilities.reservations.show');
        Route::patch('/facilities/reservations/{reservation}/status', [FacilityReservationController::class, 'update'])->name('admin.facilities.reservations.update');
        Route::patch('/facilities/reservations/qualification/{id}/status', [FacilityReservationController::class, 'updateQualificationApproval'])->name('admin.facilities.reservations.qualification.update');
        Route::put('/addon-payments/{id}', [FacilityReservationController::class, 'updateAddonPayment'])
            ->name('admin.addon-payments.update');

        Route::get('/slide', [AdminController::class, 'slides'])->name('admin.slides');
        Route::get('/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');
        Route::post('/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
        Route::get('/slide/{id}/edit', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');
        Route::put('/slide/update', [AdminController::class, 'slide_update'])->name('admin.slide.update');
        Route::delete('/slide/{id}/delete', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');

        Route::get('/contact', [AdminController::class, 'contacts'])->name('admin.contacts');
        Route::delete('/contact/{id}/delete', [AdminController::class, 'contact_delete'])->name('admin.contact.delete');
        Route::post('/contact/{id}/reply', [AdminController::class, 'contact_reply'])->name('admin.contact.reply');

        Route::get('/courses-by-college/{college}', [AdminController::class, 'getCoursesByCollege'])->name('admin.courses.by.college');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/user/filter', [AdminController::class, 'filter'])->name('admin.users.filter');
        Route::get('/user/search', [AdminController::class, 'search'])->name('admin.users.search');
        Route::delete('/users/{id}', [AdminController::class, 'users_destroy'])->name('admin.users.destroy');
        Route::get('/users/{id}/edit', [AdminController::class, 'users_edit'])->name('admin.users.edit');
        Route::put('/users/{id}/update', [AdminController::class, 'users_update'])->name('admin.users.update');
        Route::get('/add', [AdminController::class, 'users_add'])->name('admin.users.add');
        Route::post('/store', [AdminController::class, 'users_store'])->name('admin.users.store');
        // Route::get('/admin/courses-by-college/{collegeId}', [UserController::class, 'coursesByCollege'])->name('admin.courses.by.college');

        // College routes
        Route::get('/admin/colleges', [AdminCollegeController::class, 'index'])->name('admin.colleges.index');
        Route::get('/admin/college/create', [AdminCollegeController::class, 'create'])->name('admin.colleges.create');
        Route::post('/admin/colleges', [AdminCollegeController::class, 'store'])->name('admin.colleges.store');
        Route::get('/colleges/{id}/edit', [AdminCollegeController::class, 'edit'])->name('admin.colleges.edit');
        Route::put('/colleges/{id}', [AdminCollegeController::class, 'update'])->name('admin.colleges.update');
        Route::delete('/admin/colleges/{id}', [AdminCollegeController::class, 'destroy'])->name('admin.colleges.destroy');
        Route::get('/admin/colleges/archive', [AdminCollegeController::class, 'archive'])->name('admin.colleges.archive');
        Route::patch('/admin/colleges/{id}/restore', [AdminCollegeController::class, 'restore'])->name('admin.colleges.restore');
        Route::delete('/admin/colleges/{id}/force-delete', [AdminCollegeController::class, 'forceDelete'])->name('admin.colleges.force-delete');

        // Courses Routes
        Route::get('courses', [AdminCourseController::class, 'index'])->name('admin.courses.index');
        Route::get('courses/create', [AdminCourseController::class, 'create'])->name('admin.courses.create');
        Route::post('courses', [AdminCourseController::class, 'store'])->name('admin.courses.store');
        Route::get('courses/{course}/edit', [AdminCourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('courses/{course}', [AdminCourseController::class, 'update'])->name('admin.courses.update');
        Route::delete('/courses/{id}', [AdminCourseController::class, 'destroy'])->name('admin.courses.destroy');
        Route::get('/courses/archive', [AdminCourseController::class, 'archive'])->name('admin.courses.archive');
        Route::patch('/courses/{id}/restore', [AdminCourseController::class, 'restore'])->name('admin.courses.restore');
        Route::delete('/courses/{id}/force-delete', [AdminCourseController::class, 'forceDelete'])->name('admin.courses.force-delete');

        // Addons
        Route::get('addons', [AddonsController::class, 'index'])->name('admin.addons');
        Route::get('addons/create', [AddonsController::class, 'create'])->name('admin.addons.create');
        Route::post('addons', [AddonsController::class, 'store'])->name('admin.addons.store');
        Route::get('/addons/{id}/edit', [AddonsController::class, 'edit'])->name('admin.addons.edit');
        Route::put('/addons/update/{id}', [AddonsController::class, 'update'])->name('admin.addons.update');
        Route::delete('/addons/{id}', [AddonsController::class, 'destroy'])->name('admin.addons.destroy');

        // Addon archive routes
        Route::get('/addons/archive', [AddonsController::class, 'archive'])->name('admin.addons.archive');
        Route::patch('/addons/{id}/restore', [AddonsController::class, 'restore'])->name('admin.addons.restore');
        Route::delete('/addons/{id}/force-delete', [AddonsController::class, 'forceDelete'])->name('admin.addons.force-delete');

        //Validation for duplicate names in Addons
        Route::get('/addons/get-names', [AddonsController::class, 'getAddonNames'])->name('admin.addons.getNames');

        Route::get('/search', [AdminController::class, 'searchproduct'])->name('admin.searchproduct');

        Route::get('/index-weekly', [AdminController::class, 'indexWeekly'])->name('admin.index-weekly');
        Route::get('/getWeeklyData', [AdminController::class, 'getWeeklyData'])->name('admin.getWeeklyData');
        Route::get('/index-daily', [AdminController::class, 'indexDaily'])->name('admin.index-daily');

        Route::get('/reports', [ReportController::class, 'generateReport'])->name('admin.reports');
        Route::get('/report-user', [ReportController::class, 'generateUser'])->name('admin.report-user');
        Route::get('/report-product', [ReportController::class, 'generateProduct'])->name('admin.report-product');
        Route::get('/admin/reports/product-list', [ReportController::class, 'productList'])->name('admin.report.product-list');
        Route::get('/report-inventory', [ReportController::class, 'generateInventory'])->name('admin.report-inventory');
        Route::get('/report-statements', [ReportController::class, 'listBillingStatements'])->name('admin.report-statements');
        Route::get('/report-statement/{orderId}', [ReportController::class, 'generateBillingStatement'])->name('admin.report-statement');

        Route::get('/facilities-sales', [ReportController::class, 'listSalesFacilities'])->name('admin.facilties.stataments');
        Route::get('/payment-details/{paymentId}', [ReportController::class, 'showPaymentDetails'])->name('admin.sales-report-details');

        Route::get('/facility-statement', [ReportController::class, 'facilitiesStatement'])->name('admin.facility-statement');
        Route::get('/admin/facility-statement/addons', [ReportController::class, 'getAddonsData'])
            ->name('admin.facility-statement.addons');

        Route::post('/report-statements/download', [PdfController::class, 'downloadBillingStatements'])->name('admin.report-statements.download');
        Route::post('/downloadPdf', [PdfController::class, 'downloadPdf'])->name('admin.downloadPdf');
        Route::post('/report-user/pdf', [PdfController::class, 'downloadUserReportPdf'])->name('admin.report-user.pdf');
        Route::get('/report-inventory/pdf', [PdfController::class, 'downloadInventoryReportPdf'])->name('admin.report-inventory.pdf');
        Route::get('/report-product/download', [PdfController::class, 'downloadProduct'])->name('admin.report-product.download');
        Route::post('/admin/reports/product-list/download', [PdfController::class, 'downloadProductList'])->name('admin.report.product-list.download');
        Route::post('/sales-report/download', [PdfController::class, 'downloadInputSales'])->name('admin.download-input-sales');
        Route::post('/user-report/download', [PdfController::class, 'downloadInputUsers'])->name('admin.download-input-users');
        Route::get('/facility-statement/download', [PdfController::class, 'facilityStatement'])->name('admin.facility-statement.download');

        Route::post('/sales-report', [ReportController::class, 'generateInputSales'])->name('admin.generate-input-sales');
        Route::get('/sales-report', function () {
            $reportController = new ReportController();
            $periods = $reportController->getAvailablePeriods();
            $dateParams = $reportController->getDateParameters(new \Illuminate\Http\Request());


            return view('admin.reports.product-input-sales', [
                'chartData' => null,
                'startDate' => null,
                'endDate' => null,
                'availableMonths' => $periods['availableMonths'],
                'availableWeeks' => $periods['availableWeeks'],
                'yearRange' => $periods['yearRange'],
                'selectedMonth' => $periods['availableMonths']->firstWhere('id', $dateParams['selectedMonth']) ?: $periods['availableMonths']->first(),
                'selectedYear' => $dateParams['selectedYear'],
                'selectedWeekId' => $dateParams['selectedWeek']
            ]);
        })->name('admin.reports.input-sales');

        Route::post('/user-report', [ReportController::class, 'generateInputUsers'])->name('admin.generate-input-users');
        Route::get('/user-report', function () {
            return view('admin.reports.product-input-user');
        });

        Route::get('/report/facilities', [AdminController::class, 'generateFacilitespayment'])->name('admin.report.facilities');


        // Print Data
        // Products
        Route::get('/admin/reports/print/product', [PrintController::class, 'printProduct'])->name('admin.report-product.print');

        // signature
        // In your web.php routes file
        Route::resource('signatures', SignatureController::class)->names([
            'index' => 'admin.signatures.index',
            'create' => 'admin.signatures.create',
            'store' => 'admin.signatures.store',
            'show' => 'admin.signatures.show',
            'edit' => 'admin.signatures.edit',
            'update' => 'admin.signatures.update',
            'destroy' => 'admin.signatures.destroy'
        ]);

        // Archive routes for signatures
        Route::get('/admin/signatures/archive', [SignatureController::class, 'archive'])
            ->name('admin.signatures.archive');
        Route::patch('/admin/signatures/{id}/restore', [SignatureController::class, 'restore'])
            ->name('admin.signatures.restore');
        Route::delete('/admin/signatures/{id}/force-delete', [SignatureController::class, 'forceDelete'])
            ->name('admin.signatures.force-delete');

        //  Route::get('signature', [SignatureController::class, 'index'])->name('admin.signatures.index');
    });
