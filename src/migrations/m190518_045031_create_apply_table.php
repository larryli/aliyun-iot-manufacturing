<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%apply}}`.
 */
class m190518_045031_create_apply_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // https://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = "CHARACTER SET {$this->db->charset} COLLATE utf8mb4_unicode_ci ENGINE=InnoDB";
        }

        $this->createTable('{{%apply}}', [
            'id' => $this->primaryKey(),
            'product_key' => $this->string()->notNull(),
            'product_name' => $this->string()->notNull(),
            'title' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'start_serial_no' => $this->string()->notNull(),
            'count' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
        ], $tableOptions);

        $this->createIndex('idx-apply-product_key', '{{%apply}}', [
            'product_key',
        ]);
        $this->createIndex('idx-apply-created_at', '{{%apply}}', [
            'created_at',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%apply}}');
    }
}
