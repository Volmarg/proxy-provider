<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Migrations\Exception\IrreversibleMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240630084612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add dummy localhost proxies';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            INSERT INTO `proxy` (`id`, `rotating`, `last_usage`, `ip`, `port`, `provider`, `username`, `password`, `enabled`, `info`, `internal_id`, `usage`, `target_country_iso_code`) VALUES
            (1,	0,	NOW(), '127.0.0.1', 80, 'Localhost', NULL,	NULL, 1, 'Localhost', 'LOCALHOST_GENERIC', 'GENERIC', NULL);
        ");

        $this->addSql("
            INSERT INTO `proxy` (`id`, `rotating`, `last_usage`, `ip`, `port`, `provider`, `username`, `password`, `enabled`, `info`, `internal_id`, `usage`, `target_country_iso_code`) VALUES
            (2,	0,	NOW(), '127.0.0.1', 81, 'Localhost', NULL,	NULL, 1, 'Localhost', 'LOCALHOST_SERP', 'SERP', NULL);
        ");

        $this->addSql("
            INSERT INTO `proxy` (`id`, `rotating`, `last_usage`, `ip`, `port`, `provider`, `username`, `password`, `enabled`, `info`, `internal_id`, `usage`, `target_country_iso_code`) VALUES
            (3,	0,	NOW(), '127.0.0.1', 82, 'Localhost', NULL,	NULL, 1, 'Localhost', 'LOCALHOST_UNLOCKER', 'UNLOCKER', 'pol');
        ");

    }

    public function down(Schema $schema): void
    {
        throw new IrreversibleMigration();
    }
}
