<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
?>
<div id="fm-search-parent" class="panel-group collapse in" aria-expanded="true">
    <div class="fm-accordion panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a class="collapse-toggle" href="#fm-search" data-toggle="collapse" data-parent="#fm-search-parent" aria-expanded="false">
                    <?php echo Yii::t('filemanager', 'Advanced Search'); ?>
                </a>
            </h4>
        </div>
        <div id="fm-search" class="panel-collapse collapse in" aria-expanded="false">
            <div class="panel-body">
                <?php $form = ActiveForm::begin(['method' => 'get', 'id' => 'fm-search-form']); ?>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php echo $form->field($model, 'folder_id')->dropDownList($folderArray); ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php echo $form->field($model, 'mime_type')->dropDownList(ArrayHelper::merge(['' => Yii::t('filemanager', 'All')], \Yii::$app->controller->module->acceptedFilesType)); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $form->field($model, 'tags')->widget(Select2::classname(), [
                            'data' => empty($model->tags) ? [] : $model->tags,
                            'options' => [
                                'multiple' => true,
                                'placeholder' => Yii::t('filemanager', 'Search by tag...'),
                            ],
                            'pluginOptions' => [
                                'tags' => true,
                                'maximumInputLength' => 20
                            ],
                        ]);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $form->field($model, 'keywords')->textInput([
                            'placeholder' => Yii::t('filemanager', 'Search by File Name, Title, Alternative Text or Description...')
                        ]);
                        ?>
                    </div>
                </div>
                <?php
                echo Html::submitButton(Yii::t('filemanager', 'Search'), ['class' => 'btn btn-primary pull-right']);
                ActiveForm::end();
                ?>
            </div>
        </div>
    </div>
</div>
