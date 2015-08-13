<?php

use dpodium\filemanager\FilemanagerAsset;
use kartik\widgets\ActiveForm;
use kartik\widgets\FileInput;
use yii\helpers\Url;

if ($uploadType == \dpodium\filemanager\components\Filemanager::TYPE_MODAL) {
    FilemanagerAsset::register($this);
}

$form = ActiveForm::begin([
            'action' => \Yii::$app->urlManager->createUrl(['/filemanager/files/upload']),
            'id' => 'fm-upload-form',
            'options' => ['enctype' => 'multipart/form-data'] // important
        ]);

if (!empty($folderArray)) {
    echo $form->field($model, 'folder_id')->dropDownList($folderArray);
}

$script = <<< SCRIPT
    function (event, params) {
        params.form.append('uploadType', {$uploadType});
        if(jQuery('select[name="Files[folder_id]"]').val() != undefined) {
            params.form.append('uploadTo', jQuery('select[name="Files[folder_id]"]').val());        
        } else {
            params.form.append('uploadTo', '{$model->folder_id}'); 
        }
    }
SCRIPT;
echo $form->field($model, 'upload_file[]')->widget(FileInput::classname(), [
    'options' => [
        'multiple' => $multiple,
        'accept' => implode(',', \Yii::$app->controller->module->acceptedFilesType)
    ],
    'pluginOptions' => [
        'uploadUrl' => Url::to(['/filemanager/files/upload']),
        'browseClass' => 'btn btn-sm btn-success',
        'uploadClass' => 'btn btn-sm btn-info',
        'removeClass' => 'btn btn-sm btn-danger',
        'maxFileCount' => $maxFileCount
    ],
    'pluginEvents' => [
        'filepreupload' => $script
    ]
]);

ActiveForm::end();
