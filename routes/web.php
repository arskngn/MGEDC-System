<?php

use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\ExtensionController;
use App\Http\Controllers\GeneralSettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NotificationSettingController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\ReportRequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SystemConfigurationController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierPaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPaymentController;
use App\Http\Controllers\CustomerNotificationController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffPerformanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:Dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats')->middleware('permission:Dashboard');

    Route::get('/system-settings', function () {
        return view('settings.index');
    })->name('settings.index')->middleware('permission:Setting Index');

    Route::get('/system-settings/general', [GeneralSettingController::class, 'index'])->name('settings.general')->middleware('permission:Setting Index');
    Route::post('/system-settings/general', [GeneralSettingController::class, 'update'])->name('settings.general.update')->middleware('permission:Update Setting');

    Route::get('/system-settings/logo-favicon', [GeneralSettingController::class, 'logoFavicon'])->name('settings.logo-favicon')->middleware('permission:Setting Logo Icon');
    Route::post('/system-settings/logo-favicon', [GeneralSettingController::class, 'updateLogoFavicon'])->name('settings.logo-favicon.update')->middleware('permission:Store Setting Logo Icon');

    Route::get('/system-settings/configuration', [SystemConfigurationController::class, 'index'])->name('settings.configuration')->middleware('permission:Setting System Configuration');
    Route::post('/system-settings/configuration', [SystemConfigurationController::class, 'update'])->name('settings.configuration.update')->middleware('permission:Store Setting System Configuration');

    Route::get('/system-settings/notification', [NotificationSettingController::class, 'index'])->name('settings.notification')->middleware('permission:Email Notification Setting');
    Route::post('/system-settings/notification', [NotificationSettingController::class, 'update'])->name('settings.notification.update')->middleware('permission:Notification Setting Email Update');
    Route::post('/system-settings/notification/test-mail', [NotificationSettingController::class, 'sendTestMail'])->name('settings.notification.test-mail')->middleware('permission:Notification Setting Email Test');
    Route::post('/system-settings/notification/test-sms', [NotificationSettingController::class, 'sendTestSms'])->name('settings.notification.test-sms')->middleware('permission:Notification Setting SMS Test');
    Route::post('/system-settings/notification/template/{id}', [NotificationSettingController::class, 'updateTemplate'])->name('settings.notification.template.update')->middleware('permission:Setting Notification Template Update');

    Route::get('/system-settings/extensions', [ExtensionController::class, 'index'])->name('settings.extensions')->middleware('permission:Setting Index');
    Route::post('/system-settings/extensions/update/{extension}', [ExtensionController::class, 'update'])->name('settings.extensions.update')->middleware('permission:Setting Index');
    Route::post('/system-settings/extensions/toggle/{extension}', [ExtensionController::class, 'toggleStatus'])->name('settings.extensions.toggle')->middleware('permission:Setting Index');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

    // Staff Management
    Route::controller(StaffController::class)->group(function () {
        Route::get('/staff/all', 'index')->name('staff.index')->middleware('permission:All Staffs');
        Route::post('/staff/store', 'store')->name('staff.store')->middleware('permission:Save Staff');
        Route::post('/staff/update/{staff}', 'update')->name('staff.update')->middleware('permission:Save Staff');
        Route::post('/staff/toggle-status/{staff}', 'toggleStatus')->name('staff.toggle-status')->middleware('permission:Staff Status');
        Route::post('/staff/login-as/{staff}', 'loginAs')->name('staff.login-as')->middleware('permission:Staff Login');
        Route::get('/staff/stop-impersonating', 'stopImpersonating')->name('staff.stop-impersonating');
        Route::get('/staff/statuses', 'getStatuses')->name('staff.statuses');
    });

    // Staff Performance
    Route::controller(StaffPerformanceController::class)->group(function () {
        Route::get('/staff/performance', 'dashboard')->name('staff.performance.dashboard')->middleware('permission:All Staffs');
        Route::get('/staff/{user}/performance', 'show')->name('staff.performance.show')->middleware('permission:All Staffs');
        Route::post('/staff/performance/assign', 'assignTarget')->name('staff.performance.assign')->middleware('permission:Save Staff');
    });

    // Role Management
    Route::controller(RoleController::class)->group(function () {
        Route::get('/staff/roles', 'index')->name('staff.roles.index')->middleware('permission:Roles Index');
        Route::get('/staff/roles/create', 'create')->name('staff.roles.create')->middleware('permission:Roles Add');
        Route::post('/staff/roles/store', 'store')->name('staff.roles.store')->middleware('permission:Roles Save');
        Route::get('/staff/roles/edit/{role}', 'edit')->name('staff.roles.edit')->middleware('permission:Roles Edit');
        Route::post('/staff/roles/update/{role}', 'update')->name('staff.roles.update')->middleware('permission:Roles Save');
    });

    // Page Routes
    // Purchases (static routes must be registered before /purchases/{purchase})
    Route::get('/purchases/all', [PurchaseController::class, 'index'])->name('purchases.all')->middleware('permission:All Purchases');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create')->middleware('permission:New Purchase');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store')->middleware('permission:Store Purchase');
    Route::get('/purchases/products/search', [PurchaseController::class, 'searchProducts'])->name('purchases.products.search')->middleware('permission:Product Searching');
    Route::get('/purchases/export/pdf', [PurchaseController::class, 'exportPdfAll'])->name('purchases.export.pdf')->middleware('permission:Download Purchase PDF');
    Route::get('/purchases/export/csv', [PurchaseController::class, 'exportCsvAll'])->name('purchases.export.csv')->middleware('permission:Download Purchase CSV');
    Route::get('/purchases/return', [PurchaseReturnController::class, 'index'])->name('purchases.return')->middleware('permission:All Purchase Return');
    Route::get('/purchases/return/export/pdf', [PurchaseReturnController::class, 'exportPdfAll'])->name('purchases.return.export.pdf')->middleware('permission:Download Purchase Return PDF');
    Route::get('/purchases/return/export/csv', [PurchaseReturnController::class, 'exportCsvAll'])->name('purchases.return.export.csv')->middleware('permission:Download Purchase Return CSV');
    Route::get('/purchases/{purchase}/returns/create', [PurchaseReturnController::class, 'create'])->name('purchases.return.create')->middleware('permission:Store Purchase Return');
    Route::post('/purchases/{purchase}/returns', [PurchaseReturnController::class, 'store'])->name('purchases.return.store')->middleware('permission:Store Purchase Return');
    Route::get('/purchases/return/{purchaseReturn}/edit', [PurchaseReturnController::class, 'edit'])->name('purchases.return.edit')->middleware('permission:Edit Purchase Return');
    Route::put('/purchases/return/{purchaseReturn}', [PurchaseReturnController::class, 'update'])->name('purchases.return.update')->middleware('permission:Update Purchase Return');
    Route::post('/purchases/return/{purchaseReturn}/payment', [PurchaseReturnController::class, 'storePayment'])->name('purchases.return.payment')->middleware('permission:Store Supplier Payment Receive');
    Route::get('/purchases/return/{purchaseReturn}/pdf', [PurchaseReturnController::class, 'exportPdfInvoice'])->name('purchases.return.invoice.pdf')->middleware('permission:Download Purchase Return Invoice PDF');
    Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit')->middleware('permission:Edit Purchase');
    Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->name('purchases.update')->middleware('permission:Purchase Update');
    Route::post('/purchases/{purchase}/payment', [PurchaseController::class, 'storePayment'])->name('purchases.payment')->middleware('permission:Store Supplier Payment');
    Route::get('/purchases/{purchase}/pdf', [PurchaseController::class, 'exportPdfInvoice'])->name('purchases.invoice.pdf')->middleware('permission:Download Purchase Invoice PDF');

    Route::get('/products/all', [ProductController::class, 'index'])->name('products.all')->middleware('permission:All Product');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create')->middleware('permission:Add Product');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('permission:Product Store');
    Route::get('/products/export/pdf', [ProductController::class, 'exportPdfAll'])->name('products.export.pdf')->middleware('permission:Download Product PDF');
    Route::get('/products/export/csv', [ProductController::class, 'exportCsvAll'])->name('products.export.csv')->middleware('permission:Download Product CSV');
    Route::get('/products/import/sample', [ProductController::class, 'importSample'])->name('products.import.sample')->middleware('permission:Product Import');
    Route::post('/products/import', [ProductController::class, 'importStore'])->name('products.import')->middleware('permission:Product Import');

    // Categories (register before /products/{product} so paths stay unambiguous)
    Route::get('/products/categories/import/sample', [CategoryController::class, 'importSample'])->name('categories.import.sample')->middleware('permission:Import Category');
    Route::post('/products/categories/import', [CategoryController::class, 'importStore'])->name('categories.import')->middleware('permission:Import Category');
    Route::get('/products/categories', [CategoryController::class, 'index'])->name('categories.index')->middleware('permission:All Categorys');
    Route::post('/products/categories', [CategoryController::class, 'store'])->name('categories.store')->middleware('permission:Store Category');
    Route::put('/products/categories/{category}', [CategoryController::class, 'update'])->name('categories.update')->middleware('permission:Store Category');
    Route::delete('/products/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy')->middleware('permission:Delete Category');

    // Brands (same pattern as categories; register before /products/{product})
    Route::get('/products/brands/import/sample', [BrandController::class, 'importSample'])->name('brands.import.sample')->middleware('permission:Import Brand');
    Route::post('/products/brands/import', [BrandController::class, 'importStore'])->name('brands.import')->middleware('permission:Import Brand');
    Route::get('/products/brands', [BrandController::class, 'index'])->name('brands.index')->middleware('permission:All Brands');
    Route::post('/products/brands', [BrandController::class, 'store'])->name('brands.store')->middleware('permission:Store Brand');
    Route::put('/products/brands/{brand}', [BrandController::class, 'update'])->name('brands.update')->middleware('permission:Store Brand');
    Route::delete('/products/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy')->middleware('permission:Delete Brand');

    // Units (same pattern; register before /products/{product})
    Route::get('/products/units/import/sample', [UnitController::class, 'importSample'])->name('units.import.sample')->middleware('permission:Import Unit');
    Route::post('/products/units/import', [UnitController::class, 'importStore'])->name('units.import')->middleware('permission:Import Unit');
    Route::get('/products/units', [UnitController::class, 'index'])->name('units.index')->middleware('permission:All Unit');
    Route::post('/products/units', [UnitController::class, 'store'])->name('units.store')->middleware('permission:Store Unit');
    Route::put('/products/units/{unit}', [UnitController::class, 'update'])->name('units.update')->middleware('permission:Store Unit');
    Route::delete('/products/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy')->middleware('permission:Delete Unit');

    // Expense Types
    Route::get('/expense_types/import/sample', [ExpenseTypeController::class, 'importSample'])->name('expense_types.import.sample')->middleware('permission:Import Expense Types');
    Route::post('/expense_types/import', [ExpenseTypeController::class, 'importStore'])->name('expense_types.import')->middleware('permission:Import Expense Types');
    Route::get('/expense_types', [ExpenseTypeController::class, 'index'])->name('expense_types.index')->middleware('permission:All Expense Types');
    Route::post('/expense_types', [ExpenseTypeController::class, 'store'])->name('expense_types.store')->middleware('permission:Store Expense Type');
    Route::put('/expense_types/{expense_type}', [ExpenseTypeController::class, 'update'])->name('expense_types.update')->middleware('permission:Store Expense Type');
    Route::delete('/expense_types/{expense_type}', [ExpenseTypeController::class, 'destroy'])->name('expense_types.destroy')->middleware('permission:Delete Expense Type');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index')->middleware('permission:All Expenses');
    Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create')->middleware('permission:Store Expense');
    Route::get('/expenses/export/pdf', [ExpenseController::class, 'exportPdfAll'])->name('expenses.export.pdf')->middleware('permission:Download Expense PDF');
    Route::get('/expenses/export/csv', [ExpenseController::class, 'exportCsvAll'])->name('expenses.export.csv')->middleware('permission:Download Expense CSV');
    Route::get('/expenses/import/sample', [ExpenseController::class, 'importSample'])->name('expenses.import.sample')->middleware('permission:Import Expenses');
    Route::post('/expenses/import', [ExpenseController::class, 'importStore'])->name('expenses.import')->middleware('permission:Import Expenses');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store')->middleware('permission:Store Expense');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'show'])->name('expenses.show')->middleware('permission:Store Expense');
    Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update')->middleware('permission:Store Expense');
    Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('permission:Delete Expense');

    // Suppliers
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index')->middleware('permission:All Suppliers');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store')->middleware('permission:Store Supplier');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware('permission:Store Supplier');

    Route::get('/suppliers/export/pdf', [SupplierController::class, 'exportPdfAll'])->name('suppliers.export.pdf')->middleware('permission:Download Supplier PDF');
    Route::get('/suppliers/export/csv', [SupplierController::class, 'exportCsvAll'])->name('suppliers.export.csv')->middleware('permission:Download Supplier CSV');
    Route::get('/suppliers/import/sample', [SupplierController::class, 'importSample'])->name('suppliers.import.sample')->middleware('permission:Import Suppliers');
    Route::post('/suppliers/import', [SupplierController::class, 'importStore'])->name('suppliers.import')->middleware('permission:Import Suppliers');

    Route::get('/suppliers/{supplier}/payments', [SupplierPaymentController::class, 'index'])->name('suppliers.payments.index')->middleware('permission:Supplier Payment Index');
    Route::post('/suppliers/{supplier}/payments/clear', [SupplierPaymentController::class, 'clear'])->name('suppliers.payments.clear')->middleware('permission:Supplier Payment Clear');

    // Supplier Payments Report
    Route::get('/supplier-payments', [SupplierPaymentController::class, 'allPayments'])->name('supplier-payments.index')->middleware('permission:Supplier Payment Report');
    Route::get('/supplier-payments/export/pdf', [SupplierPaymentController::class, 'exportPdf'])->name('supplier-payments.export.pdf')->middleware('permission:Download Supplier Payment PDF');
    Route::get('/supplier-payments/export/csv', [SupplierPaymentController::class, 'exportCsv'])->name('supplier-payments.export.csv')->middleware('permission:Download Supplier Payment CSV');

    // Customer Payments Report
    Route::get('/customer-payments', [CustomerPaymentController::class, 'allPayments'])->name('customer-payments.index')->middleware('permission:Customer Payment Report');
    Route::get('/customer-payments/export/pdf', [CustomerPaymentController::class, 'exportPdf'])->name('customer-payments.export.pdf')->middleware('permission:Download Customer Payment PDF');
    Route::get('/customer-payments/export/csv', [CustomerPaymentController::class, 'exportCsv'])->name('customer-payments.export.csv')->middleware('permission:Download Customer Payment CSV');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index')->middleware('permission:All Customers');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store')->middleware('permission:Store Customer');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update')->middleware('permission:Store Customer');

    Route::get('/customers/export/pdf', [CustomerController::class, 'exportPdfAll'])->name('customers.export.pdf')->middleware('permission:Download Customer PDF');
    Route::get('/customers/export/csv', [CustomerController::class, 'exportCsvAll'])->name('customers.export.csv')->middleware('permission:Download Customer CSV');
    Route::get('/customers/import/sample', [CustomerController::class, 'importSample'])->name('customers.import.sample')->middleware('permission:Import Customers');
    Route::post('/customers/import', [CustomerController::class, 'importStore'])->name('customers.import')->middleware('permission:Import Customers');

    Route::get('/customers/{customer}/payments', [CustomerPaymentController::class, 'index'])->name('customers.payments.index')->middleware('permission:All Customer Payments');
    Route::post('/customers/{customer}/payments/clear', [CustomerPaymentController::class, 'clear'])->name('customers.payments.clear')->middleware('permission:Clear Payment Of Customer');

    // Customer Notifications
    Route::get('/customers/{customer}/notifications/single', [CustomerNotificationController::class, 'singleLogPage'])
        ->name('customers.notifications.single')
        ->middleware('permission:Customer Notification Single');

    Route::get('/customers/{customer}/notifications/single/send', [CustomerNotificationController::class, 'singleForm'])
        ->name('customers.notifications.single.send-form')
        ->middleware('permission:Customer Notification Single');

    Route::post('/customers/{customer}/notifications/single', [CustomerNotificationController::class, 'sendSingle'])
        ->name('customers.notifications.single.send')
        ->middleware('permission:Customer Notification Single');

    Route::get('/customers/notifications/all', [CustomerNotificationController::class, 'allForm'])
        ->name('customers.notifications.all')
        ->middleware('permission:Customer Notification Send To All');
    Route::post('/customers/notifications/all', [CustomerNotificationController::class, 'sendAll'])
        ->name('customers.notifications.all.send')
        ->middleware('permission:Customer Notification Send To All');

    // Warehouses (standalone paths; not under /products)
    Route::get('/warehouses/import/sample', [WarehouseController::class, 'importSample'])->name('warehouses.import.sample')->middleware('permission:Import Warehouses');
    Route::post('/warehouses/import', [WarehouseController::class, 'importStore'])->name('warehouses.import')->middleware('permission:Import Warehouses');
    Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index')->middleware('permission:All Warehouses');
    Route::post('/warehouses', [WarehouseController::class, 'store'])->name('warehouses.store')->middleware('permission:Store Warehouse');
    Route::put('/warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update')->middleware('permission:Store Warehouse');
    Route::patch('/warehouses/{warehouse}/status', [WarehouseController::class, 'toggleStatus'])->name('warehouses.toggle-status')->middleware('permission:Store Warehouse');
    Route::delete('/warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy')->middleware('permission:Delete Warehouse');

    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('permission:Product Edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('permission:Product Store');

    // Sales (static routes first, then parameterised)
    Route::get('/sales/all', [SaleController::class, 'index'])->name('sales.all')->middleware('permission:All Sales');
    Route::get('/sales/create', [SaleController::class, 'create'])->name('sales.create')->middleware('permission:Create Sale');
    Route::post('/sales', [SaleController::class, 'store'])->name('sales.store')->middleware('permission:Store Sale');
    Route::get('/sales/products/search', [SaleController::class, 'searchProducts'])->name('sales.products.search')->middleware('permission:Product Searching');
    Route::get('/sales/export/pdf', [SaleController::class, 'exportPdfAll'])->name('sales.export.pdf')->middleware('permission:Download Sales PDF');
    Route::get('/sales/export/csv', [SaleController::class, 'exportCsvAll'])->name('sales.export.csv')->middleware('permission:Download Sales CSV');
    Route::get('/sales/{sale}/returns/create', [SaleController::class, 'returnCreate'])->name('sales.return.create')->middleware('permission:Store Sale Return');
    Route::post('/sales/{sale}/returns', [SaleController::class, 'returnStore'])->name('sales.return.store')->middleware('permission:Store Sale Return');
    Route::post('/sales-returns/{saleReturn}/payment', [SaleController::class, 'storeReturnPayment'])->name('sales.return.payment')->middleware('permission:Store Customer Payment');
    Route::get('/sales-returns/{saleReturn}/pdf', [SaleController::class, 'exportPdfReturn'])->name('sales.return.pdf')->middleware('permission:Download Sale Return Invoice PDF');
    Route::get('/sales/{sale}/edit', [SaleController::class, 'edit'])->name('sales.edit')->middleware('permission:Edit Sale');
    Route::put('/sales/{sale}', [SaleController::class, 'update'])->name('sales.update')->middleware('permission:Update Sale');
    Route::post('/sales/{sale}/payment', [SaleController::class, 'storePayment'])->name('sales.payment')->middleware('permission:Store Customer Payment');
    Route::get('/sales/{sale}/pdf', [SaleController::class, 'exportPdfInvoice'])->name('sales.invoice.pdf')->middleware('permission:Download Sale Invoice PDF');
    Route::get('/sales/returns/export/pdf', [SaleController::class, 'exportPdfAllReturns'])->name('sales.returns.export.pdf')->middleware('permission:Download Sale Return PDF');
    Route::get('/sales/returns/export/csv', [SaleController::class, 'exportCsvAllReturns'])->name('sales.returns.export.csv')->middleware('permission:Download Sale Return CSV');

    // Adjustments (static routes must be registered before {adjustment} parameterized routes)
    Route::get('/adjustments', [AdjustmentController::class, 'index'])->name('adjustments.index')->middleware('permission:All Adjustments');
    Route::get('/adjustments/create', [AdjustmentController::class, 'create'])->name('adjustments.create')->middleware('permission:Create Adjustment');
    Route::get('/adjustments/products/search', [AdjustmentController::class, 'searchProducts'])->name('adjustments.products.search')->middleware('permission:Product Searching');
    Route::get('/adjustments/export/pdf', [AdjustmentController::class, 'exportPdfAll'])->name('adjustments.export.pdf')->middleware('permission:Download Adjustment PDF');
    Route::get('/adjustments/export/csv', [AdjustmentController::class, 'exportCsvAll'])->name('adjustments.export.csv')->middleware('permission:Download Adjustment CSV');
    Route::post('/adjustments', [AdjustmentController::class, 'store'])->name('adjustments.store')->middleware('permission:Store Adjustment');
    Route::get('/adjustments/{adjustment}/edit', [AdjustmentController::class, 'edit'])->name('adjustments.edit')->middleware('permission:Edit Adjustment');
    Route::get('/adjustments/{adjustment}/pdf', [AdjustmentController::class, 'exportPdfInvoice'])->name('adjustments.pdf')->middleware('permission:Download Adjustment PDF');
    Route::put('/adjustments/{adjustment}', [AdjustmentController::class, 'update'])->name('adjustments.update')->middleware('permission:Update Adjustment');
    Route::delete('/adjustments/{adjustment}', [AdjustmentController::class, 'destroy'])->name('adjustments.destroy')->middleware('permission:Delete Adjustment');

    // Transfers (static routes must be registered before {transfer} parameterized routes)
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index')->middleware('permission:All Transfers');
    Route::get('/transfers/create', [TransferController::class, 'create'])->name('transfers.create')->middleware('permission:Create Transfer');
    Route::get('/transfers/products/search', [TransferController::class, 'searchProducts'])->name('transfers.products.search')->middleware('permission:Product Searching');
    Route::get('/transfers/export/pdf', [TransferController::class, 'exportPdfAll'])->name('transfers.export.pdf')->middleware('permission:Download Transfer PDF');
    Route::get('/transfers/export/csv', [TransferController::class, 'exportCsvAll'])->name('transfers.export.csv')->middleware('permission:Download Transfer CSV');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store')->middleware('permission:Store Transfer');
    Route::get('/transfers/{transfer}/edit', [TransferController::class, 'edit'])->name('transfers.edit')->middleware('permission:Edit Transfer');
    Route::get('/transfers/{transfer}/pdf', [TransferController::class, 'exportPdfInvoice'])->name('transfers.pdf')->middleware('permission:Download Transfer PDF');
    Route::put('/transfers/{transfer}', [TransferController::class, 'update'])->name('transfers.update')->middleware('permission:Update Transfer');
    Route::delete('/transfers/{transfer}', [TransferController::class, 'destroy'])->name('transfers.destroy')->middleware('permission:Delete Transfer');

    Route::controller(PageController::class)->group(function () {
        Route::get('/sales/return', 'salesReturn')->name('sales.return')->middleware('permission:All Sales Return');

        Route::get('/reports/payments/supplier', 'reportSupplierPayments')->name('reports.payments.supplier')->middleware('permission:Supplier Payment Report');
        Route::get('/reports/payments/customer', 'reportCustomerPayments')->name('reports.payments.customer')->middleware('permission:Customer Payment Report');
        Route::get('/reports/stock', 'reportStock')->name('reports.stock')->middleware('permission:Stock Report');

        Route::get('/reports/data-entry/purchases', 'reportDataEntryPurchases')->name('reports.data-entry.purchases')->middleware('permission:Purchase Data Entry Report');
        Route::get('/reports/data-entry/purchase-return', 'reportDataEntryPurchaseReturn')->name('reports.data-entry.purchases-return')->middleware('permission:Purchase Return Data Entry Report');
        Route::get('/reports/data-entry/sales', 'reportDataEntrySales')->name('reports.data-entry.sales')->middleware('permission:Sale Data Entry Report');
        Route::get('/reports/data-entry/sale-return', 'reportDataEntrySaleReturn')->name('reports.data-entry.sales-return')->middleware('permission:Sale Return Data Entry Report');
        Route::get('/reports/data-entry/products', 'reportDataEntryProducts')->name('reports.data-entry.products')->middleware('permission:Product Data Entry Report');
        Route::get('/reports/data-entry/customers', 'reportDataEntryCustomers')->name('reports.data-entry.customers')->middleware('permission:Customer Data Entry Report');
        Route::get('/reports/data-entry/customer-payments', 'reportDataEntryCustomerPayments')->name('reports.data-entry.customer-payments')->middleware('permission:Customer Payment Data Entry Report');
        Route::get('/reports/data-entry/suppliers', 'reportDataEntrySuppliers')->name('reports.data-entry.suppliers')->middleware('permission:Supplier Data Entry Report');
        Route::get('/reports/data-entry/supplier-payments', 'reportDataEntrySupplierPayments')->name('reports.data-entry.supplier-payments')->middleware('permission:Supplier Payment Data Entry Report');
        Route::get('/reports/data-entry/adjustments', 'reportDataEntryAdjustments')->name('reports.data-entry.adjustments')->middleware('permission:Report Data Entry Report Adjustment');
        Route::get('/reports/data-entry/transfers', 'reportDataEntryTransfers')->name('reports.data-entry.transfers')->middleware('permission:Transfer Data Entry Report');
        Route::get('/reports/data-entry/expenses', 'reportDataEntryExpenses')->name('reports.data-entry.expenses')->middleware('permission:Expense Data Entry Report');

        // Extra Routes
        Route::get('/extra/application', 'extraApplication')->name('extra.application')->middleware('permission:System Info');
        Route::get('/extra/server', 'extraServer')->name('extra.server')->middleware('permission:System Server Info');
        Route::get('/extra/cache', 'extraCache')->name('extra.cache')->middleware('permission:System Optimize');
        Route::post('/extra/cache/clear', 'clearCache')->name('extra.cache.clear')->middleware('permission:System Optimize');
        Route::get('/extra/update', 'extraUpdate')->name('extra.update');
    });

    // Report & Request Controller
    Route::controller(ReportRequestController::class)->group(function () {
        Route::get('/report-request', 'index')->name('report.request')->middleware('permission:Request Report');
        Route::post('/report-request', 'store')->name('report-request.store')->middleware('permission:Request Report');
        Route::post('/report-request/update/{reportRequest}', 'update')->name('report-request.update')->middleware('permission:Request Report');
        Route::delete('/report-request/delete/{reportRequest}', 'destroy')->name('report-request.destroy')->middleware('permission:Request Report');
    });

    
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
