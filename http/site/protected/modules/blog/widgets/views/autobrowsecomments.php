




<?php if ($show_form): ?>

<div class="ask-question clearfix">
    <h3>Оставьте свой комментарий</h3>
    <div class="form-wrapper clearfix">
        <?php 
		$form = $this->beginWidget('CActiveForm', array(
        'action' => $this->controller->createUrl('/blog/ajax/addComment'),
        'id' => 'comment-form',
        'enableClientValidation' => true,
        'htmlOptions'=>array('class'=>"clearfix jqtransform"),
    )); ?>  
			<?php echo CHtml::hiddenField('PostComment[redirectTo]', $redirectTo);?>
            <?php echo CHtml::hiddenField('PostComment[post_id]', $post_id);?>
            <?php $isAuth = Yii::app()->user->isAuthenticated(); ?>
            <div class="left-form">
                <div class="form-row placeholder inFields">
                    <label for="YourName" style="opacity: 1; "><span>*</span>Ваше имя</label>
                    <input type="text" name="PostComment[name]" id="YourName" value="<?=$isAuth?(Yii::app()->user->getState('nick_name')):""?>" <?=$isAuth?'readonly="1"':""?>>
                </div>
                <div class="form-row placeholder inFields">
                    <label for="EMail" style="opacity: 1; ">Email</label>
                    <input type="text" name="PostComment[email]" id="EMail" value="<?=$isAuth?(Yii::app()->user->getState('email')):""?>" <?=$isAuth?'readonly="1"':""?>>
                </div>
            </div>
            <div class="right-form">
                <div class="form-row placeholder inFields">
                    <label for="Commennt" style="opacity: 1; "><span>*</span> Текст сообщения</label>
                    <textarea name="PostComment[text]" id="Commennt"></textarea>
                </div>
            </div>
            <?php if(!Yii::app()->user->isAuthenticated()): ?>
            <div class="left-form">
            	<div class="form-row placeholder inFields">
                    <?php $this->widget('CCaptcha', array(                    
                    'captchaAction'  => '/blog/ajax/captcha',           
                    'showRefreshButton' => false,
                    'clickableImage'    => true,
					'imageOptions'	=> array('style'=>'float:right', 'class'=>'captcha', 'title'=>'Кликните, что-бы обновить капчу'),         
                )); ?>
                </div>
            </div>
            <div class="right-form">
            	<div class="form-row placeholder inFields">
                    <label for="VCode" style="opacity: 1; "><span>*</span>Код проверки</label>
                    <input type="text" name="PostComment[verifyCode]" id="VCode" style="width:269px;">
                </div>
            </div>
            <?php endif; ?>
            <div class="form-row">
                <?php
                echo CHtml::ajaxSubmitButton('отправить', CHtml::normalizeUrl(array('/blog/ajax/addComment')), 
					 array(
					   'data'=>'js:jQuery(this).parents("form").serialize()',               
					   'success'=>
								  'function(response){
									  	if(response.result==true){
											alert("Спасибо!\nВаше сообщение добавлено.");
											window.location.reload();
										}else{
											alert(response.data);
										}
										return false;
								   }',
						'error'=>'function(response){ alert(response.data) }',
						'dataType'=>'json',  
				 
					 ), 
					 array(
						'id'=>'ajaxSubmit', 
						'name'=>'ajaxSubmit',
						'class'=>'more',
					 )); 
				?>
            </div>
        <?php $this->endWidget(); ?>
    </div>
</div>

<?php endif; ?>

<div class="comment">
    
</div>

<script language="javascript">
    function activate_autobrowse(){
		//$('.comment').autobrowse('flush');
        $('.comment').autobrowse(
            {
                url: function (offset)
                {
                    return "<?=Yii::app()->createUrl("/blog/ajax/getComments")?>?postId=<?=$post_id?>&page=OFFSET".replace(/OFFSET/, 1+Math.round(offset/10));
                },
                template: function (response)
                {
                    var markup='';
                    for (var i=0; i<response.length; i++)
                    {
                        markup += '<div class="answer clearfix" style="opacity:0;-moz-opacity: 0;filter: alpha(opacity=0);"><span class="tip"></span>';
                        markup += '<div class="image"></div>';
                        markup += '<div class="text">';
                        markup += '<h3>'+response[i].name+' @ '+response[i].creation_date+'</h3>';
                        markup += '<p>'+response[i].text+'</p>';
                        markup += '</div></div>';
                    }
                    return markup;
                },
                itemsReturned: function (response) { return response.length; },
                max: <?=$max?>,
                sensitivity: 30//,
                //useCache: true
                //finished: function () { $(this).append('<p style="text-align:center">-----------------</p>') }
            }
        );
    }
</script>