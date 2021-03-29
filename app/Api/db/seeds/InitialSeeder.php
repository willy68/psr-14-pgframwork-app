<?php

use Phinx\Seed\AbstractSeed;

class InitialSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $data = [];
        $data[] = ['code' => 1, 'libelle' => 'standard'];
        $data[] = ['code' => 2, 'libelle' => 'facturation'];
        $data[] = ['code' => 3, 'libelle' => 'travaux'];
        $data[] = ['code' => 4, 'libelle' => 'entreprise'];
        $this->table('adresse_type')->insert($data)->save();

        $data = [];
        $data[] = ['role' => 'user'];
        $data[] = ['role' => 'admin'];
        $data[] = ['role' => 'root'];
        $this->table('role')->insert($data)->save();

        $data = [];
        $data[] = ['libelle' => 'en cours'];
        $data[] = ['libelle' => 'accepté'];
        $data[] = ['libelle' => 'terminé'];
        $data[] = ['libelle' => 'rejeté'];
        $data[] = ['libelle' => 'brouillon'];
        $this->table('statut_devis')->insert($data)->save();
    }
}
