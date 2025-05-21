<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521213935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP token2_fa, DROP expiracion2_fa');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BDF55A635F37A13B ON user_token (token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_BDF55A635F37A13B ON user_token');
        $this->addSql('ALTER TABLE user ADD token2_fa VARCHAR(255) DEFAULT NULL, ADD expiracion2_fa DATETIME DEFAULT NULL');
    }
}
