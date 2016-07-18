<!--<p style="color:red"><?php echo $alert_comision; ?></p>!-->
<form method="post" action="<?php echo $action; ?>" id="payment">

  <input type="hidden" name="p_cust_id_cliente"  value="<?php echo $p_cust_id_cliente; ?>">
  <input type="hidden" name="p_key"  value="<?php echo $p_key; ?>">
  <input type="hidden" name="p_id_invoice" id="refVenta"  value="<?php echo $p_id_invoice ?>">
  <input type="hidden" name="p_description"  value="<?php echo $p_description; ?>">
  <input type="hidden" name="p_amount" id="valor"  value="<?php echo $p_amount; ?>">
  <input type="hidden" name="p_tax"  value="<?php echo $p_tax; ?>">
  <input type="hidden" name="p_amount_base"  value="<?php echo $p_amount_base; ?>">
  <input type="hidden" name="p_billing_first_name" value="<?php echo $p_billing_first_name; ?>" />
  <input type="hidden" name="p_billing_last_name" value="<?php echo $p_billing_last_name; ?>" />
  <input type="hidden" name="p_billing_company" value="<?php echo $p_billing_company; ?>" />
  <input type="hidden" name="p_billing_address" value="<?php echo $p_billing_address; ?>" />
  <input type="hidden" name="p_billing_city" value="<?php echo $p_billing_city; ?>" />
  <input type="hidden" name="p_billing_state" value="<?php echo $p_billing_state; ?>" />
  <input type="hidden" name="p_billing_zip" value="<?php echo $p_billing_zip; ?>" />
  <input type="hidden" name="p_billing_country" value="<?php echo $p_billing_country; ?>" />
  <input type="hidden" name="p_customer_ip" value="<?php echo $p_customer_ip; ?>" />
  <input type="hidden" name="p_email"  value="<?php echo  $p_email;?>">
  <input type="hidden" name="p_currency_code" id="moneda"  value="<?php echo $p_currency_code; ?>">
  <input type="hidden" name="p_test_request"  value="<?php echo $p_test_request; ?>">
  <input type="hidden" name="p_url_response"  value="<?php echo $p_url_response; ?>">
  <input type="hidden" name="p_url_confirmation"  value="<?php echo $p_url_confirmation; ?>">
  <input type="hidden" name="p_signature"  value="<?php echo $p_signature; ?>">
  <input name="p_extra1" type="hidden" value="0">
  <input name="p_extra2" type="hidden" value="0">
  <input name="p_extra3" type="hidden" value="0">
   <div class="buttons">
      <div class="pull-right">
        <input type="submit" value="<?php echo $button_confirm; ?>" class="btn btn-primary" />
      </div>
    </div>
</form>