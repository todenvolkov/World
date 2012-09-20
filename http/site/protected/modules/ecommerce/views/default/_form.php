<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'order-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'id_customer'); ?>
		<?php echo $form->textField($model,'id_customer',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'id_customer'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'id_cart'); ?>
		<?php echo $form->textField($model,'id_cart',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'id_cart'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'id_address_delivery'); ?>
		<?php echo $form->textField($model,'id_address_delivery',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'id_address_delivery'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'payment'); ?>
		<?php echo $form->textField($model,'payment',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'payment'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'conversion_rate'); ?>
		<?php echo $form->textField($model,'conversion_rate',array('size'=>13,'maxlength'=>13)); ?>
		<?php echo $form->error($model,'conversion_rate'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'module'); ?>
		<?php echo $form->textField($model,'module',array('size'=>60,'maxlength'=>255)); ?>
		<?php echo $form->error($model,'module'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'shipping_number'); ?>
		<?php echo $form->textField($model,'shipping_number',array('size'=>32,'maxlength'=>32)); ?>
		<?php echo $form->error($model,'shipping_number'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'total_discounts'); ?>
		<?php echo $form->textField($model,'total_discounts',array('size'=>17,'maxlength'=>17)); ?>
		<?php echo $form->error($model,'total_discounts'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'total_paid'); ?>
		<?php echo $form->textField($model,'total_paid',array('size'=>17,'maxlength'=>17)); ?>
		<?php echo $form->error($model,'total_paid'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'total_paid_real'); ?>
		<?php echo $form->textField($model,'total_paid_real',array('size'=>17,'maxlength'=>17)); ?>
		<?php echo $form->error($model,'total_paid_real'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'total_products'); ?>
		<?php echo $form->textField($model,'total_products',array('size'=>17,'maxlength'=>17)); ?>
		<?php echo $form->error($model,'total_products'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'total_products_wt'); ?>
		<?php echo $form->textField($model,'total_products_wt',array('size'=>17,'maxlength'=>17)); ?>
		<?php echo $form->error($model,'total_products_wt'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'total_shipping'); ?>
		<?php echo $form->textField($model,'total_shipping',array('size'=>17,'maxlength'=>17)); ?>
		<?php echo $form->error($model,'total_shipping'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'carrier_tax_rate'); ?>
		<?php echo $form->textField($model,'carrier_tax_rate',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'carrier_tax_rate'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'invoice_number'); ?>
		<?php echo $form->textField($model,'invoice_number',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'invoice_number'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'delivery_number'); ?>
		<?php echo $form->textField($model,'delivery_number',array('size'=>10,'maxlength'=>10)); ?>
		<?php echo $form->error($model,'delivery_number'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'invoice_date'); ?>
		<?php echo $form->textField($model,'invoice_date'); ?>
		<?php echo $form->error($model,'invoice_date'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'delivery_date'); ?>
		<?php echo $form->textField($model,'delivery_date'); ?>
		<?php echo $form->error($model,'delivery_date'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'valid'); ?>
		<?php echo $form->textField($model,'valid',array('size'=>1,'maxlength'=>1)); ?>
		<?php echo $form->error($model,'valid'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'date_add'); ?>
		<?php echo $form->textField($model,'date_add'); ?>
		<?php echo $form->error($model,'date_add'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'date_upd'); ?>
		<?php echo $form->textField($model,'date_upd'); ?>
		<?php echo $form->error($model,'date_upd'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->