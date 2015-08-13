<?php

use dpodium\filemanager\widgets\Gallery;
use dpodium\filemanager\components\Filemanager;

$colClass = ($uploadType == Filemanager::TYPE_MODAL) ? 'col-sm-6 col-xs-12' : 'col-xs-12';
?>
<div class="row">
    <div class="<?php echo $colClass; ?>">
        <div class="fm-gallery clearfix">
            <?php
            echo Gallery::widget([
                'dataProvider' => $dataProvider,
                'viewFrom' => $viewFrom
            ]);
            ?>
        </div>
        <div class="fm-gallery-loading fm-loading hide">
            <i class="fa fa-spinner fa-pulse"></i>
        </div>
    </div>
    <?php if ($uploadType == Filemanager::TYPE_MODAL) { ?>
        <div class="fm-file-info-loading col-sm-6 col-xs-12">
            <div class="fm-loading hide">
                <i class="fa fa-spinner fa-pulse"></i>
            </div>
        </div>
        <div class="fm-file-info col-sm-6 col-xs-12">
            <h2 style="text-align: center;"><?php echo Yii::t('filemanager', 'Click a file to view info.'); ?></h2>
        </div>
    <?php } ?>
</div>
