<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{

    public function change()
    {
        $this->table('users')
            ->addColumn('username', 'string')
            ->addColumn('email', 'string')
            ->addColumn('password', 'string')
            ->addIndex(['username', 'email'], ['unique' => true])
            ->create();
    }
}
