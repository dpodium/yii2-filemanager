<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model dpodium\filemanager\models\Folders */

$this->title = $model->category;
$this->params['breadcrumbs'][] = ['label' => Yii::t('filemanager', 'Media Folder'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header clearfix">
    <h1><?php echo Html::encode($this->title); ?></h1>
</div>
<div class="space-10"></div>
<div class="clearfix">
    <p>
        <?php echo Html::a('Update', ['update', 'id' => $model->folder_id], ['class' => 'btn btn-primary']); ?>
        <?php
        echo Html::a('Delete', ['delete', 'id' => $model->folder_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]);
        ?>
    </p>
</div>
<div class="row">
    <div class="col-lg-5">
        <?php
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'category',
                'path',
                'storage',
            ],
        ]);
        ?>
    </div>
</div>