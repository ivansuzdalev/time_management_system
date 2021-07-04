<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210703190132 extends AbstractMigration
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
        

        $command = 'CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY,
                start_from datetime,
                end_date_time datetime,
                title  VARCHAR (255) NOT NULL,
                comment TEXT NOT NULL, 
                date_time_spent INTEGER,
                user_id INTEGER NOT NULL, 
                FOREIGN KEY (user_id)
                REFERENCES user(id) ON UPDATE CASCADE
                                                ON DELETE CASCADE)';
        // execute the sql commands to create new tables
        $this->addSql($command);

    }

    public function down(Schema $schema): void
    {
        $command = 
            'DROP TABLE user'
          ;
        // execute the sql commands to create new tables
        $this->addSql($command);

        $command = 
            'DROP TABLE tasks'
          ;
        // execute the sql commands to create new tables
        $$this->addSql($command);
    }

    
}
