<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return "Création de l'entité Transaction";
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('
            CREATE TABLE account (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(255) NOT NULL,
                balance DOUBLE PRECISION NOT NULL,
                PRIMARY KEY(id)
            ) 
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE transaction (
                id INT AUTO_INCREMENT NOT NULL,
                account_id INT NOT NULL,
                date DATE NOT NULL,
                description VARCHAR(255) NOT NULL,
                value DOUBLE PRECISION NOT NULL,
                type SMALLINT NOT NULL,
                INDEX IDX_723705D19B6B5FBA (account_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE user (
                id INT AUTO_INCREMENT NOT NULL,
                username VARCHAR(180) NOT NULL,
                roles JSON NOT NULL COMMENT \'(DC2Type:json)\',
                password VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_8D93D649F85E0677 (username),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            CREATE TABLE messenger_messages (
                id BIGINT AUTO_INCREMENT NOT NULL,
                body LONGTEXT NOT NULL,
                headers LONGTEXT NOT NULL,
                queue_name VARCHAR(190) NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX IDX_75EA56E0FB7336F0 (queue_name),
                INDEX IDX_75EA56E0E3BD61CE (available_at),
                INDEX IDX_75EA56E016BA31DB (delivered_at),
                PRIMARY KEY(id)
            ) 
            DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
        $this->addSql('
            ALTER TABLE transaction
            ADD CONSTRAINT FK_723705D19B6B5FBA FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE
        ');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('
            ALTER TABLE transaction 
            DROP FOREIGN KEY FK_723705D19B6B5FBA
        ');
        $this->addSql('DROP TABLE account');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('DROP TABLE user');
    }
}
