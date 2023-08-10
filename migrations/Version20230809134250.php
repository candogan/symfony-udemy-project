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
        return 'Initial migration for bank-bamboo project';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
CREATE DATABASE IF NOT EXISTS bamboo;
CREATE USER IF NOT EXISTS bamboo;
GRANT ALL PRIVILEGES ON *.* TO 'bamboo'@'%' IDENTIFIED BY 'localsecretpassword';
FLUSH PRIVILEGES;
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
DROP DATABASE IF EXISTS bamboo;
DROP USER bamboo;
SQL
        );
    }
}
