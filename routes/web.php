<?php

use App\Domains\Invoicing\Models\Invoice;
use App\Http\Controllers\Seo\RobotsController;
use App\Http\Controllers\Seo\SitemapController;
use App\Livewire\Catalog\PartiesIndex;
use App\Livewire\Catalog\PartyStatement;
use App\Livewire\Catalog\ProductsIndex;
use App\Livewire\Dashboard\Overview;
use App\Livewire\Invoicing\InvoiceForm;
use App\Livewire\Invoicing\InvoicesIndex;
use App\Livewire\Invoicing\InvoiceShow;
use App\Livewire\Invoicing\QuoteForm;
use App\Livewire\Invoicing\QuotesIndex;
use App\Livewire\Invoicing\QuoteShow;
use App\Livewire\Organization\InvoiceDesignSettings;
use App\Livewire\Organization\OrganizationSettings;
use App\Livewire\Profile\ManageProfile;
use App\Livewire\Reports\TaxReport;
use App\Livewire\Zatca\DeviceSettings;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');
Route::view('/about', 'marketing.about')->name('about');
Route::view('/contact', 'marketing.contact')->name('contact');
Route::view('/electronic-invoicing', 'marketing.pages.electronic-invoicing')->name('marketing.electronic-invoicing');
Route::view('/tax-invoice', 'marketing.pages.tax-invoice')->name('marketing.tax-invoice');
Route::view('/quotations', 'marketing.pages.quotations')->name('marketing.quotations');
Route::view('/free-invoice-software', 'marketing.pages.free-invoice-software')->name('marketing.free-invoice-software');
Route::view('/zatca-e-invoicing', 'marketing.pages.zatca-e-invoicing')->name('marketing.zatca-e-invoicing');
Route::view('/privacy', 'marketing.pages.privacy')->name('privacy');
Route::view('/terms', 'marketing.pages.terms')->name('terms');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/dashboard', Overview::class)->name('dashboard');

    Route::get('/organization', OrganizationSettings::class)->name('organization.edit');
    Route::get('/organization/invoice-design', InvoiceDesignSettings::class)->name('organization.invoice-design');
    Route::get('/parties', PartiesIndex::class)->name('parties.index');
    Route::get('/parties/{party}/statement', PartyStatement::class)->name('parties.statement');
    Route::get('/products', ProductsIndex::class)->name('products.index');
    Route::get('/zatca/devices', DeviceSettings::class)->name('zatca.devices');
    Route::get('/reports', TaxReport::class)->name('reports.index');

    Route::get('/quotes', QuotesIndex::class)->name('quotes.index');
    Route::get('/quotes/create', QuoteForm::class)->name('quotes.create');
    Route::get('/quotes/{quote}', QuoteShow::class)->name('quotes.show');
    Route::get('/quotes/{quote}/edit', QuoteForm::class)->name('quotes.edit');

    Route::get('/invoices', InvoicesIndex::class)->name('invoices.index')
        ->defaults('direction', 'sales');
    Route::get('/invoices/create', InvoiceForm::class)->name('invoices.create')
        ->defaults('direction', 'sales');
    Route::get('/invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit')
        ->defaults('direction', 'sales');

    Route::get('/purchases', InvoicesIndex::class)->name('purchases.index')
        ->defaults('direction', 'purchase');
    Route::get('/purchases/create', InvoiceForm::class)->name('purchases.create')
        ->defaults('direction', 'purchase');
    Route::get('/purchases/{invoice}', InvoiceShow::class)->name('purchases.show');
    Route::get('/purchases/{invoice}/edit', InvoiceForm::class)->name('purchases.edit')
        ->defaults('direction', 'purchase');

    Route::get('/invoices/{invoice}/pdf', function (Invoice $invoice) {
        abort_unless($invoice->status === 'issued', 404);

        $path = app(\App\Domains\Invoicing\Services\InvoicePdfRenderer::class)->render($invoice);

        return Storage::disk('local')->download($path, ($invoice->invoice_number ?: 'invoice').'.pdf');
    })->name('invoices.pdf');
});

Route::get('/profile', ManageProfile::class)
    ->middleware(['auth', 'tenant'])
    ->name('profile');

require __DIR__.'/auth.php';
