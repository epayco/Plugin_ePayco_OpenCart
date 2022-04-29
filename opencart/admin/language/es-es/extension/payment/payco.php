<?php
// Heading
$_['heading_title']      = 'ePayco';

// Text 
$_['text_payco']		 = '<a target="_BLANK" href="https://epayco.com/"><img src="view/image/payment/payco.png" alt="ePayco Commerce Platform" title="ePayco Commerce Platform" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_extension'] 	 = 'Extensiones';
$_['text_success']       = 'Éxito: Modulo ePayco actualizado exitosamente!';
$_['text_edit']       	 = 'Configuración ePayco';

// Entry
$_['entry_status']       = 'Estados';
$_['entry_title']       = 'Título:';
$_['entry_title_default']       = 'ePayco CheckOut (Tarjetas de crédito,debito,efectivo).';
$_['entry_title_description']       = 'Corresponde al titulo que el usuario ve durante el checkout.';
$_['entry_description']       = 'Descripción:';
$_['entry_description_default']       = 'ePayco Gateway (Tarjetas de crédito,debito,efectivo).';
$_['entry_description_description']       = 'Corresponde a la descripción que verá el usuaro durante el checkout';
$_['entry_merchant']     = 'P_CUST_ID_CLIENTE:';
$_['entry_merchant_description'] = 'ID de cliente que lo identifica en ePayco. Lo puede encontrar en su panel de clientes en la opción configuración.';
$_['entry_key']          = 'P_KEY:';
$_['entry_key_description'] = 'Llave para firmar la información enviada y recibida de ePayco. Lo puede encontrar en su panel de clientes en la opción configuración.';
$_['entry_public_key']          = 'Public_KEY:';
$_['entry_public_key_description'] = 'LLave para autenticar y consumir los servicios de ePayco, Proporcionado en su panel de clientes en la opción configuración.';
$_['entry_callback']     = 'URL De Respuesta:';
$_['entry_callback_description']     = 'Url de respuesta para confirmar los pagos desde ePayco';
$_['entry_confirmation']     = 'URL de confirmación:';
$_['entry_confirmation_description']     = 'Url de la tienda donde ePayco confirma el pago';
$_['entry_test']         = 'Modo Pruebas:';
$_['entry_test_description']     = 'Habilitar el envio de pago en modo pruebas';
$_['entry_languaje']         = 'Lenguaje:';
$_['entry_languaje_description']     = 'Selecciona el lenguaje del checkout';
$_['text_es']         = 'Es';
$_['text_en']     = 'En';
$_['entry_checkout_type']          = 'Tipo Checkout:';
$_['entry_checkout_type_description'] = '(Onpage Checkout, el usuario a pagar permanece en el sitio) o (Standart Checkout, el usuario a pagar se redirige a la puerta de enlace de ePayco)';
$_['entry_order_status'] = 'Estado Inicial del Pedido:';
$_['entry_initial_order_status_description'] = 'Seleccione el estado del pedido que se aplicará a la hora de iniciar el pago de la orden';
$_['entry_final_order_status'] = 'Estado Final del Pedido:';
$_['entry_final_order_status_description'] = 'Seleccione el estado del pedido que se aplicará a la hora de aceptar y confirmar el pago de la orden';


$_['entry_total']        = 'Total';

$_['entry_geo_zone']     = 'Geo Zone:';

$_['entry_sort_order']   = 'Orden:';

$_['entry_status_description'] = 'Habilitar ePayco Checkout' ; 



// Help
$_['help_total']         = 'The checkout total the order must reach before this payment method becomes active.';

// Error 
$_['error_permission']   = 'Alerta: Usted no tiene permisos para modificar el modulo ePayco!';
$_['error_title']     = 'Titulo Requerido!';
$_['error_description']          = 'Descripción Requerido!';
$_['error_merchant']     = 'P_CUST_ID_CLIENTE Requerido!';
$_['error_key']          = 'P_KEY Requerido!';
$_['error_callback']          = 'URL De Respuesta Requerido!';
$_['error_confirmation']          = 'URL de confirmación Requerido!';
?>