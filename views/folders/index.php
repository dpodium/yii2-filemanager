<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('filemanager', 'Media Folder');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header clearfix">
    <div class="pull-left">
        <h1><?php echo Html::encode($this->title); ?></h1>
    </div>
    <div class="pull-right">
        <?php echo Html::a(Yii::t('filemanager', 'Create Folder'), ['create'], ['class' => 'btn btn-success']); ?>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <?php
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $model,
            'export' => false,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'category',
                [
                    'class' => 'kartik\grid\DataColumn',
                    'attribute' => 'path',
                    'value' => function ($model) {
                        $path = $model->path;
                        if ($model->storage == 'local') {
                            $path = Yii::getAlias('@webroot') . '/' . $model['path'];
                        }
                        return $path;
                    }
                ],
                'storage',
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]);
        ?>
    </div>
</div>
