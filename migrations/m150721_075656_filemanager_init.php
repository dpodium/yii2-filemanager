<?php

use yii\db\Schema;
use yii\db\Migration;

class m150721_075656_filemanager_init extends Migration {

    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%folders}}', [
            'folder_id' => Schema::TYPE_BIGPK,
            'category' => Schema::TYPE_STRING . '(64) NOT NULL',
            'path' => Schema::TYPE_STRING . '(255) UNIQUE NOT NULL',
            'storage' => Schema::TYPE_STRING . '(32) NOT NULL',
                ], $tableOptions);

        $this->createTable('{{%files}}', [
            'file_id' => Schema::TYPE_BIGPK,
            'file_identifier' => Schema::TYPE_STRING . '(32) NOT NULL',
            'host' => Schema::TYPE_STRING . '(64) NULL',
            'url' => Schema::TYPE_STRING . '(255) NOT NULL', // folder path
            'storage_id' => Schema::TYPE_STRING . '(64) NOT NULL', // S3 bucket ID or module directory(@webroot)
            'object_url' => Schema::TYPE_STRING . '(255) NOT NULL', // S3 ObjectUrl            
            'thumbnail_name' => Schema::TYPE_STRING . '(100) NULL', // thumb_`src_file_name`_`dimension`
            'src_file_name' => Schema::TYPE_STRING . '(64) NOT NULL', // use to search by filename
            'mime_type' => Schema::TYPE_STRING . '(100) NOT NULL', // eg. image/png
            'caption' => Schema::TYPE_STRING . '(64) NULL',
            'alt_text' => Schema::TYPE_STRING . '(64) NULL',
            'description' => Schema::TYPE_STRING . '(255) NULL',
            'dimension' => Schema::TYPE_STRING . '(12) NULL', // width and height
            'folder_id' => Schema::TYPE_BIGINT . ' NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ], $tableOptions);

        $this->addCommentOnColumn('{{%files}}', 'dimension', 'w X h');
        $this->createIndex('idx__file_identifier', '{{%files}}', 'file_identifier');
        $this->addForeignKey('fk__files__folders', '{{%files}}', 'folder_id', '{{%folders}}', 'folder_id', 'RESTRICT');

        $this->createTable('{{%files_tag}}', [
            'tag_id' => Schema::TYPE_BIGPK,
            'value' => Schema::TYPE_STRING . '(32) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER . ' NOT NULL',
                ], $tableOptions);

        $this->createTable('{{%files_relationship}}', [
            'file_id' => Schema::TYPE_BIGINT,
            'tag_id' => Schema::TYPE_BIGINT,
                ], $tableOptions);
        $this->createIndex('idx__file_id__tag_id', '{{%files_relationship}}', ['file_id', 'tag_id']);
        $this->addForeignKey('fk__files_relationship__files', '{{%files_relationship}}', 'file_id', '{{%files}}', 'file_id', 'CASCADE');
        $this->addForeignKey('fk__files_relationship__files_tag', '{{%files_relationship}}', 'tag_id', '{{%files_tag}}', 'tag_id', 'CASCADE');

        echo "m150721_075656_filemanager_init successfully applied.\n";
    }

    public function down() {
        $this->dropTable('{{%files_relationship}}');
        $this->dropTable('{{%files_tag}}');
        $this->dropTable('{{%files}}');
        $this->dropTable('{{%folders}}');

        echo "m150721_075656_filemanager_init successfully reverted.\n";
    }

}
