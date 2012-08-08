<?php
$this->breadcrumbs=array(
	'Orders'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Order', 'url'=>array('index')),
	array('label'=>'Create Order', 'url'=>array('create')),
	array('label'=>'Update Order', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Order', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Order', 'url'=>array('admin')),
);
?>

<h1>View Order #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'id_customer',
		'id_cart',
		'id_address_delivery',
		'payment',
		'conversion_rate',
		'module',
		'shipping_number',
		'total_discounts',
		'total_paid',
		'total_paid_real',
		'total_products',
		'total_products_wt',
		'total_shipping',
		'carrier_tax_rate',
		'invoice_number',
		'delivery_number',
		'invoice_date',
		'delivery_date',
		'valid',
		'date_add',
		'date_upd',
	),
)); ?>
