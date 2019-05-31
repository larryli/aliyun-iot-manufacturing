<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%device}}`.
 */
class m190518_070900_create_device_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // https://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET {$this->db->charset} COLLATE utf8_unicode_ci ENGINE=InnoDB";
        }

        $this->createTable('{{%device}}', [
            'id' => $this->primaryKey(),
            'serial_no' => $this->string()->unique(),
            'apply_id' => $this->integer(),
            'device_name' => $this->string(),
            'device_secret' => $this->string(),
            'state' => $this->tinyInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ], $tableOptions);

        $this->createIndex('idx-device-apply_id', '{{%device}}', [
            'apply_id',
        ]);
        $this->createIndex('idx-device-device_name', '{{%device}}', [
            'device_name',
        ]);
        $this->createIndex('idx-device-state', '{{%device}}', [
            'state',
        ]);
        $this->createIndex('idx-device-created_at', '{{%device}}', [
            'created_at',
        ]);
        $this->createIndex('idx-device-updated_at', '{{%device}}', [
            'updated_at',
        ]);

        if (!empty($tableOptions)) {
            $this->addForeignKey(
                'fk-device-apply_id',
                '{{%device}}',
                'apply_id',
                '{{%apply}}',
                'id',
                'CASCADE'
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%device}}');
    }
}
