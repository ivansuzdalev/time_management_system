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
        $command = 'CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            user VARCHAR (255) NOT NULL,
            pass VARCHAR (255) NOT NULL,
            register_from datetime,
            user_active INTEGER NOT NULL
          )';
        $this->addSql($command);

        $command = 'CREATE TABLE IF NOT EXISTS tasks (
                id INTEGER PRIMARY KEY,
                task_name  VARCHAR (255) NOT NULL,
                completed  INTEGER NOT NULL,
                start_from datetime,
                end_to datetime,
                project_id VARCHAR (255),
                FOREIGN KEY (id)
                REFERENCES users(id) ON UPDATE CASCADE
                                                ON DELETE CASCADE)';
        // execute the sql commands to create new tables
        $this->addSql($command);

    }

    public function down(Schema $schema): void
    {
        $command = 
            'DROP TABLE users'
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
