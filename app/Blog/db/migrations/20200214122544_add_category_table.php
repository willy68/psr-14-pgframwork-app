<?php

use Phinx\Migration\AbstractMigration;

use function DI\string;

class AddCategoryTable extends AbstractMigration
{

    public function change()
    {
        $this->table('categories')
            ->addColumn('name', 'string')
            ->addColumn('slug', 'string')
            ->addIndex('slug', ['unique' => true])
            ->create();
    }
}
