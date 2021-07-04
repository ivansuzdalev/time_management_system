<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210703202525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)');
        $this->addSql('DROP TABLE tasks');
        $this->addSql('DROP TABLE users');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tasks (id INTEGER PRIMARY KEY AUTOINCREMENT DEFAULT NULL, task_name VARCHAR(255) NOT NULL COLLATE BINARY, completed INTEGER NOT NULL, start_from DATETIME DEFAULT NULL, end_to DATETIME DEFAULT NULL, project_id VARCHAR(255) DEFAULT NULL COLLATE BINARY)');
        $this->addSql('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT DEFAULT NULL, user VARCHAR(255) NOT NULL COLLATE BINARY, pass VARCHAR(255) NOT NULL COLLATE BINARY, register_from DATETIME DEFAULT NULL, user_active INTEGER NOT NULL)');
        $this->addSql('DROP TABLE user');
    }
}
