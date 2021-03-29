<?php

use Phinx\Db\Adapter\MysqlAdapter;

class Paysagest extends Phinx\Migration\AbstractMigration
{
    public function change()
    {
        $this->execute("ALTER DATABASE CHARACTER SET 'utf8';");
        $this->execute("ALTER DATABASE COLLATE='utf8_general_ci';");
        $this->table('administrateur', [
                'id' => false,
                'primary_key' => ['user_id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'user_id',
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_administrateur_entreprise_idx',
                'unique' => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'fk_administrateur_user_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('adresse', [
                'id' => false,
                'primary_key' => ['id', 'client_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('adresse_1', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'client_id',
            ])
            ->addColumn('adresse_2', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'adresse_1',
            ])
            ->addColumn('adresse_3', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'adresse_2',
            ])
            ->addColumn('cp', 'string', [
                'null' => true,
                'limit' => 10,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'adresse_3',
            ])
            ->addColumn('ville', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'cp',
            ])
            ->addColumn('pays', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'ville',
            ])
            ->addColumn('adresse_type_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'pays',
            ])
            ->addIndex(['adresse_type_id'], [
                'name' => 'fk_adresse_adresse_type_idx',
                'unique' => false,
            ])
            ->addIndex(['client_id'], [
                'name' => 'fk_adresse_client_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('adresse_type', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('code', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('libelle', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code',
            ])
            ->addIndex(['code'], [
                'name' => 'code_UNIQUE',
                'unique' => true,
            ])
            ->create();
        $this->table('article', [
                'id' => false,
                'primary_key' => ['id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('code', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('famille_article', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code',
            ])
            ->addColumn('libelle', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'famille_article',
            ])
            ->addColumn('unite', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'libelle',
            ])
            ->addColumn('ht', 'decimal', [
                'null' => true,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'unite',
            ])
            ->addColumn('prix_achat', 'decimal', [
                'null' => true,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'ht',
            ])
            ->addColumn('coef', 'decimal', [
                'null' => true,
                'default' => '0.00',
                'precision' => '5',
                'scale' => '2',
                'after' => 'prix_achat',
            ])
            ->addColumn('ttc', 'decimal', [
                'null' => true,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'coef',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'ttc',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'after' => 'created_at',
            ])
            ->addColumn('tva_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'updated_at',
            ])
            ->addIndex(['code'], [
                'name' => 'code_UNIQUE',
                'unique' => true,
            ])
            ->addIndex(['tva_id'], [
                'name' => 'fk_article_tva_idx',
                'unique' => false,
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_articles_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('civilite', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('libelle', 'string', [
                'null' => false,
                'limit' => 100,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_civilite_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('client', [
                'id' => false,
                'primary_key' => ['id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('code_client', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('civilite', 'string', [
                'null' => true,
                'limit' => 100,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code_client',
            ])
            ->addColumn('nom', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'civilite',
            ])
            ->addColumn('prenom', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'nom',
            ])
            ->addColumn('tel', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'prenom',
            ])
            ->addColumn('portable', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'tel',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'portable',
            ])
            ->addColumn('tva_intracom', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'email',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'tva_intracom',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'after' => 'created_at',
            ])
            ->addIndex(['code_client'], [
                'name' => 'code_client_UNIQUE',
                'unique' => true,
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_client_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('contrat', [
                'id' => false,
                'primary_key' => ['id', 'client_id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'client_id',
            ])
            ->addColumn('code_contrat', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('total_ht', 'decimal', [
                'null' => true,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'code_contrat',
            ])
            ->addColumn('tva_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'total_ht',
            ])
            ->addColumn('total_ttc', 'decimal', [
                'null' => true,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'tva_id',
            ])
            ->addColumn('total_ht_period', 'decimal', [
                'null' => true,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ttc',
            ])
            ->addColumn('total_ttc_period', 'decimal', [
                'null' => true,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ht_period',
            ])
            ->addColumn('periodicite_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'total_ttc_period',
            ])
            ->addIndex(['code_contrat'], [
                'name' => 'num_contrat_UNIQUE',
                'unique' => true,
            ])
            ->addIndex(['tva_id'], [
                'name' => 'fk_contrat_tva_idx',
                'unique' => false,
            ])
            ->addIndex(['periodicite_id'], [
                'name' => 'fk_contrat_periodicite_idx',
                'unique' => false,
            ])
            ->addIndex(['client_id', 'entreprise_id'], [
                'name' => 'fk_contrat_client_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('dernier_code', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('table_nom', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('colonne', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'table_nom',
            ])
            ->addColumn('code_table', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'colonne',
            ])
            ->addColumn('prochain_code', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code_table',
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_dernier_code_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('devis', [
                'id' => false,
                'primary_key' => ['id', 'client_id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'client_id',
            ])
            ->addColumn('code_devis', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('statut_devis', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code_devis',
            ])
            ->addColumn('objet', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'statut_devis',
            ])
            ->addColumn('adresse_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'objet',
            ])
            ->addColumn('validite', 'datetime', [
                'null' => true,
                'after' => 'adresse_id',
            ])
            ->addColumn('total_ht', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'validite',
            ])
            ->addColumn('total_tva', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ht',
            ])
            ->addColumn('total_ttc', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_tva',
            ])
            ->addColumn('acompte', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ttc',
            ])
            ->addColumn('total_net', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'acompte',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'total_net',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
                'after' => 'created_at',
            ])
            ->addIndex(['code_devis'], [
                'name' => 'code_devis_UNIQUE',
                'unique' => true,
            ])
            ->addIndex(['adresse_id'], [
                'name' => 'fk_devis_adresse_idx',
                'unique' => false,
            ])
            ->addIndex(['client_id', 'entreprise_id'], [
                'name' => 'fk_devis_client_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('echeance', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('libelle', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->addColumn('interval', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'libelle',
            ])
            ->addColumn('unite', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'interval',
            ])
            ->addColumn('taux', 'decimal', [
                'null' => true,
                'precision' => '4',
                'scale' => '2',
                'after' => 'unite',
            ])
            ->create();
        $this->table('entreprise', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('siret', 'string', [
                'null' => false,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->addColumn('nom', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'siret',
            ])
            ->addColumn('ape', 'string', [
                'null' => true,
                'limit' => 25,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'nom',
            ])
            ->addColumn('tva_intracom', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'ape',
            ])
            ->addColumn('adresse', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'tva_intracom',
            ])
            ->addColumn('suite_adresse', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'adresse',
            ])
            ->addColumn('cp', 'string', [
                'null' => true,
                'limit' => 16,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'suite_adresse',
            ])
            ->addColumn('ville', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'cp',
            ])
            ->addColumn('tel', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'ville',
            ])
            ->addColumn('portable', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'tel',
            ])
            ->addColumn('email', 'string', [
                'null' => true,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'portable',
            ])
            ->addColumn('regime_commercial', 'string', [
                'null' => true,
                'limit' => 150,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'email',
            ])
            ->addColumn('logo', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'regime_commercial',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'logo',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'after' => 'created_at',
            ])
            ->addIndex(['siret'], [
                'name' => 'siret_UNIQUE',
                'unique' => true,
            ])
            ->create();
        $this->table('facture', [
                'id' => false,
                'primary_key' => ['id', 'client_id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'client_id',
            ])
            ->addColumn('code_facture', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('adresse_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'code_facture',
            ])
            ->addColumn('echeance_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'adresse_id',
            ])
            ->addColumn('date_edition', 'datetime', [
                'null' => true,
                'after' => 'echeance_id',
            ])
            ->addColumn('objet', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'date_edition',
            ])
            ->addColumn('total_ht', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'objet',
            ])
            ->addColumn('total_tva', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ht',
            ])
            ->addColumn('total_ttc', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_tva',
            ])
            ->addColumn('acompte', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ttc',
            ])
            ->addColumn('total_net', 'decimal', [
                'null' => false,
                'precision' => '12',
                'scale' => '4',
                'after' => 'acompte',
            ])
            ->addColumn('modifiable', 'integer', [
                'null' => false,
                'default' => '1',
                'limit' => MysqlAdapter::INT_TINY,
                'after' => 'total_net',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'modifiable',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'after' => 'created_at',
            ])
            ->addIndex(['code_facture'], [
                'name' => 'code_facture_UNIQUE',
                'unique' => true,
            ])
            ->addIndex(['adresse_id'], [
                'name' => 'fk_facture_adresse_idx',
                'unique' => false,
            ])
            ->addIndex(['echeance_id'], [
                'name' => 'fk_facture_echeance_idx',
                'unique' => false,
            ])
            ->addIndex(['client_id', 'entreprise_id'], [
                'name' => 'fk_facture_client_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('facture_periodique', [
                'id' => false,
                'primary_key' => ['id', 'client_id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'client_id',
            ])
            ->addColumn('contrat_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'entreprise_id',
            ])
            ->addColumn('prochaine_facture', 'date', [
                'null' => false,
                'after' => 'contrat_id',
            ])
            ->addColumn('date_debut', 'date', [
                'null' => false,
                'after' => 'prochaine_facture',
            ])
            ->addIndex(['contrat_id'], [
                'name' => 'fk_facture_periodique_contrat_idx',
                'unique' => false,
            ])
            ->addIndex(['client_id', 'entreprise_id'], [
                'name' => 'fk_facture_periodique_client_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('famille_article', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('libelle', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'libelle',
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_famille_article_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('ligne_devis', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('devis_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('num_ligne', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'devis_id',
            ])
            ->addColumn('libelle', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'num_ligne',
            ])
            ->addColumn('quantite', 'decimal', [
                'null' => true,
                'precision' => '6',
                'scale' => '2',
                'after' => 'libelle',
            ])
            ->addColumn('unite', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'quantite',
            ])
            ->addColumn('ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'unite',
            ])
            ->addColumn('tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'ht',
            ])
            ->addColumn('total_ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '2',
                'after' => 'tva',
            ])
            ->addColumn('ttc', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ht',
            ])
            ->addColumn('article_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'ttc',
            ])
            ->addColumn('tranche_devis_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'article_id',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'default' => 'standard',
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'tranche_devis_id',
            ])
            ->addIndex(['article_id'], [
                'name' => 'fk_ligne_devis_article_idx',
                'unique' => false,
            ])
            ->addIndex(['devis_id'], [
                'name' => 'fk_ligne_devis_devis_idx',
                'unique' => false,
            ])
            ->addIndex(['tranche_devis_id'], [
                'name' => 'fk_ligne_devis_tranche_devis_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('ligne_facture', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('num_ligne', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('libelle', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'num_ligne',
            ])
            ->addColumn('quantite', 'decimal', [
                'null' => true,
                'precision' => '6',
                'scale' => '2',
                'after' => 'libelle',
            ])
            ->addColumn('unite', 'string', [
                'null' => true,
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'quantite',
            ])
            ->addColumn('ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'unite',
            ])
            ->addColumn('tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'ht',
            ])
            ->addColumn('total_ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '2',
                'after' => 'tva',
            ])
            ->addColumn('ttc', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ht',
            ])
            ->addColumn('article_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'ttc',
            ])
            ->addColumn('facture_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'article_id',
            ])
            ->addColumn('tranche_facture_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'facture_id',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'default' => 'standard',
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'tranche_facture_id',
            ])
            ->addIndex(['article_id'], [
                'name' => 'fk_ligne_facture_article_idx',
                'unique' => false,
            ])
            ->addIndex(['facture_id'], [
                'name' => 'fk_ligne_facture_facture_idx',
                'unique' => false,
            ])
            ->addIndex(['tranche_facture_id'], [
                'name' => 'fk_ligne_facture_tranche_facture_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('ligne_facture_periodique', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('facture_periodique_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('article_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'facture_periodique_id',
            ])
            ->addColumn('num_ligne', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'article_id',
            ])
            ->addColumn('libelle', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'num_ligne',
            ])
            ->addColumn('quantite', 'decimal', [
                'null' => true,
                'precision' => '6',
                'scale' => '2',
                'after' => 'libelle',
            ])
            ->addColumn('ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'quantite',
            ])
            ->addColumn('tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'ht',
            ])
            ->addColumn('total_ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '2',
                'after' => 'tva',
            ])
            ->addColumn('ttc', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'total_ht',
            ])
            ->addColumn('tranche_facture_periodique_id', 'integer', [
                'null' => true,
                'limit' => '10',
                'signed' => false,
                'after' => 'ttc',
            ])
            ->addColumn('type', 'string', [
                'null' => true,
                'default' => 'standard',
                'limit' => 45,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'tranche_facture_periodique_id',
            ])
            ->addIndex(['article_id'], [
                'name' => 'fk_ligne_facture_periodique_article_idx',
                'unique' => false,
            ])
            ->addIndex(['facture_periodique_id'], [
                'name' => 'fk_ligne_facture_periodique_facture_periodique_idx',
                'unique' => false,
            ])
            ->addIndex(['tranche_facture_periodique_id'], [
                'name' => 'fk_ligne_facture_periodique_tranche_facture_periodique_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('periodicite', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('libelle', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('interval', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'libelle',
            ])
            ->addColumn('unite', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'interval',
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_periodicite_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('prefix', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('table_nom', 'string', [
                'null' => false,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'entreprise_id',
            ])
            ->addColumn('libelle', 'string', [
                'null' => false,
                'limit' => 16,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'table_nom',
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_prefix_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('reglement', [
                'id' => false,
                'primary_key' => ['id', 'facture_id', 'client_id', 'entreprise_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('facture_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'facture_id',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'client_id',
            ])
            ->addColumn('reglement', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'entreprise_id',
            ])
            ->addColumn('reste', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'reglement',
            ])
            ->addColumn('total_ttc', 'decimal', [
                'null' => false,
                'default' => '0.0000',
                'precision' => '12',
                'scale' => '4',
                'after' => 'reste',
            ])
            ->addIndex(['facture_id', 'client_id', 'entreprise_id'], [
                'name' => 'fk_reglement_facture_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('rib', [
                'id' => false,
                'primary_key' => ['id', 'client_id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('client_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('nom_banque', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'client_id',
            ])
            ->addColumn('nom_agence', 'string', [
                'null' => true,
                'limit' => 64,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'nom_banque',
            ])
            ->addColumn('code_banque', 'string', [
                'null' => false,
                'limit' => 5,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'nom_agence',
            ])
            ->addColumn('code_guichet', 'string', [
                'null' => false,
                'limit' => 5,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code_banque',
            ])
            ->addColumn('num_compte', 'string', [
                'null' => false,
                'limit' => 11,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code_guichet',
            ])
            ->addColumn('cle_rib', 'string', [
                'null' => false,
                'limit' => 2,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'num_compte',
            ])
            ->addColumn('iban', 'string', [
                'null' => false,
                'limit' => 34,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'cle_rib',
            ])
            ->addColumn('bic', 'string', [
                'null' => false,
                'limit' => 11,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'iban',
            ])
            ->addIndex(['client_id'], [
                'name' => 'fk_rib_client_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('role', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('role', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->create();
        $this->table('statut_devis', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('libelle', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->create();
        $this->table('total_tva_devis', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('libelle', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->addColumn('taux', 'decimal', [
                'null' => true,
                'precision' => '4',
                'scale' => '2',
                'after' => 'libelle',
            ])
            ->addColumn('total_tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'taux',
            ])
            ->addColumn('devis_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'total_tva',
            ])
            ->addIndex(['devis_id'], [
                'name' => 'fk_total_tva_devis_devis_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('total_tva_facture', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('libelle', 'string', [
                'null' => true,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->addColumn('taux', 'decimal', [
                'null' => true,
                'precision' => '4',
                'scale' => '2',
                'after' => 'libelle',
            ])
            ->addColumn('total_tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'taux',
            ])
            ->addColumn('facture_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'total_tva',
            ])
            ->addIndex(['facture_id'], [
                'name' => 'fk_total_tva_facture_facture_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('tranche_devis', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('devis_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('num_ligne', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'devis_id',
            ])
            ->addColumn('libelle', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'num_ligne',
            ])
            ->addColumn('sous_total_ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'libelle',
            ])
            ->addColumn('sous_total_tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'sous_total_ht',
            ])
            ->addColumn('sous_total_ttc', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'sous_total_tva',
            ])
            ->addIndex(['devis_id'], [
                'name' => 'fk_tranche_devis_devis_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('tranche_facture', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('facture_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('num_ligne', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'facture_id',
            ])
            ->addColumn('libelle', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'num_ligne',
            ])
            ->addColumn('sous_total_ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'libelle',
            ])
            ->addColumn('sous_total_tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'sous_total_ht',
            ])
            ->addColumn('sous_total_ttc', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'sous_total_tva',
            ])
            ->addIndex(['facture_id'], [
                'name' => 'fk_tranche_facture_facture_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('tranche_facture_periodique', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('facture_periodique_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('num_ligne', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'facture_periodique_id',
            ])
            ->addColumn('libelle', 'text', [
                'null' => true,
                'limit' => 65535,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'num_ligne',
            ])
            ->addColumn('sous_total_ht', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'libelle',
            ])
            ->addColumn('sous_total_tva', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'sous_total_ht',
            ])
            ->addColumn('sous_total_ttc', 'decimal', [
                'null' => true,
                'precision' => '12',
                'scale' => '4',
                'after' => 'sous_total_tva',
            ])
            ->addIndex(['facture_periodique_id'], [
                'name' => 'fk_tranche_facture_periodique_facture_periodique_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('tva', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('entreprise_id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'after' => 'id',
            ])
            ->addColumn('code_tva', 'integer', [
                'null' => false,
                'limit' => MysqlAdapter::INT_REGULAR,
                'after' => 'entreprise_id',
            ])
            ->addColumn('libelle', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'code_tva',
            ])
            ->addColumn('taux', 'decimal', [
                'null' => false,
                'default' => '0.00',
                'precision' => '4',
                'scale' => '2',
                'after' => 'libelle',
            ])
            ->addIndex(['code_tva'], [
                'name' => 'code_tva_UNIQUE',
                'unique' => true,
            ])
            ->addIndex(['entreprise_id'], [
                'name' => 'fk_tva_entreprise_idx',
                'unique' => false,
            ])
            ->create();
        $this->table('user', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8',
                'collation' => 'utf8_general_ci',
                'comment' => '',
                'row_format' => 'DYNAMIC',
            ])
            ->addColumn('id', 'integer', [
                'null' => false,
                'limit' => '10',
                'signed' => false,
                'identity' => 'enable',
            ])
            ->addColumn('username', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'id',
            ])
            ->addColumn('email', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'username',
            ])
            ->addColumn('password', 'string', [
                'null' => false,
                'limit' => 255,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'email',
            ])
            ->addColumn('role', 'string', [
                'null' => false,
                'limit' => 32,
                'collation' => 'utf8_general_ci',
                'encoding' => 'utf8',
                'after' => 'password',
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
                'after' => 'role',
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => false,
                'after' => 'created_at',
            ])
            ->create();

        $this->execute("CREATE DEFINER = CURRENT_USER TRIGGER `entreprise_AFTER_INSERT` 
            AFTER INSERT ON `entreprise` FOR EACH ROW
            BEGIN
            DECLARE old_code VARCHAR(32);
            DECLARE new_code VARCHAR(32);
            DECLARE pref VARCHAR(16);
            
            INSERT INTO tva (entreprise_id, code_tva, libelle, taux)
            VALUES (NEW.id, 1, 'tva_20', 20.00);
            
            INSERT INTO tva (entreprise_id, code_tva, libelle, taux)
            VALUES (NEW.id, 2, 'tva_5.5', 5.50);
            
            INSERT INTO periodicite (entreprise_id, libelle, `interval`, unite)
            VALUES (NEW.id, 'mensuelle', 'MONTH', 1);
            
            INSERT INTO periodicite (entreprise_id, libelle, `interval`, unite)
            VALUES (NEW.id, 'bimestrielle', 'MONTH', 2);
            
            INSERT INTO periodicite (entreprise_id, libelle, `interval`, unite)
            VALUES (NEW.id, 'trimestrielle', 'MONTH', 3);
            
            INSERT INTO prefix (entreprise_id, table_nom, libelle)
            VALUES (NEW.id, 'client', 'CL');
            
            INSERT INTO prefix (entreprise_id, table_nom, libelle)
            VALUES (NEW.id, 'devis', 'DC');
            
            INSERT INTO prefix (entreprise_id, table_nom, libelle)
            VALUES (NEW.id, 'facture', 'FC');
            
            INSERT INTO prefix (entreprise_id, table_nom, libelle)
            VALUES (NEW.id, 'contrat', 'C');
            
            INSERT INTO civilite (entreprise_id, libelle)
            VALUES (NEW.id, 'M. et MM.');
            
            INSERT INTO civilite (entreprise_id, libelle)
            VALUES (NEW.id, 'Madame');
            
            INSERT INTO civilite (entreprise_id, libelle)
            VALUES (NEW.id, 'Monsieur');
            
            INSERT INTO civilite (entreprise_id, libelle)
            VALUES (NEW.id, 'Madame et Monsieur');
            
            
            SET pref = (SELECT COALESCE(libelle, 'CL') from prefix 
                                WHERE table_nom = 'client');
                    SET old_code = CONCAT(pref, 0);
                    SET new_code = CONCAT(pref, 1);
            
            INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
            VALUES (NEW.id, 'client', 'code_client', old_code, new_code);
            
            SET pref = (SELECT COALESCE(libelle, 'DC') from prefix 
                                WHERE table_nom = 'devis');
                    SET old_code = CONCAT(pref, 0);
                    SET new_code = CONCAT(pref, 1);
            
            INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
            VALUES (NEW.id, 'devis', 'code_devis', old_code, new_code);
            
            SET pref = (SELECT COALESCE(libelle, 'FC') from prefix 
                                WHERE table_nom = 'facture');
                    SET old_code = CONCAT(pref, 0);
                    SET new_code = CONCAT(pref, 1);
            
            INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
            VALUES (NEW.id, 'facture', 'code_facture', old_code, new_code);
            
            SET pref = (SELECT COALESCE(libelle, 'C') from prefix 
                                WHERE table_nom = 'contrat');
                    SET old_code = CONCAT(pref, 0);
                    SET new_code = CONCAT(pref, 1);
            
            INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
            VALUES (NEW.id, 'contrat', 'code_contrat', old_code, new_code);
            END");

        $this->execute("CREATE TRIGGER `client_AFTER_INSERT` 
            AFTER INSERT ON `client` FOR EACH ROW
            BEGIN
            DECLARE len INTEGER;
            DECLARE new_code VARCHAR(32);
            DECLARE num_code INTEGER UNSIGNED;
            DECLARE pref VARCHAR(16);
            
                IF (NEW.code_client IS NOT NULL) THEN
                    SET pref = (SELECT COALESCE(libelle, 'CL') from prefix 
                                WHERE table_nom = 'client' AND entreprise_id = NEW.entreprise_id);
                    SET len = char_length(pref);
                    SET new_code = substring(NEW.code_client, len + 1);
                    SET num_code = CAST(new_code AS UNSIGNED);
                    SET new_code = CONCAT(pref, num_code +1);
                    
                    INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
                    VALUES (NEW.entreprise_id, 'client', 'code_client', NEW.code_client, new_code);
                END IF;
            END");

        $this->execute("CREATE TRIGGER `facture_AFTER_INSERT` 
            AFTER INSERT ON `facture` FOR EACH ROW
            BEGIN
            DECLARE len INTEGER;
            DECLARE new_code VARCHAR(32);
            DECLARE num_code INTEGER UNSIGNED;
            DECLARE pref VARCHAR(16);
            
                IF (NEW.code_facture IS NOT NULL) THEN
                    SET pref = (SELECT COALESCE(libelle, 'FC') from prefix 
                                WHERE table_nom = 'facture' AND entreprise_id = NEW.entreprise_id);
                    SET len = char_length(pref);
                    SET new_code = substring(NEW.code_facture, len + 1);
                    SET num_code = CAST(new_code AS UNSIGNED);
                    SET new_code = CONCAT(pref, num_code +1);
                    
                    INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
                    VALUES (NEW.entreprise_id, 'facture', 'code_facture', NEW.code_facture, new_code);
                END IF;
            END");

        $this->execute("CREATE TRIGGER `devis_AFTER_INSERT` 
            AFTER INSERT ON `devis` FOR EACH ROW
            BEGIN
            DECLARE len INTEGER;
            DECLARE new_code VARCHAR(32);
            DECLARE num_code INTEGER UNSIGNED;
            DECLARE pref VARCHAR(16);
                IF (NEW.code_devis IS NOT NULL) THEN
                    SET pref = (SELECT COALESCE(libelle, 'DC') from prefix 
                                WHERE table_nom = 'devis' AND entreprise_id = NEW.entreprise_id);
                    SET len = char_length(pref);
                    SET new_code = substring(NEW.code_devis, len + 1);
                    SET num_code = CAST(new_code AS UNSIGNED);
                    SET new_code = CONCAT(pref, num_code +1);
                    
                    INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
                    VALUES (NEW.entreprise_id, 'devis', 'code_devis', NEW.code_devis, new_code);
                END IF;
            END
        ");

        $this->execute("CREATE TRIGGER `contrat_AFTER_INSERT` 
            AFTER INSERT ON `contrat` FOR EACH ROW
            BEGIN
            DECLARE len INTEGER;
            DECLARE new_code VARCHAR(32);
            DECLARE num_code INTEGER UNSIGNED;
            DECLARE pref VARCHAR(16);
            
                IF (NEW.code_contrat IS NOT NULL) THEN
                    SET pref = (SELECT COALESCE(libelle, 'C') from prefix 
                                WHERE table_nom = 'contrat' AND entreprise_id = NEW.entreprise_id);
                    SET len = char_length(pref);
                    SET new_code = substring(NEW.code_contrat, len + 1);
                    SET num_code = CAST(new_code AS UNSIGNED);
                    SET new_code = CONCAT(pref, num_code +1);
                    
                    INSERT INTO dernier_code (entreprise_id, table_nom, colonne, code_table, prochain_code)
                    VALUES (NEW.entreprise_id, 'contrat', 'code_contrat', NEW.code_contrat, new_code);
                END IF;
            END
        ");
    }
}
