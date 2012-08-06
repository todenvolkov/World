<?php $this->pageTitle = Yii::t('gallery', 'Галереи изображений'); ?>

<?php
$this->breadcrumbs = array(
    Yii::t('gallery', 'Галереи изображений') => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    Yii::t('gallery', 'Изменить'),
);

$this->menu = array(
    array('label' => Yii::t('feedback', 'Управление галереями'), 'url' => array('admin')),
    array('label' => Yii::t('feedback', 'Добавить галерею'), 'url' => array('create')),
    array('label' => Yii::t('feedback', 'Редактирование галереи'), 'url' => array('update', 'id' => $model->id)),
    array('label' => Yii::t('feedback', 'Просмотреть галерею'), 'url' => array('view', 'id' => $model->id))
);
?>

<h1><?php echo Yii::t('feedback', 'Редактировать галерею')?>
    «<?php echo $model->name; ?>»</h1>

<?php echo $this->renderPartial('_form', array('model' => $model)); ?>
<div class="row-fluid control-group">
    <div class="span7">
        <a class="btn btn-success" data-toggle="modal" href="#upload_modal_window">Загрузить фотографии</a>
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
        <a href="#" class="btn" data-dismiss="modal">Отмена</a>
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
        <li>
          <a href="#" class="thumbnail cover">
            <img imgid="1" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="2" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="3" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="4" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="5" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail cover">
            <img imgid="6" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="7" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="8" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="9" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
        <li>
          <a href="#" class="thumbnail">
            <img imgid="10" src="http://placehold.it/145x120" alt="">
          </a>
        </li>
    </ul>
</span>

<script language="javascript" type="application/javascript">
function updateImages(){
	var all_photo = $('#all_photo').html();
	$('#cover_selector').html(all_photo);
	$('#all_photo_control').fadeOut(500).slideUp('slow').html(all_photo).fadeIn(500).slideDown('slow');
	
	$('#all_photo_control .thumbnails .thumbnail img').each( function() { 
			$(this).parent('a').append('<div style="margin-top:7px; text-align:right"><a class="delete" rel="tooltip" data-original-title="Удалить" href="/image/default/delete/id/'+$(this).attr('imgid')+'"><i class="icon-trash"></i></a></div>');
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
				
				$(this).click(function() { $(href).modal('hide'); $('#Gallery_name').val($(this).attr('src')); } );
		 } );
	});
	/*==================================*/
	
	updateImages();
});
</script>