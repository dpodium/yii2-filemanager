<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
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
                        echo $form->field($model, 'tag')->textInput([
                            'placeholder' => Yii::t('filemanager', 'Search by tag...')
                        ]);
                        ?>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <?php
                        echo $form->field($model, 'keywords')->textInput([
                            'placeholder' => Yii::t('filemanager', 'Search by source file name, caption, alternative text or description...')
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
