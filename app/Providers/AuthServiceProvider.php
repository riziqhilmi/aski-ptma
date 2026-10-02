<?php

namespace App\Providers;

use App\Models\MembershipApplication;
use App\Models\Invoice;
use App\Models\IntellectualProperty;
use App\Models\Document;
use App\Policies\InvoicePolicy;
use App\Policies\IntellectualPropertyPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\MembershipApplicationPolicy;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        MembershipApplication::class => MembershipApplicationPolicy::class,
        Invoice::class => InvoicePolicy::class,
        IntellectualProperty::class => IntellectualPropertyPolicy::class,
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
