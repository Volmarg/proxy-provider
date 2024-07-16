<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230716060322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `call` (id INT AUTO_INCREMENT NOT NULL, proxy_id INT NOT NULL, started DATETIME NOT NULL, finished DATETIME DEFAULT NULL, called_url VARCHAR(255) NOT NULL, success TINYINT(1) NOT NULL, INDEX IDX_CC8E2F3EDB26A4E (proxy_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE proxy (id INT AUTO_INCREMENT NOT NULL, rotating TINYINT(1) NOT NULL, last_usage DATETIME NOT NULL, ip VARCHAR(255) NOT NULL, port INT NOT NULL, provider VARCHAR(255) NOT NULL, username VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `call` ADD CONSTRAINT FK_CC8E2F3EDB26A4E FOREIGN KEY (proxy_id) REFERENCES proxy (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `call` DROP FOREIGN KEY FK_CC8E2F3EDB26A4E');
        $this->addSql('DROP TABLE `call`');
        $this->addSql('DROP TABLE proxy');
    }
}
