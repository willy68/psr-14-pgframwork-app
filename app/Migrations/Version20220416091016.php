<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220416091016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX slug ON categories');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY posts_ibfk_1');
        $this->addSql('ALTER TABLE posts CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE published published TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA12469DE2 FOREIGN KEY (category_id) REFERENCES categories (id)');
        $this->addSql('ALTER TABLE posts RENAME INDEX category_id TO IDX_885DBAFA12469DE2');
        $this->addSql('DROP INDEX series ON user_tokens');
        $this->addSql('ALTER TABLE user_tokens CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE expiration_date expiration_date DATETIME NOT NULL');
        $this->addSql('DROP INDEX username ON users');
        $this->addSql('ALTER TABLE users CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX slug ON categories (slug)');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA12469DE2');
        $this->addSql('ALTER TABLE posts CHANGE created_at created_at DATETIME NOT NULL, CHANGE published published TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT posts_ibfk_1 FOREIGN KEY (category_id) REFERENCES categories (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('ALTER TABLE posts RENAME INDEX idx_885dbafa12469de2 TO category_id');
        $this->addSql('ALTER TABLE user_tokens CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE expiration_date expiration_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX series ON user_tokens (series)');
        $this->addSql('ALTER TABLE users CHANGE roles roles VARCHAR(255) DEFAULT \'["ROLE_USER"]\' NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX username ON users (username, email)');
    }
}
