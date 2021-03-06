<?php
$this->breadcrumbs=array(
	'Orders'=>array('index'),
	'Manage',
);

$this->menu=array(
	array('label'=>'List Order', 'url'=>array('index')),
	array('label'=>'Create Order', 'url'=>array('create')),
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('order-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<h1>Manage Orders</h1>

<p>
You may optionally enter a comparison operator (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
or <b>=</b>) at the beginning of each of your search values to specify how the comparison should be done.
</p>

<?php echo CHtml::link('Advanced Search','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<?php $this->widget('YCustomGridView', array(
	'id'=>'order-grid',
	'dataProvider'=>$model->search(),
    'itemsCssClass' => ' table table-condensed',
	//'filter'=>$model,
	'columns'=>array(
		'id',
        'invoice_number',
		'id_cart',
		'payment',
		'conversion_rate',
        'date_add',
        'valid',
		/*
		'module',
		'shipping_number',
		'total_discounts',
		'total_paid',
		'total_paid_real',
		'total_products',
		'total_products_wt',
		'total_shipping',
		'carrier_tax_rate',

		'delivery_number',
		'invoice_date',
		'delivery_date',

		,
		'date_upd',
		*/
		array(
			'class'=>'CButtonColumn',
		),
	),
)); ?>
