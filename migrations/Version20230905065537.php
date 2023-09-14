<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230905065537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE member_events ADD cover_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE member_events ADD CONSTRAINT FK_7751D579922726E9 FOREIGN KEY (cover_id) REFERENCES images (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7751D579922726E9 ON member_events (cover_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE member_events DROP FOREIGN KEY FK_7751D579922726E9');
        $this->addSql('DROP INDEX UNIQ_7751D579922726E9 ON member_events');
        $this->addSql('ALTER TABLE member_events DROP cover_id');
    }
}
