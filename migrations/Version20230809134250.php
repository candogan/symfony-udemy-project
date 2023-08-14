<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230809134250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration for symfony-udemy project';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
CREATE DATABASE IF NOT EXISTS udemy;
CREATE USER IF NOT EXISTS udemy;
GRANT ALL PRIVILEGES ON *.* TO 'udemy'@'%' IDENTIFIED BY 'localsecretpassword';
FLUSH PRIVILEGES;
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
DROP DATABASE IF EXISTS udemy;
DROP USER udemy;
SQL
        );
    }
}
