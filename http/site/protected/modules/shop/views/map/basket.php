<?php $this->pageTitle = "Basket";?>
<?php $this->renderPartial("_basket_menu"); ?>
<div class="basket_content">
    <h2>ВАШ <strong>ЗАКАЗ</strong></h2>
    <div class="info clearfix staticpage">
        <div id="basket_content" class="content">
        <?php $this->widget('application.modules.yupe.widgets.YFlashMessages'); ?>
        <?php $this->renderPartial("_basket-table",array("data"=>$data)); ?>
        </div>
    </div>
</div>