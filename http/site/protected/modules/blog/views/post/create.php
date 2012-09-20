<?php $this->pageTitle = "Basket";?>
<?php $this->renderPartial("application.modules.shop.views.map._basket_menu"); ?>
<div class="basket_content">
    <h2>новый <strong>пост</strong></h2>
    <div class="info clearfix staticpage">
        <div id="basket_content" class="content">
        <?php $this->widget('application.modules.yupe.widgets.YFlashMessages'); ?>
        
        <div class="post-form">
            <form action="/" method="post">
              <div class="form-row">
                <label for="title" >Заголовок поста</label>
                <input type="text" name="title" id="title" />
              </div>
              <div class="form-row">
                <label for="company-name" >Название компании</label>
                <input type="text" name="company-name" id="company-name" />
              </div>
              <div class="form-row">
                <label for="work">Вид работ</label>
                <input type="text" name="work" id="work" />
              </div>
              <div class="form-row jqtransform project-manager">
                <label for="subject">Менеджер проекта</label>
                <select id="subject">
                  <option value="0">Фамилия имя</option>
                  <option value="1">Option 1</option>
                  <option value="2">Option 2</option>
                  <option value="3">Option 3</option>
                </select>
              </div>
              <div class="form-row jqtransform project-designer">
                <label for="subject-topic">Дизайнер проекта</label>
                <select id="subject-topic">
                  <option value="0">Фамилия имя</option>
                  <option value="1">Option 1</option>
                  <option value="2">Option 2</option>
                  <option value="3">Option 3</option>
                </select>
              </div>
              <div class="form-row jqtransform project-marketer">
                <label>Маркетолог проекта</label>
                <select>
                  <option value="0">Фамилия имя</option>
                  <option value="1">Option 1</option>
                  <option value="2">Option 2</option>
                  <option value="3">Option 3</option>
                </select>
              </div>
              <div class="form-row">
                <div class="styles">
                    <a href="#" class="bold" title="Click For Text to be Bold"></a>
                    <a href="#" class="italic" title="Click For Text to be italic"></a>
                    <a href="#" class="underline" title="Click For Underline"></a>
                </div>
                <div class="formatting">
                    <a href="#" class="numbering" title="Click for Numbering"></a>
                    <a href="#" class="bullets" title="Click for Bullets"></a>
                    <a href="#" class="quotes" title="Click for Quotes"></a>
                </div>
              </div>
              <div class="form-row">
                <label for="message"><strong>Содержание</strong>/описание работ</label>
                <textarea name="message" id="message" rows="5" cols="25"></textarea>
              </div>
              <div class="form-row topic jqtransform">
                <label>Тема(Рубрика)</label>
                <select>
                  <option value="0">Фамилия имя</option>
                  <option value="1">Option 1</option>
                  <option value="2">Option 2</option>
                  <option value="3">Option 3</option>
                </select>
                <select>
                  <option value="0">Фамилия имя</option>
                  <option value="1">Option 1</option>
                  <option value="2">Option 2</option>
                  <option value="3">Option 3</option>
                </select>        
              </div>
              <div class="form-row tags">
                <label for="tags">Тэги</label>
                <input type="text" name="tags" id="tags" />
              </div>
              <div class="form-row post-photo">
                <label>Фото</label>
                <div class="photo-upload">
                    <img src="images/img-01.jpg" width="75" height="56" alt="" />
                    <span class="delete"></span>
                </div>
                <div class="post-photo-cmt">
                    <label for="comments">комментарии</label>
                    <textarea name="comments" id="comments" rows="1" cols="25"></textarea>            
                </div>
                <div class="aligning">
                    <span>выравнивание</span>
                    <div><a href="#" class="justify active"></a></div>
                    <div><a href="#" class="right"></a></div>
                    <div><a href="#" class="center"></a></div>
                    <div><a href="#" class="left"></a></div>
                </div>
                <div class="upload">
                    <span>выравнивание</span>
                    <input type="file" name="file" lang="ru" />
                </div>
                <div class="top-face">
                    <span>Лучшие фотографии лица</span>
                    <figure>
                        <img src="images/top-face.png" width="125" height="96" alt="" />
                        <figcaption>TopFace Photos <span>комментарии</span></figcaption>
                    </figure>
                    
                </div>
              </div>
              <div class="form-row row-submit">
                <input type="submit" name="publish" id="publish" value="Опубликовать" />
                <input type="submit" name="preview" id="preview" value="Предварительный просмотр" />
                <input type="submit" name="drafts" id="drafts" value="В черновики" />
                <input type="submit" name="resets" id="resets" value="Сброс" />
              </div>
            </form>
          </div>
        	<script type="text/javascript">
				$(document).ready(function() {
					$(".jqtransform").jqTransform();
				});
			</script> 
        </div>
    </div>
</div>