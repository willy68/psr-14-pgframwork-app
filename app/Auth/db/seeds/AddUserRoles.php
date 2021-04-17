<?php


use Phinx\Seed\AbstractSeed;

class AddUserRoles extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run()
    {
        $admin = $this->fetchRow('SELECT * FROM users WHERE username = "admin"');
        $roles = json_encode(['ROLE_USER', 'ROLE_ADMIN']);

        $admin = $this->fetchRow("UPDATE users SET roles='{$roles}' WHERE id={$admin['id']}");

        $this->table('users')
            ->insert([
                'username' => 'willy',
                'email'    => 'willy@willy.fr',
                'password' => password_hash('willy', PASSWORD_DEFAULT, []),
                'roles' => json_encode(['ROLE_USER'])
            ])
            ->save();
    }
}
