<?php

use dpodium\filemanager\components\Filemanager;
use yii\helpers\Html;

$this->title = Yii::t('filemanager', 'Upload New Media');
$this->params['breadcrumbs'][] = ['label' => Yii::t('filemanager', 'Media Gallery'), 'url' => ['files/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="page-header">
    <h1><?php echo $this->title; ?></h1>
</div>
<div class="row">
    <div class="col-xs-12">
        <?php if (empty($folderArray)) { ?>
            <div class="alert alert-warning" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                <i class="fa fa-info-circle"></i>
                <?php
                echo Yii::t('filemanager', 'Click {link} to create a folder to upload media', [
                    'link' => Html::a(Yii::t('filemanager', 'here'), ['/filemanager/folders/create'], ['target' => '_blank'])
                ]);
                ?>
            </div>
            <?php
        } else {
            echo $this->render('_file-input', [
                'model' => $model,
                'folderArray' => $folderArray,
                'uploadType' => Filemanager::TYPE_FULL_PAGE,
                'multiple' => true,
                'maxFileCount' => 10
            ]);
        }
        ?>                
    </div>
</div>
<div class="space-24"></div>
<div class="row">
    <div class="col-xs-12 edit-uploaded-container"></div>
</div>