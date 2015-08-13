<?php

namespace dpodium\filemanager\widgets;

use yii\helpers\Html;
use yii\widgets\BaseListView;
use yii\helpers\BaseFileHelper;
use dpodium\filemanager\FilemanagerAsset;
use dpodium\filemanager\components\GridBox;

/**
 * Description of Gallery
 *
 * @author June
 */
class Gallery extends BaseListView {

    public $options = ['id' => 'fm-section', 'class' => 'fm-section'];
    public $layout = "{items}\n{pager}";
    public $viewFrom = 'full-page';
    public $gridBox = [];
    protected $_galleryClientFunc = '';

    /**
     * Initializes the grid view.
     * This method will initialize required property values and instantiate [[columns]] objects.
     */
    public function init() {
        parent::init();

        $script = '';
        $view = $this->getView();

        if (empty($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }

        $opts = \yii\helpers\Json::encode([
                    'viewFrom' => $this->viewFrom
        ]);
        $script .= "$('#{$this->options['id']}').filemanagerGallery({$opts});";
        $this->_galleryClientFunc = 'fmGalleryInit_' . hash('crc32', $script);
        $view->registerJs("var {$this->_galleryClientFunc}=function(){\n{$script}\n};\n{$this->_galleryClientFunc}();");
    }

    /**
     * Runs the widget.
     */
    public function run() {
        $view = $this->getView();
        FilemanagerAsset::register($view);
//        $view->registerJs("jQuery('.fm-gallery').jscroll();");

        parent::run();
    }

    public function renderItems() {
        if (empty($this->dataProvider)) {
            return 'No images in the library.';
        }

        $items = '';
        foreach ($this->dataProvider->getModels() as $model) {
            $src = '';
            $fileType = $model->mime_type;
            if ($model->dimension) {
                $src = $model->object_url . $model->thumbnail_name;
                $fileType = 'image';
            } else {
                $src = $model->object_url . $model->src_file_name;
            }

            $toolArray = $this->_getToolArray($model->file_id);
            $items .= $this->renderGridBox($src, $fileType, $toolArray);
        }

        return $items;
    }

    public function renderPager() {
        $pagination = $this->dataProvider->getPagination();
        $links = $pagination->getLinks();

        if (isset($links[\yii\data\Pagination::LINK_NEXT])) {
            $link = Html::a('', $links[\yii\data\Pagination::LINK_NEXT]);
            return Html::tag('div', $link, ['id' => 'fm-next-page']);
        }

        return;
    }

    public function renderGridBox($src, $fileType, $toolArray) {
        $gridBox = new GridBox([
            'owner' => $this,
            'src' => $src,
            'fileType' => $fileType,
            'toolArray' => $toolArray
        ]);

        return $gridBox->renderGridBox();
    }

    private function _getToolArray($fileId) {
        $input = Html::input('radio', 'fm-gallery-group', $fileId, ['data-url' => \yii\helpers\Url::to(['/filemanager/files/update', 'id' => $fileId])]);
        $view = Html::tag('i', '', ['class' => 'fa-icon fa fa-eye fm-view', 'title' => \Yii::t('filemanager', 'View')]);

        $toolArray = [
            [
                'tagType' => 'label',
                'content' => $input . $view
            ]
        ];

        if ($this->viewFrom == 'modal') {
            $toolArray[] = [
                'tagType' => 'i',
                'options' => [
                    'class' => 'fa-icon fa fa-link fm-use',
                    'title' => \Yii::t('filemanager', 'Use'),
                    'data-url' => \yii\helpers\Url::to(['/filemanager/files/use']),
                    'data-id' => $fileId
                ]
            ];
            $toolArray[] = [
                'tagType' => 'i',
                'options' => [
                    'class' => 'fa-icon fa fa-trash fm-delete',
                    'title' => \Yii::t('filemanager', 'Delete Permanently'),
                    'data-url' => \yii\helpers\Url::to(['/filemanager/files/use']),
                    'data-id' => $fileId
                ]
            ];
        }
        
        return $toolArray;
    }

}
