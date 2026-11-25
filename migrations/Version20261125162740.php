<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261125162740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Alternative text of the main photo of a show and of the company image';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company ADD image_alt VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE theater_show ADD image_alt VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE company DROP image_alt');
        $this->addSql('ALTER TABLE theater_show DROP image_alt');
    }
}
