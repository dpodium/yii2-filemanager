<?php

namespace dpodium\filemanager\models;

use Yii;
use dpodium\filemanager\models\FilesTag;

/**
 * This is the model class for table "files_relationship".
 *
 * @property integer $file_id
 * @property integer $tag_id
 *
 * @property FilesTag $tag
 * @property Files $file
 */
class FilesRelationship extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'files_relationship';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['file_id', 'tag_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'file_id' => Yii::t('filemanager', 'File ID'),
            'tag_id' => Yii::t('filemanager', 'Tag ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTag() {
        return $this->hasOne(FilesTag::className(), ['tag_id' => 'tag_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile() {
        return $this->hasOne(Files::className(), ['file_id' => 'file_id']);
    }

    public static function getTagIdArray($fileId) {
        $models = static::findAll(['file_id' => $fileId]);
        $tags = [];

        foreach ($models as $model) {
            if (isset($model->tag)) {
                $tags[] = [
                    'id' => $model->tag->tag_id,
                    'value' => $model->tag->value
                ];
            }
        }

        return $tags;
    }

    public function saveRelationship($fileId, $tagArray) {
        // delete all tags before save
        $this->deleteAll(['file_id' => $fileId]);

        foreach ($tagArray as $tagId) {
            $this->setIsNewRecord(true);
            $this->file_id = $fileId;
            $this->tag_id = $tagId;
            $this->save();
        }
    }

}
