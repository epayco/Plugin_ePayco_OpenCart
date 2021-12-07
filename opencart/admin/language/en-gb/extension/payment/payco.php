<?php
// Heading
$_['heading_title']      = 'ePayco';
$_['text_payco']					= '<a href="https://epayco.co/" target="_blank"><img src="https://369969691f476073508a-60bf0867add971908d4f26a64519c2aa.ssl.cf5.rackcdn.com/logos/logo_epayco_200px.png" alt="ePayco" title="ePayco" style="max-width: 90px;" /></a>';
$_['text_extension'] = 'Extensions';

// Text 
$_['text_payment']       = 'Payment';
$_['text_edit']       	 = 'Configuration ePayco';
$_['text_info']          = '<b>This module allows you to accept secure payments through the ePayco payment platform</b>
					        <br>If the customer decides to pay for ePayco, the status of the order will change to ePayco Waiting for Payment
					        <br>When the payment is Accepted or Rejected ePayco sends a configuration to the store to change the status of the order.';
$_['text_success']       = 'Success: Modulo ePayco update succesfull!';

// Entry
$_['entry_title']       = 'Title:';
$_['entry_title_default']       = 'ePayco CheckOut (Credit, debit cards, and cash).';
$_['entry_title_description']       = 'Corresponds to the title that the user sees during the checkout.';
$_['entry_description']       = 'Description:';
$_['entry_description_default']       = 'ePayco Gateway (Credit, debit cards, and cash).';
$_['entry_description_description']       = 'Corresponds to the description that the user will see during the checkout';
$_['entry_merchant']     = 'P_CUST_ID_CLIENTE:';
$_['entry_merchant_description'] = 'Customer ID that identifies you in ePayco. You can find it in your customer panel in the configuration option.';
$_['entry_key']          = 'P_KEY:';
$_['entry_key_description'] = 'Key to sign the information sent and received from ePayco. You can find it in your customer panel in the configuration option.';
$_['entry_public_key']          = 'PUBLIC_KEY:';
$_['entry_public_key_description'] = 'Key to authenticate and consume ePayco services, provided in your customer panel in the option.';
$_['entry_checkout_type']          = 'Checkout Type:';
$_['entry_checkout_type_description'] = '(Onpage Checkout, the user to pay remains on the site) or (Standart Checkout, the user to pay is redirected to the ePayco gateway)';
$_['entry_comision']     = '% Comisión ePayco:';
$_['entry_valor_comision'] ='Valor Comisión Payco:';
$_['entry_callback']     = 'URL Response:';
$_['entry_callback_description']     = 'Answer url to confirm payments from ePayco';
$_['entry_confirmation']     = 'URL Confirmation:';
$_['entry_confirmation_description']     = 'Url of the store where ePayco confirms the payment';
$_['entry_test']         = 'Test Mode:';
$_['entry_test_description']     = 'Enable payment sending in test mode';
$_['entry_total']        = 'Total:<br /><span class="help">The checkout total the order must reach before this payment method becomes active</span>';
$_['entry_initial_order_status'] = 'Initial Order Status:';
$_['entry_initial_order_status_description'] = 'Select the status of the order that will be applied when starting the payment of the order';
$_['entry_final_order_status'] = 'Final Order Status:';
$_['entry_final_order_status_description'] = 'Select the status of the order that will be applied when accepting and confirming the payment of the order';
$_['entry_geo_zone']     = 'Geo Zone:';
$_['entry_status']       = 'Enable/Disable:';
$_['entry_status_description'] = 'Enable ePayco Checkout' ;
$_['entry_sort_order']   = 'Order:';

// Error 
$_['error_permission']   = 'Alert: You do not have permission to modify the ePayco module!';
$_['error_title']        = 'Title Required!';
$_['error_description']  = 'Description Required!';
$_['error_merchant']     = 'P_CUST_ID_CLIENTE Required!';
$_['error_key']          = 'P_KEY Required!';
$_['error_public_key']          = 'PUBLIC_KEY Required!';
$_['error_callback']          = 'URL Response Required!';
$_['error_confirmation']          = 'URL Confirmation Required!';
?>