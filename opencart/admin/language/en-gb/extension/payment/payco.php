<?php
// Heading
$_['heading_title']      = 'ePayco';

// Text
$_['text_payco']		 				= '<a target="_BLANK" href="https://epayco.com/"><img src="view/image/payment/payco.png" alt="ePayco Commerce Platform" title="ePayco Commerce Platform" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_extension']     = 'Extensions';
$_['text_success']       = 'Success: You have modified ePayco payment module!';
$_['text_edit']          = 'Edit ePayco';

// Entry
$_['entry_status']       = 'Status';
$_['entry_title']       = 'Title:';
$_['entry_title_default']       = 'ePayco CheckOut (Credit, debit cards, and cash).';
$_['entry_description']       = 'Description:';
$_['entry_description_default']       = 'ePayco Gateway (Credit, debit cards, and cash).';
$_['entry_merchant']     = 'P_CUST_ID_CLIENTE:';
$_['entry_merchant_description'] = 'Customer ID that identifies you in ePayco. You can find it in your customer panel in the configuration option.';
$_['entry_key']          = 'P_KEY:';
$_['entry_key_description'] = 'Key to sign the information sent and received from ePayco. You can find it in your customer panel in the configuration option.';
$_['entry_public_key']          = 'PUBLIC_KEY:';
$_['entry_public_key_description'] = 'Key to authenticate and consume ePayco services, provided in your customer panel in the option.';
$_['entry_callback']     = 'URL Response:';
$_['entry_callback_description']     = 'Answer url to confirm payments from ePayco';
$_['entry_confirmation']     = 'URL Confirmation:';
$_['entry_confirmation_description']     = 'Url of the store where ePayco confirms the payment';
$_['entry_test']         = 'Test Mode:';
$_['entry_test_description']     = 'Enable payment sending in test mode';
$_['entry_languaje']         = 'Language:';
$_['entry_languaje_description']     = 'Select the language of the checkout';
$_['text_es']         = 'Es';
$_['text_en']     = 'En';
$_['entry_checkout_type']          = 'Checkout Type:';
$_['entry_checkout_type_description'] = '(Onpage Checkout, the user to pay remains on the site) or (Standart Checkout, the user to pay is redirected to the ePayco gateway)';
$_['entry_order_status'] = 'Initial Order Status';
$_['entry_initial_order_status_description'] = 'Select the status of the order that will be applied when starting the payment of the order';
$_['entry_final_order_status'] = 'Final Order Status:';
$_['entry_final_order_status_description'] = 'Select the status of the order that will be applied when accepting and confirming the payment of the order';

$_['entry_total']        = 'Total';

$_['entry_geo_zone']     = 'Geo Zone';

$_['entry_sort_order']   = 'Sort Order';

// Help
$_['help_total']         = 'The checkout total the order must reach before this payment method becomes active.';

// Error
$_['error_permission']   = 'Warning: You do not have permission to modify payment ePayco!';
$_['error_merchant']     = 'P_CUST_ID_CLIENTE Required!';
$_['error_key']          = 'P_KEY Required!';
$_['error_public_key']   = 'PUBLIC_KEY Required!';
