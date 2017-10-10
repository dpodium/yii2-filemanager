<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model dpodium\filemanager\models\Folders */

$this->title = Yii::t('filemanager', 'Update Folder').': ' . ' ' . $model->category;
$this->params['breadcrumbs'][] = ['label' => Yii::t('filemanager', 'Media Folder'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->category, 'url' => ['view', 'id' => $model->folder_id]];
$this->params['breadcrumbs'][] = Yii::t('filemanager', 'Update');
?>
<div class="page-header clearfix">
    <h1><?php echo Html::encode($this->title); ?></h1>
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
