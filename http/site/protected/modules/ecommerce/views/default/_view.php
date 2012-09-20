<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('id_customer')); ?>:</b>
	<?php echo CHtml::encode($data->id_customer); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('id_cart')); ?>:</b>
	<?php echo CHtml::encode($data->id_cart); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('id_address_delivery')); ?>:</b>
	<?php echo CHtml::encode($data->id_address_delivery); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('payment')); ?>:</b>
	<?php echo CHtml::encode($data->payment); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('conversion_rate')); ?>:</b>
	<?php echo CHtml::encode($data->conversion_rate); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('module')); ?>:</b>
	<?php echo CHtml::encode($data->module); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('shipping_number')); ?>:</b>
	<?php echo CHtml::encode($data->shipping_number); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('total_discounts')); ?>:</b>
	<?php echo CHtml::encode($data->total_discounts); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('total_paid')); ?>:</b>
	<?php echo CHtml::encode($data->total_paid); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('total_paid_real')); ?>:</b>
	<?php echo CHtml::encode($data->total_paid_real); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('total_products')); ?>:</b>
	<?php echo CHtml::encode($data->total_products); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('total_products_wt')); ?>:</b>
	<?php echo CHtml::encode($data->total_products_wt); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('total_shipping')); ?>:</b>
	<?php echo CHtml::encode($data->total_shipping); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('carrier_tax_rate')); ?>:</b>
	<?php echo CHtml::encode($data->carrier_tax_rate); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('invoice_number')); ?>:</b>
	<?php echo CHtml::encode($data->invoice_number); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('delivery_number')); ?>:</b>
	<?php echo CHtml::encode($data->delivery_number); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('invoice_date')); ?>:</b>
	<?php echo CHtml::encode($data->invoice_date); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('delivery_date')); ?>:</b>
	<?php echo CHtml::encode($data->delivery_date); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('valid')); ?>:</b>
	<?php echo CHtml::encode($data->valid); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('date_add')); ?>:</b>
	<?php echo CHtml::encode($data->date_add); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('date_upd')); ?>:</b>
	<?php echo CHtml::encode($data->date_upd); ?>
	<br />

	*/ ?>

</div>