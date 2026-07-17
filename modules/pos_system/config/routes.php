<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| POS System API Routes
|--------------------------------------------------------------------------
| Keys   = full URI that the browser requests (pos_system/api/...)
| Values = module-relative path WITHOUT the leading "pos_system/" segment,
|           because MX parse_routes() automatically prepends the module name.
|
| MX parse_routes() does:
|   return explode('/', $module . '/' . $val);
| So a value of 'api/Products_api/pos' becomes ['pos_system','api','Products_api','pos']
| which resolves to modules/pos_system/controllers/api/Products_api::pos()
*/

// ─── Auth ─────────────────────────────────────────────────────────────────
$route['pos_system/api/auth/login']  = 'api/Auth_api/login';
$route['pos_system/api/auth/logout'] = 'api/Auth_api/logout';
$route['pos_system/api/auth/me']     = 'api/Auth_api/me';

// ─── Branches ─────────────────────────────────────────────────────────────
$route['pos_system/api/branches']                                   = 'api/Branches_api/index';
$route['pos_system/api/branches/create']                            = 'api/Branches_api/create';
$route['pos_system/api/branches/(:num)']                            = 'api/Branches_api/show/$1';
$route['pos_system/api/branches/(:num)/update']                     = 'api/Branches_api/update/$1';
$route['pos_system/api/branches/(:num)/delete']                     = 'api/Branches_api/delete/$1';
$route['pos_system/api/branches/(:num)/staff']                      = 'api/Branches_api/staff/$1';
$route['pos_system/api/branches/(:num)/staff/assign']               = 'api/Branches_api/assign_staff/$1';
$route['pos_system/api/branches/(:num)/staff/(:num)/remove']        = 'api/Branches_api/remove_staff/$1/$2';

// ─── Products ─────────────────────────────────────────────────────────────
$route['pos_system/api/products/pos']                               = 'api/Products_api/pos';
$route['pos_system/api/products/categories']                        = 'api/Products_api/categories';
$route['pos_system/api/products/create']                            = 'api/Products_api/create';
$route['pos_system/api/products/barcode/(:any)']                    = 'api/Products_api/barcode/$1';
$route['pos_system/api/products/(:num)']                            = 'api/Products_api/show/$1';
$route['pos_system/api/products/(:num)/update']                     = 'api/Products_api/update/$1';
$route['pos_system/api/products/(:num)/delete']                     = 'api/Products_api/delete/$1';
$route['pos_system/api/products']                                   = 'api/Products_api/index';

// ─── Sessions ─────────────────────────────────────────────────────────────
$route['pos_system/api/sessions/current']                           = 'api/Sessions_api/current';
$route['pos_system/api/sessions/open']                              = 'api/Sessions_api/open';
$route['pos_system/api/sessions/(:num)/close']                      = 'api/Sessions_api/close/$1';
$route['pos_system/api/sessions/(:num)/cash-in']                    = 'api/Sessions_api/cash_in/$1';
$route['pos_system/api/sessions/(:num)/cash-out']                   = 'api/Sessions_api/cash_out/$1';
$route['pos_system/api/sessions/(:num)']                            = 'api/Sessions_api/show/$1';
$route['pos_system/api/sessions']                                   = 'api/Sessions_api/index';

// ─── Sales ────────────────────────────────────────────────────────────────
$route['pos_system/api/sales/create']                               = 'api/Sales_api/create';
$route['pos_system/api/sales/sync']                                 = 'api/Sales_api/sync';
$route['pos_system/api/sales/(:num)/refund']                        = 'api/Sales_api/refund/$1';
$route['pos_system/api/sales/(:num)']                               = 'api/Sales_api/show/$1';
$route['pos_system/api/sales']                                      = 'api/Sales_api/index';

// ─── Payments ─────────────────────────────────────────────────────────────
$route['pos_system/api/payments/methods']                           = 'api/Payments_api/methods';
$route['pos_system/api/payments/mobile-money']                      = 'api/Payments_api/initiate_mobile_money';
$route['pos_system/api/payments/mobile-money/(:any)/status']        = 'api/Payments_api/mobile_money_status/$1';
$route['pos_system/api/payments/callback/mpesa']                    = 'api/Payments_api/callback_mpesa';
$route['pos_system/api/payments/callback/airtel']                   = 'api/Payments_api/callback_airtel';
$route['pos_system/api/payments/callback/mtn/(:any)']               = 'api/Payments_api/callback_mtn/$1';
$route['pos_system/api/payments/callback/telebirr']                 = 'api/Payments_api/callback_telebirr';

// ─── Customers ────────────────────────────────────────────────────────────
$route['pos_system/api/customers/search']                           = 'api/Customers_api/search';
$route['pos_system/api/customers/create']                           = 'api/Customers_api/create';
$route['pos_system/api/customers/(:num)/update']                    = 'api/Customers_api/update/$1';
$route['pos_system/api/customers/(:num)/delete']                    = 'api/Customers_api/delete/$1';
$route['pos_system/api/customers/(:num)/history']                   = 'api/Customers_api/history/$1';
$route['pos_system/api/customers/(:num)/redeem']                    = 'api/Customers_api/redeem/$1';
$route['pos_system/api/customers/(:num)']                           = 'api/Customers_api/show/$1';
$route['pos_system/api/customers']                                  = 'api/Customers_api/index';

// ─── Inventory ────────────────────────────────────────────────────────────
$route['pos_system/api/inventory/low-stock']                        = 'api/Inventory_api/low_stock';
$route['pos_system/api/inventory/expiring']                         = 'api/Inventory_api/expiring';
$route['pos_system/api/inventory/movements']                        = 'api/Inventory_api/movements';
$route['pos_system/api/inventory/valuation']                        = 'api/Inventory_api/valuation';
$route['pos_system/api/inventory/adjust']                           = 'api/Inventory_api/adjust';
$route['pos_system/api/inventory/transfer']                         = 'api/Inventory_api/transfer';
$route['pos_system/api/inventory/receive']                          = 'api/Inventory_api/receive';
$route['pos_system/api/inventory/sync_warehouse']                   = 'api/Inventory_api/sync_warehouse';
$route['pos_system/api/inventory']                                  = 'api/Inventory_api/index';

// ─── Reports ─────────────────────────────────────────────────────────────
$route['pos_system/api/reports/daily-sales']                        = 'api/Reports_api/daily_sales';
$route['pos_system/api/reports/products']                           = 'api/Reports_api/products';
$route['pos_system/api/reports/cashiers']                           = 'api/Reports_api/cashiers';
$route['pos_system/api/reports/profit-loss']                        = 'api/Reports_api/profit_loss';
$route['pos_system/api/reports/payments']                           = 'api/Reports_api/payments';
$route['pos_system/api/reports/hourly']                             = 'api/Reports_api/hourly';
$route['pos_system/api/reports/categories']                         = 'api/Reports_api/categories';
$route['pos_system/api/reports/sessions']                           = 'api/Reports_api/sessions';
$route['pos_system/api/reports/customers']                          = 'api/Reports_api/customers';
$route['pos_system/api/reports/tax']                                = 'api/Reports_api/tax';
$route['pos_system/api/reports/branches']                           = 'api/Reports_api/branches';
$route['pos_system/api/reports/inventory']                          = 'api/Reports_api/inventory';
$route['pos_system/api/reports/ledger']                             = 'api/Reports_api/ledger';

// ─── Config / Profiles ────────────────────────────────────────────────────
$route['pos_system/api/config/profiles/create']                     = 'api/Config_api/profile_create';
$route['pos_system/api/config/profiles/(:num)/update']              = 'api/Config_api/profile_update/$1';
$route['pos_system/api/config/profiles/(:num)/delete']              = 'api/Config_api/profile_delete/$1';
$route['pos_system/api/config/profiles/(:num)/assign']              = 'api/Config_api/profile_assign_user/$1';
$route['pos_system/api/config/profiles/(:num)/unassign']            = 'api/Config_api/profile_unassign_user/$1';
$route['pos_system/api/config/profiles/(:num)']                     = 'api/Config_api/profile_show/$1';
$route['pos_system/api/config/profiles']                            = 'api/Config_api/profiles';
$route['pos_system/api/config/price-lists']                         = 'api/Config_api/price_lists';
$route['pos_system/api/config/item-groups']                         = 'api/Config_api/item_groups';
$route['pos_system/api/config/customer-groups']                     = 'api/Config_api/customer_groups';
$route['pos_system/api/config/print-templates']                     = 'api/Config_api/print_templates';
$route['pos_system/api/config/payment-methods']                     = 'api/Config_api/payment_methods';
$route['pos_system/api/config']                                     = 'api/Config_api/index';

// ─── Restaurant ──────────────────────────────────────────────────────────
$route['pos_system/api/restaurant/tables']                          = 'api/Restaurant_api/tables';
$route['pos_system/api/restaurant/kot/(:num)/status']               = 'api/Restaurant_api/kot_status/$1';
$route['pos_system/api/restaurant/kot']                             = 'api/Restaurant_api/kot';

// ─── Invoices ────────────────────────────────────────────────────────────
$route['pos_system/api/invoices/generate']                          = 'api/Invoice_api/generate';
$route['pos_system/api/invoices/sale/(:num)']                       = 'api/Invoice_api/by_sale/$1';
$route['pos_system/api/invoices/(:num)/cancel']                     = 'api/Invoice_api/cancel/$1';
$route['pos_system/api/invoices/(:num)/credit-note']                = 'api/Invoice_api/credit_note/$1';
$route['pos_system/api/invoices/(:num)/push-perfex']                = 'api/Invoice_api/push_perfex/$1';
$route['pos_system/api/invoices/(:num)']                            = 'api/Invoice_api/show/$1';
$route['pos_system/api/invoices']                                   = 'api/Invoice_api/index';
