<?php

namespace dpodium\filemanager\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use dpodium\filemanager\FilemanagerAsset;

/**
 * Description of FileBrowse
 *
 * @author June
 */
class FileBrowse extends \yii\widgets\InputWidget {

    /**
     * @var boolean 
     * Set TRUE if allow upload multiple files
     */
    public $multiple = false;
    public $folderId = 0;

    /**
     * @var integer 
     * Only applied if $multiple = true
     */
    public $maxFileCount = 10;
    public $options = [];
    public $containerOptions = [];
    public $fileData = [
        'file_id',
        'host',
        'object_url',
        'url',
        'thumbnail_name',
        'src_file_name',
        'mime_type',
        'caption',
        'alt_text',
        'description',
        'dimension',
        'storage_id',
        'folder_id'
    ];
    protected $_browseClientFunc = '';

    public function init() {
        parent::init();

        $script = '';
        $view = $this->getView();
        FilemanagerAsset::register($view);

        if (empty($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $this->getId();
        }

        $fileContent = $this->renderFileContent();
        $opts = \yii\helpers\Json::encode([
                    'multiple' => $this->multiple,
                    'maxFileCount' => $this->maxFileCount,
                    'folderId' => $this->folderId,
        ]);
        $script .= "$('#{$this->containerOptions['id']}').filemanagerBrowse({$opts});";
        $this->_browseClientFunc = 'fmBrowseInit_' . hash('crc32', $script);
        $view->registerJs("var {$this->_browseClientFunc}=function(){\n{$script}\n};\n{$this->_browseClientFunc}();");

        echo Html::tag('div', $fileContent, ['id' => $this->containerOptions['id'], 'class' => 'fm-browse-selected']);
    }

    public function renderFileContent() {
        $attribute = $this->attribute;
        $input = $thumb = '';
        $selectedFileOpt = ['class' => 'fm-browse-input'];

        if ($this->model->$attribute) {
            $filesModel = \Yii::$app->getModule('filemanager')->models['files'];
            $file = $filesModel::findOne(['file_identifier' => $this->model->$attribute]);
        }

        if (isset($file) && $file) {
            $fileType = $file->mime_type;
            if ($file->dimension) {
                $src = $file->object_url . $file->thumbnail_name;
                $fileType = 'image';
            } else {
                $src = $file->object_url . $file->src_file_name;
            }
            $gridBox = new \dpodium\filemanager\components\GridBox([
                'owner' => $this,
                'src' => $src,
                'fileType' => $fileType,
                'toolArray' => [['tagType' => 'i', 'options' => ['class' => 'fa-icon fa fa-times fm-remove', 'title' => Yii::t('filemanager', 'Remove')]]],
                'thumbnailSize' => \Yii::$app->getModule('filemanager')->thumbnailSize
            ]);

            foreach ($this->fileData as $attribute) {
                $value = isset($file->$attribute) ? $file->$attribute : null;
                $input .= Html::input('input', "Filemanager[{$attribute}]", $value);
            }
            $thumb = $gridBox->renderGridBox();
        } else {
            $selectedFileOpt['value'] = '';
        }

        $fileView = Html::tag('div', $thumb, ['class' => 'fm-browse-selected-view']);
        $selectedFile = Html::activeInput('input', $this->model, $this->attribute, $selectedFileOpt);

        $buttonClass = empty($this->options['class']) ? 'btn btn-primary' : $this->options['class'];
        $browseButton = Html::label(Yii::t('filemanager', 'Browse'), Html::getInputId($this->model, $this->attribute), [
                    'class' => 'fm-btn-browse btn-browse ' . $buttonClass,
                    'data-url' => Url::to(['/filemanager/files/browse']),
                    'data-backdrop' => 'static',
                    'data-toggle' => 'modal',
                    'data-target' => '#fm-modal',
        ]);

        return $fileView . $browseButton . $selectedFile . $input;
    }

    public static function renderModal() {
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
        $modalBody = Html::tag('div', $tab, ['class' => 'modal-body', 'style' => 'min-height: 560px;']);
        $modalContent = Html::tag('div', $modalHeader . $modalBody, ['class' => 'modal-content']);
        $modalHtml = Html::tag('div', Html::tag('div', $modalContent, ['class' => 'modal-dialog modal-lg', 'role' => 'document']), [
                    'class' => 'fm-modal modal fade',
                    'id' => "fm-modal",
                    'tabindex' => "-1",
                    'role' => "dialog",
                    'aria-labelledby' => "fm-modal-label"
        ]);

        return $modalHtml;
    }

}
