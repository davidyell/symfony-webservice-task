<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211205204748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__expenses AS SELECT id, title, description, value FROM expenses');
        $this->addSql('DROP TABLE expenses');
        $this->addSql('CREATE TABLE expenses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type_id INTEGER DEFAULT 5 NULL, title VARCHAR(255) NOT NULL COLLATE BINARY, description CLOB DEFAULT NULL COLLATE BINARY, value NUMERIC(10, 2) NOT NULL, CONSTRAINT FK_2496F35BC54C8C93 FOREIGN KEY (type_id) REFERENCES expense_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO expenses (id, title, description, value) SELECT id, title, description, value FROM __temp__expenses');
        $this->addSql('DROP TABLE __temp__expenses');
        $this->addSql('CREATE INDEX IDX_2496F35BC54C8C93 ON expenses (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_2496F35BC54C8C93');
        $this->addSql('CREATE TEMPORARY TABLE __temp__expenses AS SELECT id, title, description, value FROM expenses');
        $this->addSql('DROP TABLE expenses');
        $this->addSql('CREATE TABLE expenses (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, value NUMERIC(10, 2) NOT NULL, expenses_type_id INTEGER DEFAULT 5 NOT NULL)');
        $this->addSql('INSERT INTO expenses (id, title, description, value) SELECT id, title, description, value FROM __temp__expenses');
        $this->addSql('DROP TABLE __temp__expenses');
    }
}
