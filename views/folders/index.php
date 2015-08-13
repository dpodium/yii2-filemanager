<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('filemanager', 'Folders');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="folders-index">
    <h1><?php echo Html::encode($this->title); ?></h1>
    <p><?php echo Html::a(Yii::t('filemanager', 'Create Folder'), ['create'], ['class' => 'btn btn-success']); ?></p>
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
                    if($model->storage == 'local') {
                        $path = Yii::getAlias('@common') . '/' . $model['path'];                        
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
