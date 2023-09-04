<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230901073847 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event_participant DROP guest_first_name, DROP guest_last_name, DROP guests');
        $this->addSql('ALTER TABLE guests DROP FOREIGN KEY FK_4D11BCB29B321462');
        $this->addSql('DROP INDEX IDX_4D11BCB29B321462 ON guests');
        $this->addSql('ALTER TABLE guests CHANGE guestfrom_id event_participant_id INT NOT NULL');
        $this->addSql('ALTER TABLE guests ADD CONSTRAINT FK_4D11BCB24258866A FOREIGN KEY (event_participant_id) REFERENCES event_participant (id)');
        $this->addSql('CREATE INDEX IDX_4D11BCB24258866A ON guests (event_participant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE guests DROP FOREIGN KEY FK_4D11BCB24258866A');
        $this->addSql('DROP INDEX IDX_4D11BCB24258866A ON guests');
        $this->addSql('ALTER TABLE guests CHANGE event_participant_id guestfrom_id INT NOT NULL');
        $this->addSql('ALTER TABLE guests ADD CONSTRAINT FK_4D11BCB29B321462 FOREIGN KEY (guestfrom_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_4D11BCB29B321462 ON guests (guestfrom_id)');
        $this->addSql('ALTER TABLE event_participant ADD guest_first_name LONGTEXT DEFAULT NULL, ADD guest_last_name LONGTEXT DEFAULT NULL, ADD guests INT DEFAULT NULL');
    }
}
