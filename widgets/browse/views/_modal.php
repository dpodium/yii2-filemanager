<?php

use yii\bootstrap\Tabs;
use yii\helpers\Html;
?>
<div class="fm-modal modal fade" id="fm-modal" tabindex="-1" role="dialog" aria-labelledby="fm-modal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="fm-modal-label"><?php echo Yii::t('filemanager', 'Media Gallery'); ?></h4>
            </div>
            <div class="modal-body" style="height: 450px; overflow-y: auto;">
                <?php
                echo Tabs::widget([
                    'items' => [
                        [
                            'label' => Yii::t('filemanager', 'Media Gallery'),
                            'active' => true,
                            'linkOptions' => [
                                'id' => 'fm-upload-tab',
                                'data-url' => \yii\helpers\Url::to(['/filemanager/files/upload-tab'])
                            ]
                        ],
                        [
                            'label' => Yii::t('filemanager', 'Library'),
                            'linkOptions' => [
                                'id' => 'fm-library-tab',
                                'data-url' => \yii\helpers\Url::to(['/filemanager/files/library-tab'])
                            ]
                        ],
                    ],
                ]);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo Yii::t('filemanager', 'Cancel'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
