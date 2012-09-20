<h2> <?php echo Shop::t('Products'); ?> </h2>
<?php 

$model = new Products();

$this->widget('YCustomGridView', array(
	'id'=>'products-grid',
	'dataProvider'=>$model->search(),
	'itemsCssClass' => ' table table-condensed',
	'columns'=>array(
		'title',
		'price',
		array(
			'class' => 'bootstrap.widgets.BootButtonColumn',
			'template' => '{view}{update}{delete} — {images}',
			'viewButtonUrl' => 'Yii::app()->createUrl("/shop/products/view",
			array("id" => $data->product_id))',
			'updateButtonUrl' => 'Yii::app()->createUrl("/shop/products/update",
			array("id" => $data->product_id))',
			'deleteButtonUrl' => 'Yii::app()->createUrl("/shop/products/delete",
			array("id" => $data->product_id))',
			'buttons' => array(
				
				'images' => array(
					'label' => Yii::t('ShopModule.shop', 'images'),
					'icon' => 'icon-picture',
					'url' => 'Yii::app()->createUrl("/shop/image/admin",
					array("product_id" => $data->product_id))',
				),
			),
			'htmlOptions' => array('style'=>'width:110px; text-align:center;'),
		),
	)
)
); 


echo CHtml::link(Shop::t('Create a new Product'), array('products/create'));
?>
