<?php

/* @var $this yii\web\View */
/* @var $model dpodium\filemanager\models\Folders */

$this->title = Yii::t('filemanager', 'Create Folder');
$this->params['breadcrumbs'][] = ['label' => Yii::t('filemanager', 'Media Folder'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?php echo $this->title; ?></h1>
</div>
<div class="row">
    <div class="col-lg-5">
        <?php
        echo $this->render('_form', [
            'model' => $model,
        ]);
        ?>             
    </div>
</div>
