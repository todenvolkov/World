<?php $this->pageTitle = $page->title; $this->breadcrumbs = $this->getBreadCrumbs();?>
<?php
$title = $page->title;
if(strpos($title," ")!==false){
	$title = wordwrap($title,(strlen($title)/2),"|");
	$title = explode("|",$title,2);
	$title = str_replace("|"," ",$title);
	$title = $title[0]." <strong>".$title[1]."</strong>";
}
?>
<h2><?php echo $title;?></h2>
<div class="info clearfix staticpage">
        <div class="content">
			<p><?php echo $page->body;?></p>
        </div>
</div>