<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261031233718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Teaser as an uploaded video; press photos become a list of images';
    }

    public function up(Schema $schema): void
    {
        // The column held one PDF or ZIP file: an image is kept as the first photo, anything else is dropped.
        $this->addSql("UPDATE theater_show SET press_photos = IF(press_photos REGEXP '\\\\.(jpe?g|png|gif|webp)$', JSON_ARRAY(press_photos), '[]')");
        $this->addSql('ALTER TABLE theater_show ADD teaser_file VARCHAR(255) DEFAULT NULL, CHANGE press_photos press_photos JSON DEFAULT \'[]\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE theater_show DROP teaser_file, CHANGE press_photos press_photos VARCHAR(255) DEFAULT NULL');
        // Only the first photo fits in the old column.
        $this->addSql("UPDATE theater_show SET press_photos = JSON_UNQUOTE(JSON_EXTRACT(press_photos, '$[0]'))");
    }
}
