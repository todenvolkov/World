
<?php

	$this->breadcrumbs=array(
			Shop::t('Orders')=>array('admin'),
			Shop::t('Manage'),
			);
?>

<h2> <?php echo Shop::t('Orders'); ?> </h2>

<div class="btn-toolbar">
  <div class="btn-group">
 
    <a class="btn" href="#" rel="tooltip" data-original-title="<?=Shop::t('Create order')?>"><i class="icon-plus"></i></a>
    <a class="btn" href="#" rel="tooltip" data-original-title="<?=Shop::t('Show stats')?>"><i class="icon-signal"></i></a>
    <a class="btn" href="#"><i class="icon-align-right" rel="tooltip"></i></a>
    <a class="btn" href="#"><i class="icon-align-justify" rel="tooltip"></i></a>
  </div>
</div>

<?php 

$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'order-grid',
	'dataProvider'=>$model->search(),
	'itemsCssClass' => ' table table-condensed',
	'filter'=>$model,
	'columns'=>array(
		'order_id',
		'customer.address.firstname',
		'customer.address.lastname',
		array(
			'name' => 'ordering_date',
			'value' => 'date("M j, Y", $data->ordering_date)',
			'filter' => false
			),
		array(
			'name' => 'status',
			'value' => 'Shop::t($data->status)',
			'filter' => Order::statusOptions(),
			), 
		array(
			'class'=>'CButtonColumn', 
			'template' => '{view}',
		),

	),
)); ?>
