<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if an index exists on a table.
     */
    protected function hasIndex(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite doesn't have information_schema, use PRAGMA instead
            try {
                $indexes = $connection->select("PRAGMA index_list({$table})");
                foreach ($indexes as $idx) {
                    if ($idx->name === $index) {
                        return true;
                    }
                }

                return false;
            } catch (\Exception $e) {
                // If table doesn't exist, return false
                return false;
            }
        }

        // MySQL/MariaDB
        $database = $connection->getDatabaseName();
        try {
            $result = $connection->select(
                'SELECT COUNT(*) as count FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $table, $index]
            );

            return $result[0]->count > 0;
        } catch (\Exception $e) {
            // If information_schema is not available, return false
            return false;
        }
    }

    /**
     * Run the migrations.
     *
     * Add indexes for frequently queried columns to improve query performance.
     */
    public function up(): void
    {
        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            // Composite index for common filter combinations
            if (! $this->hasIndex('products', 'products_status_index')) {
                $table->index(['is_active', 'is_approved', 'is_featured'], 'products_status_index');
            }
            // Index for price range queries
            if (! $this->hasIndex('products', 'products_price_index')) {
                $table->index('price', 'products_price_index');
            }
            // Index for vendor filtering
            if (! $this->hasIndex('products', 'products_vendor_id_index')) {
                $table->index('vendor_id', 'products_vendor_id_index');
            }
            // Index for type filtering
            if (! $this->hasIndex('products', 'products_type_index')) {
                $table->index('type', 'products_type_index');
            }
            // Index for date sorting
            if (! $this->hasIndex('products', 'products_created_at_index')) {
                $table->index('created_at', 'products_created_at_index');
            }
            // Index for new products filter
            if (! $this->hasIndex('products', 'products_is_new_index')) {
                $table->index('is_new', 'products_is_new_index');
            }
            // Index for bookable filter
            if (! $this->hasIndex('products', 'products_is_bookable_index')) {
                $table->index('is_bookable', 'products_is_bookable_index');
            }
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            // Composite index for common order queries
            if (! $this->hasIndex('orders', 'orders_user_status_index')) {
                $table->index(['user_id', 'status'], 'orders_user_status_index');
            }
            if (! $this->hasIndex('orders', 'orders_status_payment_index')) {
                $table->index(['status', 'payment_status'], 'orders_status_payment_index');
            }
            // Index for date range queries
            if (! $this->hasIndex('orders', 'orders_created_at_index')) {
                $table->index('created_at', 'orders_created_at_index');
            }
            // Index for total sorting
            if (! $this->hasIndex('orders', 'orders_total_index')) {
                $table->index('total', 'orders_total_index');
            }
            // Index for payment method filtering
            if (! $this->hasIndex('orders', 'orders_payment_method_index')) {
                $table->index('payment_method', 'orders_payment_method_index');
            }
            // Index for refund status
            if (! $this->hasIndex('orders', 'orders_refund_status_index')) {
                $table->index('refund_status', 'orders_refund_status_index');
            }
        });

        // Vendor Orders table indexes
        Schema::table('vendor_orders', function (Blueprint $table) {
            // Composite index for vendor order queries
            if (! $this->hasIndex('vendor_orders', 'vendor_orders_vendor_status_index')) {
                $table->index(['vendor_id', 'status'], 'vendor_orders_vendor_status_index');
            }
            if (! $this->hasIndex('vendor_orders', 'vendor_orders_order_vendor_index')) {
                $table->index(['order_id', 'vendor_id'], 'vendor_orders_order_vendor_index');
            }
            // Index for branch filtering
            if (! $this->hasIndex('vendor_orders', 'vendor_orders_branch_id_index')) {
                $table->index('branch_id', 'vendor_orders_branch_id_index');
            }
            // Index for status filtering
            if (! $this->hasIndex('vendor_orders', 'vendor_orders_status_index')) {
                $table->index('status', 'vendor_orders_status_index');
            }
        });

        // Cart Items table indexes
        Schema::table('cart_items', function (Blueprint $table) {
            // Composite index for user cart queries
            if (! $this->hasIndex('cart_items', 'cart_items_user_product_index')) {
                $table->index(['user_id', 'product_id'], 'cart_items_user_product_index');
            }
            // Composite index for variant queries
            if (! $this->hasIndex('cart_items', 'cart_items_user_product_variant_index')) {
                $table->index(['user_id', 'product_id', 'variant_id'], 'cart_items_user_product_variant_index');
            }
        });

        // Vendors table indexes
        Schema::table('vendors', function (Blueprint $table) {
            // Composite index for active/featured filtering
            if (! $this->hasIndex('vendors', 'vendors_status_index')) {
                $table->index(['is_active', 'is_featured'], 'vendors_status_index');
            }
            // Index for owner queries
            if (! $this->hasIndex('vendors', 'vendors_owner_id_index')) {
                $table->index('owner_id', 'vendors_owner_id_index');
            }
            // Index for plan filtering
            if (! $this->hasIndex('vendors', 'vendors_plan_id_index')) {
                $table->index('plan_id', 'vendors_plan_id_index');
            }
            // Index for balance sorting
            if (! $this->hasIndex('vendors', 'vendors_balance_index')) {
                $table->index('balance', 'vendors_balance_index');
            }
            // Index for commission rate
            if (! $this->hasIndex('vendors', 'vendors_commission_rate_index')) {
                $table->index('commission_rate', 'vendors_commission_rate_index');
            }
            // Index for date filtering
            if (! $this->hasIndex('vendors', 'vendors_created_at_index')) {
                $table->index('created_at', 'vendors_created_at_index');
            }
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            // Composite index for active/featured filtering
            if (! $this->hasIndex('categories', 'categories_status_index')) {
                $table->index(['is_active', 'is_featured'], 'categories_status_index');
            }
            // Index for parent category queries
            if (! $this->hasIndex('categories', 'categories_parent_id_index')) {
                $table->index('parent_id', 'categories_parent_id_index');
            }
            // Index for date sorting
            if (! $this->hasIndex('categories', 'categories_created_at_index')) {
                $table->index('created_at', 'categories_created_at_index');
            }
        });

        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            // Composite index for role and status filtering
            if (! $this->hasIndex('users', 'users_role_status_index')) {
                $table->index(['role', 'is_active'], 'users_role_status_index');
            }
            // Index for date filtering
            if (! $this->hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at', 'users_created_at_index');
            }
        });

        // Branch Product Stocks table indexes
        Schema::table('branch_product_stocks', function (Blueprint $table) {
            // Composite index for stock queries
            if (! $this->hasIndex('branch_product_stocks', 'branch_product_stocks_branch_product_index')) {
                $table->index(['branch_id', 'product_id'], 'branch_product_stocks_branch_product_index');
            }
            // Index for quantity filtering (in stock queries)
            if (! $this->hasIndex('branch_product_stocks', 'branch_product_stocks_branch_quantity_index')) {
                $table->index(['branch_id', 'quantity'], 'branch_product_stocks_branch_quantity_index');
            }
            // Composite index for product stock queries
            if (! $this->hasIndex('branch_product_stocks', 'branch_product_stocks_product_quantity_index')) {
                $table->index(['product_id', 'quantity'], 'branch_product_stocks_product_quantity_index');
            }
        });

        // Branch Product Variant Stocks table indexes
        Schema::table('branch_product_variant_stocks', function (Blueprint $table) {
            // Composite index for variant stock queries
            if (! $this->hasIndex('branch_product_variant_stocks', 'branch_variant_stocks_branch_variant_index')) {
                $table->index(['branch_id', 'product_variant_id'], 'branch_variant_stocks_branch_variant_index');
            }
            // Index for quantity filtering
            if (! $this->hasIndex('branch_product_variant_stocks', 'branch_variant_stocks_branch_quantity_index')) {
                $table->index(['branch_id', 'quantity'], 'branch_variant_stocks_branch_quantity_index');
            }
            // Composite index for variant stock queries
            if (! $this->hasIndex('branch_product_variant_stocks', 'branch_variant_stocks_variant_quantity_index')) {
                $table->index(['product_variant_id', 'quantity'], 'branch_variant_stocks_variant_quantity_index');
            }
        });

        // Product Ratings table indexes (if exists)
        if (Schema::hasTable('product_ratings')) {
            Schema::table('product_ratings', function (Blueprint $table) {
                // Composite index for product rating queries
                if (! $this->hasIndex('product_ratings', 'product_ratings_product_visible_index')) {
                    $table->index(['product_id', 'is_visible'], 'product_ratings_product_visible_index');
                }
                // Index for user ratings
                if (! $this->hasIndex('product_ratings', 'product_ratings_user_id_index')) {
                    $table->index('user_id', 'product_ratings_user_id_index');
                }
            });
        }

        // Vendor Ratings table indexes (if exists)
        if (Schema::hasTable('vendor_ratings')) {
            Schema::table('vendor_ratings', function (Blueprint $table) {
                // Composite index for vendor rating queries
                if (! $this->hasIndex('vendor_ratings', 'vendor_ratings_vendor_visible_index')) {
                    $table->index(['vendor_id', 'is_visible'], 'vendor_ratings_vendor_visible_index');
                }
                // Index for user ratings
                if (! $this->hasIndex('vendor_ratings', 'vendor_ratings_user_id_index')) {
                    $table->index('user_id', 'vendor_ratings_user_id_index');
                }
            });
        }

        // Favorites table indexes (if exists)
        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                // Composite index for user favorites
                if (! $this->hasIndex('favorites', 'favorites_user_product_index')) {
                    $table->index(['user_id', 'product_id'], 'favorites_user_product_index');
                }
            });
        }

        // Addresses table indexes (if exists)
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                // Index for user addresses
                if (! $this->hasIndex('addresses', 'addresses_user_id_index')) {
                    $table->index('user_id', 'addresses_user_id_index');
                }
            });
        }

        // Tickets table indexes (if exists)
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                // Composite index for user tickets
                if (! $this->hasIndex('tickets', 'tickets_user_status_index')) {
                    $table->index(['user_id', 'status'], 'tickets_user_status_index');
                }
                // Index for status filtering
                if (! $this->hasIndex('tickets', 'tickets_status_index')) {
                    $table->index('status', 'tickets_status_index');
                }
            });
        }

        // Coupons table indexes (if exists)
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                // Index for active coupons with end date
                if (Schema::hasColumn('coupons', 'end_date')) {
                    $table->index(['is_active', 'end_date'], 'coupons_active_end_date_index');
                } else {
                    $table->index('is_active', 'coupons_is_active_index');
                }
                // Index for code lookups (code is already unique, but adding for completeness)
                if (Schema::hasColumn('coupons', 'code')) {
                    // Code is already unique, so we don't need an additional index
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_status_index');
            $table->dropIndex('products_price_index');
            $table->dropIndex('products_vendor_id_index');
            $table->dropIndex('products_type_index');
            $table->dropIndex('products_created_at_index');
            $table->dropIndex('products_is_new_index');
            $table->dropIndex('products_is_bookable_index');
        });

        // Orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_user_status_index');
            $table->dropIndex('orders_status_payment_index');
            $table->dropIndex('orders_created_at_index');
            $table->dropIndex('orders_total_index');
            $table->dropIndex('orders_payment_method_index');
            $table->dropIndex('orders_refund_status_index');
        });

        // Vendor Orders table
        Schema::table('vendor_orders', function (Blueprint $table) {
            $table->dropIndex('vendor_orders_vendor_status_index');
            $table->dropIndex('vendor_orders_order_vendor_index');
            $table->dropIndex('vendor_orders_branch_id_index');
            $table->dropIndex('vendor_orders_status_index');
        });

        // Cart Items table
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('cart_items_user_product_index');
            $table->dropIndex('cart_items_user_product_variant_index');
        });

        // Vendors table
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_status_index');
            $table->dropIndex('vendors_owner_id_index');
            $table->dropIndex('vendors_plan_id_index');
            $table->dropIndex('vendors_balance_index');
            $table->dropIndex('vendors_commission_rate_index');
            $table->dropIndex('vendors_created_at_index');
        });

        // Categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('categories_status_index');
            $table->dropIndex('categories_parent_id_index');
            $table->dropIndex('categories_created_at_index');
        });

        // Users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_status_index');
            $table->dropIndex('users_created_at_index');
        });

        // Branch Product Stocks table
        Schema::table('branch_product_stocks', function (Blueprint $table) {
            $table->dropIndex('branch_product_stocks_branch_product_index');
            $table->dropIndex('branch_product_stocks_branch_quantity_index');
            $table->dropIndex('branch_product_stocks_product_quantity_index');
        });

        // Branch Product Variant Stocks table
        Schema::table('branch_product_variant_stocks', function (Blueprint $table) {
            $table->dropIndex('branch_variant_stocks_branch_variant_index');
            $table->dropIndex('branch_variant_stocks_branch_quantity_index');
            $table->dropIndex('branch_variant_stocks_variant_quantity_index');
        });

        // Product Ratings table
        if (Schema::hasTable('product_ratings')) {
            Schema::table('product_ratings', function (Blueprint $table) {
                $table->dropIndex('product_ratings_product_visible_index');
                $table->dropIndex('product_ratings_user_id_index');
            });
        }

        // Vendor Ratings table
        if (Schema::hasTable('vendor_ratings')) {
            Schema::table('vendor_ratings', function (Blueprint $table) {
                $table->dropIndex('vendor_ratings_vendor_visible_index');
                $table->dropIndex('vendor_ratings_user_id_index');
            });
        }

        // Favorites table
        if (Schema::hasTable('favorites')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropIndex('favorites_user_product_index');
            });
        }

        // Addresses table
        if (Schema::hasTable('addresses')) {
            Schema::table('addresses', function (Blueprint $table) {
                $table->dropIndex('addresses_user_id_index');
            });
        }

        // Tickets table
        if (Schema::hasTable('tickets')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropIndex('tickets_user_status_index');
                $table->dropIndex('tickets_status_index');
            });
        }

        // Coupons table
        if (Schema::hasTable('coupons')) {
            Schema::table('coupons', function (Blueprint $table) {
                if (Schema::hasColumn('coupons', 'end_date')) {
                    $table->dropIndex('coupons_active_end_date_index');
                } else {
                    $table->dropIndex('coupons_is_active_index');
                }
            });
        }
    }
};
