<?php

use kartik\editable\Editable;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('filemanager', 'Media Property');
$this->params['breadcrumbs'][] = ['label' => Yii::t('filemanager', 'Media Gallery'), 'url' => ['files/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?php if ($uploadType === 'full-page') { ?>
    <div class="page-header">
        <h1><?php echo Html::encode($this->title); ?></h1>
    </div>
<?php } ?>
<div class="row">
    <div class="col-lg-4 col-md-4 col-xs-12">
        <?php if ($uploadType === 'full-page') { ?>
            <div style="text-align: center;">
                <?php
                $fileType = $model->mime_type;
                if ($model->dimension) {
                    $fileType = 'image';
                }
                echo dpodium\filemanager\components\Filemanager::getThumbnail($fileType, $model->object_url . $model->src_file_name, "250px", "250px");
                ?>
            </div>
        <?php } ?>
    </div>
    <div class="<?php echo ($uploadType === 'full-page') ? 'col-lg-8 col-md-8 col-xs-12' : 'col-xs-12'; ?>">
        <?php
        $tagDisplayValue = json_encode($model->tags);
        $objectUrl = $model->object_url . $model->src_file_name;
        $btnCopyToClipboard = Html::button('<i class="fa fa-copy"></i>', [
                    'class' => 'btn-copy btn-xs',
                    'title' => Yii::t('filemanager', 'Copy')
        ]);
        echo DetailView::widget([
            'model' => $model,
            'attributes' => [
                'src_file_name',
                'thumbnail_name',
                [
                    'label' => $model->folder->getAttributeLabel('storage'),
                    'value' => $model->folder->storage,
                ],
                [
                    'label' => $model->folder->getAttributeLabel('category'),
                    'value' => $model->folder->category,
                ],
                'mime_type',
                'dimension',
                [
                    'label' => $model->getAttributeLabel('object_url'),
                    'format' => 'raw',
                    'value' => Html::tag('div', $objectUrl, ['class' => 'copy-object-url', 'style' => 'float: left']) . $btnCopyToClipboard,
                ],
                [
                    'label' => $model->getAttributeLabel('caption'),
                    'format' => 'raw',
                    'value' => Editable::widget([
                        'model' => $model,
                        'attribute' => 'caption',
                        'asPopover' => false,
                    ])
                ],
                [
                    'label' => $model->getAttributeLabel('alt_text'),
                    'format' => 'raw',
                    'value' => Editable::widget([
                        'model' => $model,
                        'attribute' => 'alt_text',
                        'asPopover' => false,
                    ])
                ],
                [
                    'label' => $model->getAttributeLabel('description'),
                    'format' => 'raw',
                    'value' => Editable::widget([
                        'model' => $model,
                        'attribute' => 'description',
                        'asPopover' => false,
                        'submitOnEnter' => false,
                        'inputType' => Editable::INPUT_TEXTAREA,
                    ])
                ],
                [
                    'label' => $model->getAttributeLabel('tags'),
                    'format' => 'raw',
                    'value' => Editable::widget([
                        'model' => $model,
                        'attribute' => 'tags',
                        'asPopover' => false,
                        'submitOnEnter' => false,
                        'inputType' => Editable::INPUT_SELECT2,
                        'displayValue' => implode(', ', $editableTagsLabel),
                        'options' => [
                            'data' => $tags,
                            'options' => [
                                'multiple' => true,
                                'placeholder' => Yii::t('filemanager', 'Enter or select tags...'),
                            ],
                            'pluginOptions' => [
                                'tags' => true,
                                'maximumInputLength' => 32
                            ],
                        ],
                        'pluginEvents' => [
                            'editableReset' => "function(event) {
                                jQuery('#' + event.currentTarget.id + ' .kv-editable-parent .input-group select').select2('val', {$tagDisplayValue});
                            }"
                        ]
                    ])
                ],
                'created_at:dateTime',
                'updated_at:dateTime',
            ]
        ]);
        ?>
    </div>
</div>
<?php if ($uploadType === 'full-page') { ?>
    <div class="row">
        <div class="col-xs-12" style="text-align: center; padding: 10px; border-top: 1px solid #E5E5E5">
            <?php
            $cancelUrl = \Yii::$app->urlManager->createUrl(['/filemanager/files/index']);
            if (empty($view)) {
                $cancelUrl = \Yii::$app->urlManager->createUrl(['/filemanager/files/index', 'view' => 'grid']);
            }
            echo Html::a(Yii::t('filemanager', 'Cancel'), $cancelUrl, ['class' => 'btn btn-primary']);
            ?>
        </div>
    </div>
    <?php
}