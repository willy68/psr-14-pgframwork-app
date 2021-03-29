<?php

use Phinx\Migration\AbstractMigration;

class UserTokenMigration extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->table('user_tokens', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'collation' => 'utf8_general_ci',
            'comment' => '',
            'row_format' => 'DYNAMIC'
        ])
        ->addColumn('id', 'integer', [
            'null' => false,
            'limit' => '10',
            'signed' => false,
            'identity' => 'enable',
        ])
        ->addColumn('series', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8_general_ci',
            'encoding' => 'utf8',
            'after' => 'id',
        ])
        ->addColumn('credential', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8_general_ci',
            'encoding' => 'utf8',
            'after' => 'id',
        ])
        ->addColumn('random_password', 'string', [
            'null' => false,
            'limit' => 255,
            'collation' => 'utf8_general_ci',
            'encoding' => 'utf8',
            'after' => 'credential',
        ])
        ->addColumn('expiration_date', 'timestamp', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
            'after' => 'random_password'
        ])
        ->addIndex('series', ['unique' => true])
        ->create();
    }
}
