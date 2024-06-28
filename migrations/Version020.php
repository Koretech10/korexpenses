<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Création de l'entité MonthlyTransaction";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE monthly_transaction (
                id INT AUTO_INCREMENT NOT NULL,
                account_id INT NOT NULL,
                day SMALLINT NOT NULL,
                description VARCHAR(255) NOT NULL,
                value DOUBLE PRECISION NOT NULL,
                type SMALLINT NOT NULL,
                next_occurrence DATE NOT NULL,
                INDEX IDX_EF2C6F1E9B6B5FBA (account_id), PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE monthly_transaction
            ADD CONSTRAINT FK_EF2C6F1E9B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('
            ALTER TABLE monthly_transaction
            DROP FOREIGN KEY FK_EF2C6F1E9B6B5FBA
        ');
        $this->addSql('DROP TABLE monthly_transaction');
    }
}
