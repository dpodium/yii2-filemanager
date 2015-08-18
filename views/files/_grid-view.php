<?php

use dpodium\filemanager\widgets\Gallery;
use dpodium\filemanager\components\Filemanager;
use yii\helpers\ArrayHelper;

$colClass = ($uploadType == Filemanager::TYPE_MODAL) ? 'col-sm-6 col-xs-12' : 'col-xs-12';
?>
<?php if ($uploadType == Filemanager::TYPE_MODAL) { ?>
    <div class="row">
        <div class="col-xs-12">
            <?php
            $folders = \Yii::$app->getModule('filemanager')->models['folders'];
            $folderArray = ArrayHelper::merge(['' => Yii::t('filemanager', 'All')], ArrayHelper::map($folders::find()->all(), 'folder_id', 'category'));
            echo $this->renderAjax('_search', [
                'model' => $model,
                'folderArray' => $folderArray
            ]);
            ?>

        </div>
    </div>
    <?php
}
?>
<div class="row">
    <div class="<?php echo $colClass; ?>">
        <div class="fm-gallery clearfix" <?php echo ($uploadType == Filemanager::TYPE_MODAL) ? 'style="max-height: 550px; overflow-y: auto;"' : ''; ?>>
            <?php
            echo Gallery::widget([
                'dataProvider' => $dataProvider,
                'viewFrom' => $viewFrom
            ]);
            ?>
            <div class="fm-gallery-loading fm-loading hide">
                <i class="fa fa-spinner fa-pulse"></i>
            </div>
        </div>
    </div>
    <?php if ($uploadType == Filemanager::TYPE_MODAL) { ?>
        <div class="fm-file-info-loading col-sm-6 col-xs-12 hidden-xs">
            <div class="fm-loading hide">
                <i class="fa fa-spinner fa-pulse"></i>
            </div>
        </div>
        <div class="col-sm-6 col-xs-12 hidden-xs">
            <div class="fm-file-info">
                <h2 style="text-align: center;"><?php echo Yii::t('filemanager', 'Click a file to view info.'); ?></h2>
            </div>
        </div>
    <?php } ?>
</div>
<?php
if ($uploadType == Filemanager::TYPE_MODAL) {
    $script = <<< SCRIPT
        var renderAjax = true, scrollAtBottom = false;
        jQuery(".modal-body .fm-gallery").on('scroll', function (e) {
            var elem = jQuery(e.currentTarget);
            var ajaxUrl = jQuery('.fm-section #fm-next-page a').attr('href');

            if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight() && ajaxUrl != undefined) {
                scrollAtBottom = true;
            } else {
                return false;
            }

            if (renderAjax === true && scrollAtBottom === true) {
                renderAjax = false;
                scrollAtBottom = false;
                jQuery('.fm-gallery-loading').removeClass('hide');
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'html',
                    complete: function () {
                        renderAjax = true;
                        jQuery('.fm-gallery-loading').addClass('hide');
                    },
                    success: function (html) {
                        $('.fm-gallery .fm-section:last #fm-next-page').remove();
                        $('.fm-gallery').append(html);
                    }
                });
            }
        });
SCRIPT;
    $this->registerJs($script);
}
