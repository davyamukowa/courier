<?php
defined('BASEPATH') or exit('No direct script access allowed');

$lang['pos_system']           = 'POS System';
$lang['pos_dashboard']        = 'POS Dashboard';
$lang['pos_terminal']         = 'POS Terminal';
$lang['pos_branches']         = 'Branches';
$lang['pos_branch']           = 'Branch';
$lang['pos_products']         = 'Products';
$lang['pos_product']          = 'Product';
$lang['pos_sales_orders']     = 'Sales Orders';
$lang['pos_sales_order']      = 'Sales Order';
$lang['pos_inventory']        = 'Inventory';
$lang['pos_reports']          = 'Reports';
$lang['pos_settings']         = 'POS Settings';
$lang['pos_sessions']         = 'Sessions';
$lang['pos_session']          = 'Session';
$lang['pos_sales']            = 'Sales';
$lang['pos_sale']             = 'Sale';
$lang['pos_customers']        = 'Customers';
$lang['pos_customer']         = 'Customer';
$lang['pos_payments']         = 'Payments';
$lang['pos_payment']          = 'Payment';

// Session
$lang['pos_open_session']     = 'Open Session';
$lang['pos_close_session']    = 'Close Session';
$lang['pos_opening_float']    = 'Opening Float';
$lang['pos_closing_float']    = 'Closing Float';
$lang['pos_expected_cash']    = 'Expected Cash';
$lang['pos_actual_cash']      = 'Actual Cash';
$lang['pos_cash_difference']  = 'Difference';

// Cart / Sale
$lang['pos_add_to_cart']      = 'Add to Cart';
$lang['pos_remove_item']      = 'Remove';
$lang['pos_subtotal']         = 'Subtotal';
$lang['pos_discount']         = 'Discount';
$lang['pos_tax']              = 'Tax';
$lang['pos_total']            = 'Total';
$lang['pos_amount_paid']      = 'Amount Paid';
$lang['pos_change']           = 'Change';
$lang['pos_complete_sale']    = 'Complete Sale';
$lang['pos_print_receipt']    = 'Print Receipt';
$lang['pos_new_sale']         = 'New Sale';

// Payment methods
$lang['pos_cash']             = 'Cash';
$lang['pos_card']             = 'Card / POS';
$lang['pos_mpesa']            = 'M-Pesa';
$lang['pos_airtel']           = 'Airtel Money';
$lang['pos_mtn']              = 'MTN Mobile Money';
$lang['pos_telebirr']         = 'Telebirr';

// Misc
$lang['pos_no_branch_assigned'] = 'You are not assigned to any branch. Please contact your administrator.';
$lang['pos_search_products']    = 'Search products or scan barcode...';
$lang['pos_low_stock']          = 'Low Stock';
$lang['pos_out_of_stock']       = 'Out of Stock';
$lang['pos_receipt_number']     = 'Receipt #';
$lang['pos_refund']             = 'Refund';
$lang['pos_void_sale']          = 'Void Sale';
$lang['pos_today_sales']        = 'Today\'s Sales';
$lang['pos_today_revenue']      = 'Today\'s Revenue';

// Inventory view
$lang['pos_sync_warehouse']          = 'Sync from Warehouse';
$lang['pos_sync_complete']           = 'Sync complete: %s items updated';
$lang['pos_all_stock']               = 'All Stock';
$lang['pos_all_sources']             = 'All Sources';
$lang['pos_warehouse']               = 'Warehouse';
$lang['pos_pos_only']                = 'POS Only';
$lang['pos_selling_price']           = 'Selling Price';
$lang['pos_warehouse_stock']         = 'Warehouse Qty';
$lang['pos_pos_stock']               = 'POS Qty';
$lang['pos_source']                  = 'Source';
$lang['pos_actions']                 = 'Actions';
$lang['pos_sku']                     = 'SKU';
$lang['pos_category']                = 'Category';
$lang['pos_adjust_stock']            = 'Adjust Stock';
$lang['pos_current_qty']             = 'Current Quantity';
$lang['pos_adjustment_type']         = 'Adjustment Type';
$lang['pos_add_stock']               = 'Add Stock';
$lang['pos_remove_stock']            = 'Remove Stock';
$lang['pos_set_quantity']            = 'Set Quantity';
$lang['pos_quantity']                = 'Quantity';
$lang['pos_reason']                  = 'Reason';
$lang['pos_save_adjustment']         = 'Save Adjustment';

// Settings view
$lang['pos_general']                 = 'General';
$lang['pos_default_currency']        = 'Default Currency';
$lang['pos_tax_inclusive']           = 'Prices Include Tax';
$lang['pos_tax_inclusive_help']      = 'When enabled, entered prices are treated as tax-inclusive';
$lang['pos_low_stock_threshold']     = 'Low Stock Alert Threshold';
$lang['pos_allow_negative_stock']    = 'Allow Negative Stock';
$lang['pos_receipt_settings']        = 'Receipt Settings';
$lang['pos_receipt_header']          = 'Receipt Header';
$lang['pos_receipt_footer']          = 'Receipt Footer';
$lang['pos_auto_print_receipt']      = 'Auto-Print Receipt on Sale';
$lang['pos_payment_methods']         = 'Enabled Payment Methods';
$lang['pos_bank_transfer']           = 'Bank Transfer';
$lang['pos_credit']                  = 'Credit / Pay Later';
$lang['pos_mobile_money_config']     = 'Mobile Money Configuration';
$lang['pos_warehouse_integration']   = 'Warehouse Integration';
$lang['pos_enable_warehouse_sync']   = 'Sync Stock with Warehouse';
$lang['pos_warehouse_sync_help']     = 'When enabled, selling a product deducts from the linked warehouse';
$lang['pos_accounting_integration']  = 'Accounting Integration';
$lang['pos_enable_accounting_sync']  = 'Post Sales to Accounting';
$lang['pos_accounting_sync_help']    = 'Automatically posts GL journal entries when a sale is completed';
$lang['pos_revenue_account_key']     = 'Revenue Account Key';
$lang['pos_cash_account_key']        = 'Cash Asset Account Key';
$lang['pos_account_key_help']        = 'Enter the key_name from the accounting module\'s chart of accounts';
$lang['pos_session_settings']        = 'Session Settings';
$lang['pos_require_session']         = 'Require Open Session to Sell';
$lang['pos_session_timeout_hours']   = 'Auto-Close Sessions After (hours)';

// Branches
$lang['pos_add_branch']              = 'Add Branch';
$lang['pos_edit_branch']             = 'Edit Branch';
$lang['pos_branch_name']             = 'Branch Name';
$lang['pos_branch_code']             = 'Branch Code';
$lang['pos_address']                 = 'Address';
$lang['pos_city']                    = 'City';
$lang['pos_country']                 = 'Country';
$lang['pos_phone']                   = 'Phone';
$lang['pos_email']                   = 'Email';
$lang['pos_timezone']                = 'Timezone';
$lang['pos_tax_pin']                 = 'TAX PIN / TIN';
$lang['pos_tax_pin_help']            = 'Tax identification number issued by your country\'s revenue authority';
$lang['pos_tax_authority']           = 'Tax Authority PIN';
$lang['pos_deactivate_branch_confirm'] = 'Are you sure you want to deactivate this branch?';
$lang['pos_all_branches']            = 'All Branches';

// Profiles (Config Engine)
$lang['pos_profiles']                = 'POS Profiles';
$lang['pos_profile']                 = 'POS Profile';
$lang['pos_add_profile']             = 'Add Profile';
$lang['pos_edit_profile']            = 'Edit Profile';
$lang['pos_profile_name']            = 'Profile Name';
$lang['pos_profiles_help']           = 'POS Profiles control behaviour per branch or user. Resolution order: User → Branch → Global.';
$lang['pos_profile_invoice_mode']    = 'Invoice Mode';
$lang['pos_profile_allow_rate_change']   = 'Rate Change';
$lang['pos_profile_allow_discount']  = 'Discount Change';
$lang['pos_deactivate_profile_confirm'] = 'Deactivate this profile?';

// Profile behaviour toggles
$lang['pos_hide_images']                     = 'Hide Product Images';
$lang['pos_hide_unavailable_items']          = 'Hide Unavailable Items';
$lang['pos_auto_add_item_to_cart']           = 'Auto-Add Item to Cart (single result)';
$lang['pos_validate_stock_on_save']          = 'Validate Stock Before Saving';
$lang['pos_print_receipt_on_order_complete'] = 'Auto-Print Receipt on Sale';
$lang['pos_ignore_pricing_rule']             = 'Ignore Pricing Rules';
$lang['pos_allow_rate_change']               = 'Allow Price/Rate Change';
$lang['pos_allow_discount_change']           = 'Allow Discount Change';
$lang['pos_set_grand_total_to_default_mop']  = 'Set Grand Total as Default Payment Amount';
$lang['pos_allow_partial_payment']           = 'Allow Partial Payments';
$lang['pos_auto_create_invoice']             = 'Auto-Create Invoice on Sale';
$lang['pos_behaviour_settings']             = 'Behaviour Settings';

// Invoice settings
$lang['pos_invoice_settings']        = 'Invoice Settings';
$lang['pos_action_on_new_invoice']   = 'Action on New Invoice';
$lang['pos_invoice_action_ask']      = 'Always Ask';
$lang['pos_invoice_action_new']      = 'Always New Invoice';
$lang['pos_invoice_action_continue'] = 'Continue Current Invoice';
$lang['pos_apply_discount_on']       = 'Apply Discount On';
$lang['pos_net_total']               = 'Net Total (before tax)';
$lang['pos_grand_total']             = 'Grand Total (after tax)';
$lang['pos_invoice_prefix']          = 'Invoice Number Prefix';
$lang['pos_price_list']              = 'Price List';
$lang['pos_print_template']          = 'Print Template';

// Cash handling
$lang['pos_cash_handling']           = 'Cash Handling';
$lang['pos_enable_cash_rounding']    = 'Enable Cash Rounding';
$lang['pos_cash_rounding_increment'] = 'Rounding Increment';
$lang['pos_cash_rounding_type']      = 'Rounding Method';
$lang['pos_rounding_nearest']        = 'Round to Nearest';
$lang['pos_rounding_up']             = 'Always Round Up';
$lang['pos_rounding_down']           = 'Always Round Down';
$lang['pos_disable_rounded_total']   = 'Disable Rounded Total Display';

// Filtering engine
$lang['pos_filtering_engine']        = 'Filtering Engine';
$lang['pos_allowed_item_groups']     = 'Allowed Item Groups';
$lang['pos_allowed_customer_groups'] = 'Allowed Customer Groups';

// Invoices page
$lang['pos_currency']                = 'Currency';
$lang['pos_invoices']                = 'Invoices';
$lang['pos_invoice_number']          = 'Invoice #';
$lang['pos_perfex_invoice']          = 'Perfex Invoice';
$lang['pos_invoice_details']         = 'Invoice Details';
$lang['pos_no_invoices_found']       = 'No invoices found for the selected period.';
$lang['pos_credit_note']             = 'Credit Note';

// Payment methods
$lang['pos_payment_method']          = 'Payment Method';
$lang['pos_add_payment_method']      = 'Add Payment Method';
$lang['pos_edit_payment_method']     = 'Edit Payment Method';
$lang['pos_pm_type']                 = 'Type';
$lang['pos_pm_provider']             = 'Provider';
$lang['pos_pm_code']                 = 'Code';
$lang['pos_pm_default']              = 'Default';
$lang['pos_perfex_gateway']          = 'Perfex Gateway';
$lang['pos_perfex_gateway_help']     = 'Link this POS method to a Perfex payment gateway for invoice sync';
$lang['pos_map_gateway_help']        = 'Link POS payment methods to these gateways for Perfex invoice payments';
$lang['pos_perfex_gateways_active']  = 'Active Perfex Gateways';
$lang['pos_allow_in_returns']        = 'Allow in Returns/Refunds';
$lang['pos_account_key']             = 'Accounting Ledger Key';
$lang['pos_sort_order']              = 'Sort Order';
