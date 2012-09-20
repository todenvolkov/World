<?php $this->pageTitle = Yii::t('gallery', 'Галереи изображений'); ?>
<style>
    #sortable { list-style-type: none; margin: 0; padding: 0;; }

</style>
<?php
$this->breadcrumbs = array(
    Yii::t('gallery', 'Галереи изображений') => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    Yii::t('gallery', 'Изменить'),
);

$this->menu = array(
    array('label' => Yii::t('gallery', 'Управление галереями'), 'url' => array('admin')),
    array('label' => Yii::t('gallery', 'Добавить галерею'), 'url' => array('create')),
    array('label' => Yii::t('gallery', 'Редактирование галереи'), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('gallery', 'Просмотреть галерею'), 'url' => array('view', 'id' => $model->id)),
    array('label' => Yii::t('gallery', 'Добавить изображение'), 'url' => array('addImage', 'galleryId' => $model->id))
);
?>

<h1><?php echo Yii::t('feedback', 'Редактировать галерею')?>
    «<?php echo $model->name; ?>»</h1>

<?php echo $this->renderPartial('_form', array('model' => $model)); ?>
<div class="row-fluid control-group">
    <div class="span7">
        <a class="btn btn-success" data-toggle="modal" href="#upload_modal_window">Добавить фотографии</a>
    </div>
</div>

<div class="modal hide fade" id="upload_modal_window">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3>Добавление фотографий</h3>
    </div>
    <div class="modal-body">
    	<div>
            AJAX Uploader
        </div>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Закрыть</a>
    </div>
</div>

<div class="modal hide fade" id="selector_modal_window">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3>Выберите обложку галереии</h3>
    </div>
    <div class="modal-body">
    	<div id="cover_selector" class="row">
            
        </div>
	</div>
    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal">Отмена</a>
    </div>
</div>


<div class="row-fluid control-group">
    <div class="span7" style="margin-left: 0;">
        <h3>Редактирование фотографий</h3>
        <p>Explore all your options with the various grid classes available to you. You can also mix and match different sizes.</p>
        <div class="hide" id="all_photo_control"></div>
    </div>
</div>

<span class="hide" id="all_photo">
    <ul class="thumbnails">
        <?php
        if($model->imagesCount > 0):
            $images = ImageToGallery::model()->with('image')->findAll(array(
                'condition' => 'galleryId = :galleryId',
                'params' => array(':galleryId' => $model->id),
                'order' => 't.sort ASC, t.creation_date DESC'
            ));

            foreach($images as $img):
                $sort[$img->image->id] = '<a class="thumbnail"><img imgid="'.$img->image->id.'" orig-src="'.$img->image->file.'" src="/timthumb.php?src='.urlencode($img->image->file).'&w=145&h=120" alt=""></a>';
            ?>
            <li>
                <?=$sort[$img->image->id]?>
            </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>


</span>

<?php $this->widget('zii.widgets.jui.CJuiSortable', array(
    'itemTemplate'=>'<li><a class="thumbnail" style="cursor: move;"><img imgid="{id}" orig-src="{content}"  src="/timthumb.php?src={content}&w=145&h=120" /></a></li>',
    'id'=>'sortable',
    'items'=>CHtml::listData($images, 'id', 'image.file'),
    'htmlOptions'=>array('class'=>'span7 thumbnails'),
)); ?>

<script language="javascript" type="application/javascript">
function updateImages(){
	var all_photo = $('#all_photo').html();
	$('#cover_selector').html(all_photo);
	//$('#all_photo_control').fadeOut(500).slideUp('slow').html(all_photo).fadeIn(500).slideDown('slow');
	
	$('#sortable .thumbnail img').each( function() {
			$(this).parent('a').append('<span style="position:absolute;top:7px;right:7px; background-color: rgba(0, 0, 0, 0.5); padding: 2px;"><a class="update" rel="tooltip" data-original-title="Редактировать" href="/image/default/update/id/'+$(this).attr('imgid')+'"><i class="icon-pencil icon-white"></i></a><br><a class="delete" rel="tooltip" data-original-title="Удалить" href="/image/default/delete/id/'+$(this).attr('imgid')+'"><i class="icon-trash icon-white"></i></a></span>');
	});
	
	
}

$(document).ready(function() {
	
	/*Add AJAX Support to bootstrap-modal
	=====================================*/
	$('[data-toggle="modal"]').click(function(e) {
		//e.preventDefault();
		var href = $(this).attr('href');
		/*if (href.indexOf('#') == 0) {
			$(href).modal('open');
		} else {
			$.get(href, function(data) {
				$('<div class="modal fade" >' + data + '</div>').modal();
			}).success(function() { $('input:text:visible:first').focus(); });
		}*/
		$('#cover_selector .thumbnails .thumbnail img').each( function() {
				
				$(this).click(function() { $(href).modal('hide'); $('#coverImg').attr("src","/timthumb.php?w=260&src="+$(this).attr('orig-src')); $('#Gallery_cover_id').val($(this).attr('imgid')); } );
		 } );
	});
	/*==================================*/
	
	updateImages();
});
</script>