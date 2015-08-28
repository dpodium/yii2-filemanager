<?php

namespace dpodium\filemanager\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "files_tag".
 *
 * @property integer $tag_id
 * @property string $value
 * @property integer $created_at
 *
 * @property FilesRelationship[] $filesRelationships
 */
class FilesTag extends ActiveRecord {

    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at']
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'files_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['value'], 'required'],
            [['value'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'tag_id' => Yii::t('filemanager', 'Tag ID'),
            'value' => Yii::t('filemanager', 'Value'),
            'created_at' => Yii::t('filemanager', 'Created At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilesRelationships() {
        return $this->hasMany(FilesRelationship::className(), ['tag_id' => 'tag_id']);
    }

    public function saveTag($tagArray) {
        $saveTags = [];

        if (is_array($tagArray)) {
            foreach ($tagArray as $tag) {
                if (!$this->find()->where('tag_id=:tag_id', [':tag_id' => (int) $tag])->exists()) {
                    $this->value = \yii\helpers\Html::encode($tag);
                    if ($this->save()) {
                        $saveTags[] = $this->tag_id;
                        $this->isNewRecord = true;
                        $this->tag_id = NULL;
                    } else {
                        return ['error' => $this->errors['value'][0]];
                    }
                } else {
                    $saveTags[] = $tag;
                }
            }
        }

        return $saveTags;
    }

}
