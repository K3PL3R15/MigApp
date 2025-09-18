<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Policies\BranchPolicy;
use App\Policies\InventoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\SalePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Branch::class => BranchPolicy::class,
        Inventory::class => InventoryPolicy::class,
        Product::class => ProductPolicy::class,
        Sale::class => SalePolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        
        // Gates adicionales si son necesarios
        Gate::define('access-owner-features', function ($user) {
            return $user->role === 'owner';
        });
        
        Gate::define('access-manager-features', function ($user) {
            return in_array($user->role, ['owner', 'manager']);
        });
    }
}
