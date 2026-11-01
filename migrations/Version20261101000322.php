<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101000322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dates of the shows (past ones on the show page, upcoming ones on the agenda) and photo carousel';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE performance (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, place VARCHAR(120) NOT NULL, event VARCHAR(150) DEFAULT NULL, show_id INT NOT NULL, INDEX IDX_82D79681D0C1FC64 (show_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE performance ADD CONSTRAINT FK_82D79681D0C1FC64 FOREIGN KEY (show_id) REFERENCES theater_show (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE theater_show ADD gallery_photos JSON DEFAULT \'[]\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE performance DROP FOREIGN KEY FK_82D79681D0C1FC64');
        $this->addSql('DROP TABLE performance');
        $this->addSql('ALTER TABLE theater_show DROP gallery_photos');
    }
}
