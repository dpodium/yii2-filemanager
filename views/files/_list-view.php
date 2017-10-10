<?php

use yii\helpers\Html;
use kartik\grid\GridView;
?>
<div class="row">
    <div class="col-xs-12">
        <?php
        $thumbnailSize = \Yii::$app->controller->module->thumbnailSize;
        $public_path = \Yii::$app->controller->module->public_path;
        $gridWidth = $thumbnailSize[0] + 40;
        echo GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => false,
            'export' => false,
            'pjax' => true,
            'columns' => [
                [
                    'class' => 'yii\grid\DataColumn',
                    'format' => 'html',
                    'contentOptions' => ['style' => "width: {$gridWidth}px; text-align: center;"],
                    'value' => function ($model) use ($public_path) {
                        $fileType = $model->mime_type;
                        if ($model->dimension) {
                            $fileType = 'image';
                        }
                        return dpodium\filemanager\components\Filemanager::getThumbnail($fileType, $model->getFileUrl(true, $public_path));
                    }
                ],
                'caption',
                'alt_text',
                'description',
                [
                    'class' => 'yii\grid\DataColumn',
                    'attribute' => 'mime_type',
                    'filter' => false
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update} {delete}',
                    'buttons' => [
                        'update' => function ($url, $model) use ($view) {
                            $url = \Yii::$app->urlManager->createUrl(['/filemanager/files/update', 'id' => $model['file_id'], 'view' => $view]);
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, ['title' => Yii::t('filemanager', 'Edit')]);
                        },
                        'delete' => function ($url, $model) {
                            $url = \Yii::$app->urlManager->createUrl(['/filemanager/files/delete', 'id' => $model['file_id']]);
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, [
                                        'title' => Yii::t('filemanager', 'Delete'),
                                        'data-method' => 'post',
                                        'data-confirm' => Yii::t('filemanager', 'Confirm delete {src_file_name} ?', ['src_file_name' => $model['src_file_name']])
                            ]);
                        },
                    ],
                ],
            ],
        ]);
        ?>
    </div>
</div>
