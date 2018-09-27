<?php

namespace dpodium\filemanager\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use dpodium\filemanager\FilemanagerEditorAsset;


/**
 * Description of FileBrowse
 *
 * @author June
 */
class FileBrowseEditor extends \yii\base\Widget {

    /**
     * @var boolean
     * Set TRUE if allow upload multiple files
     */
    public $multiple = false;

    /**
     * @var int
     * Folder id to which files get uploaded
     */
    public $folderId = 0;

    /**
     * @var integer
     * Only applied if $multiple = true
     */
    public $maxFileCount = 10;

    public function init() {
        parent::init();

        $view = $this->getView();
        FilemanagerEditorAsset::register($view);

        Yii::$app->i18n->translations['filemanager*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'basePath' => "@dpodium/filemanager/messages"
        ];

    }

    public function run() {
        $btnClose = Html::button(Html::tag('span', '&times', ['aria-hidden' => 'true']), [
                    'class' => 'close',
                    'data-dismiss' => 'modal',
                    'aria-label' => "Close"
        ]);
        $modalTitle = Html::tag('h4', Yii::t('filemanager', 'Media Gallery'), ['class' => 'modal-title', 'id' => 'fm-modal-label']);
        $modalHeader = Html::tag('div', $btnClose . $modalTitle, ['class' => 'modal-header']);

        $tab = Tabs::widget([
                    'items' => [
                        [
                            'label' => Yii::t('filemanager', 'Media Gallery'),
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
        $modalBody = Html::tag('div', $tab, ['class' => 'modal-body', 'style' => 'min-height: 560px;']);
        $modalContent = Html::tag('div', $modalHeader . $modalBody, ['class' => 'modal-content']);
        $modalHtml = Html::tag('div', Html::tag('div', $modalContent, ['class' => 'modal-dialog modal-lg', 'role' => 'document']), [
                    'class' => 'fm-modal modal fade',
                    'id' => "fm-modal",
                    'tabindex' => "-1",
                    'role' => "dialog",
                    'aria-labelledby' => "fm-modal-label",
                    'data-multiple' => $this->multiple,
                    'data-maxfilecount' => $this->maxFileCount,
                    'data-folderid' => $this->folderId,
        ]);

        return $modalHtml;
    }

}
