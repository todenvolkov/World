<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model,'id'); ?>
		<?php echo $form->textField($model,'id',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'id_customer'); ?>
		<?php echo $form->textField($model,'id_customer',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'id_cart'); ?>
		<?php echo $form->textField($model,'id_cart',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'id_address_delivery'); ?>
		<?php echo $form->textField($model,'id_address_delivery',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'payment'); ?>
		<?php echo $form->textField($model,'payment',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'conversion_rate'); ?>
		<?php echo $form->textField($model,'conversion_rate',array('size'=>13,'maxlength'=>13)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'module'); ?>
		<?php echo $form->textField($model,'module',array('size'=>60,'maxlength'=>255)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'shipping_number'); ?>
		<?php echo $form->textField($model,'shipping_number',array('size'=>32,'maxlength'=>32)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'total_discounts'); ?>
		<?php echo $form->textField($model,'total_discounts',array('size'=>17,'maxlength'=>17)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'total_paid'); ?>
		<?php echo $form->textField($model,'total_paid',array('size'=>17,'maxlength'=>17)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'total_paid_real'); ?>
		<?php echo $form->textField($model,'total_paid_real',array('size'=>17,'maxlength'=>17)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'total_products'); ?>
		<?php echo $form->textField($model,'total_products',array('size'=>17,'maxlength'=>17)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'total_products_wt'); ?>
		<?php echo $form->textField($model,'total_products_wt',array('size'=>17,'maxlength'=>17)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'total_shipping'); ?>
		<?php echo $form->textField($model,'total_shipping',array('size'=>17,'maxlength'=>17)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'carrier_tax_rate'); ?>
		<?php echo $form->textField($model,'carrier_tax_rate',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'invoice_number'); ?>
		<?php echo $form->textField($model,'invoice_number',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'delivery_number'); ?>
		<?php echo $form->textField($model,'delivery_number',array('size'=>10,'maxlength'=>10)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'invoice_date'); ?>
		<?php echo $form->textField($model,'invoice_date'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'delivery_date'); ?>
		<?php echo $form->textField($model,'delivery_date'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'valid'); ?>
		<?php echo $form->textField($model,'valid',array('size'=>1,'maxlength'=>1)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'date_add'); ?>
		<?php echo $form->textField($model,'date_add'); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model,'date_upd'); ?>
		<?php echo $form->textField($model,'date_upd'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Search'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->