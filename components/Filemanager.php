<?php

namespace dpodium\filemanager\components;

use Yii;
use yii\helpers\Html;

class Filemanager {

    const TYPE_FULL_PAGE = 1; // upload from filemanager module
    const TYPE_MODAL = 2; // upload from pop-up modal

    public static function renderEditUploadedBar($fileId, $objectUrl, $filename, $fileType) {
        $img = FilemanagerHelper::getFile($fileId);
        $src = $img['img_thumb_src'];
        //$src = $objectUrl . $filename;
        $file = static::getThumbnail($fileType, $src, "20px", "30px");
        $content_1 = Html::tag('h6', $filename, ['class' => 'separator-box-title']);
        $content_2 = Html::tag('div', Html::a(Yii::t('filemanager', 'Edit'), ['/filemanager/files/update', 'id' => $fileId], ['target' => '_blank']), ['class' => 'separator-box-toolbar']);
        $content_3 = Html::tag('div', $file . $content_1 . $content_2, ['class' => 'separator-box-header']);
        $html = Html::tag('div', $content_3, ['class' => 'separator-box']);

        return $html;
    }

    public static function getThumbnail($fileType, $src, $height = '', $width = '') {
        $thumbnailSize = \Yii::$app->getModule('filemanager')->thumbnailSize;

        if ($fileType == 'image') {
            return Html::img($src, ['class' => 'img-responsive']);
        }

        $availableThumbnail = ['archive', 'audio', 'code', 'excel', 'movie', 'pdf', 'powerpoint', 'text', 'video', 'word', 'zip'];
        $type = explode('/', $fileType);
        $faClass = 'fa-file-o';
        $fontSize = !empty($height) ? $height : "{$thumbnailSize[1]}px";

        if (in_array($type[0], $availableThumbnail)) {
            $faClass = "fa-file-{$type[0]}-o";
        } else if (in_array($type[1], $availableThumbnail)) {
            $faClass = "fa-file-{$type[1]}-o";
        }

        return Html::tag('div', Html::tag('i', '', ['class' => "fa {$faClass}", 'style' => "font-size: $fontSize"]), ['class' => 'fm-thumb', 'style' => "height: $height; width: $width"]);
    }

}
