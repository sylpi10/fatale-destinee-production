<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261031205335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial MariaDB schema: users, shows and their credits';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE show_credit (id INT AUTO_INCREMENT NOT NULL, role VARCHAR(80) NOT NULL, people LONGTEXT NOT NULL, position INT NOT NULL, show_id INT NOT NULL, INDEX IDX_93A64165D0C1FC64 (show_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE theater_show (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, slug VARCHAR(160) NOT NULL, published TINYINT NOT NULL, position INT NOT NULL, author VARCHAR(150) DEFAULT NULL, genre VARCHAR(60) DEFAULT NULL, creation_year INT DEFAULT NULL, minimum_age INT DEFAULT NULL, venues VARCHAR(40) DEFAULT NULL, duration_minutes INT DEFAULT NULL, catchphrase LONGTEXT DEFAULT NULL, synopsis LONGTEXT DEFAULT NULL, teaser_url VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, press_kit VARCHAR(255) DEFAULT NULL, technical_rider VARCHAR(255) DEFAULT NULL, stands_technical_rider VARCHAR(255) DEFAULT NULL, press_photos VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_D42F5FAF989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified TINYINT DEFAULT 0 NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE show_credit ADD CONSTRAINT FK_93A64165D0C1FC64 FOREIGN KEY (show_id) REFERENCES theater_show (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE show_credit DROP FOREIGN KEY FK_93A64165D0C1FC64');
        $this->addSql('DROP TABLE show_credit');
        $this->addSql('DROP TABLE theater_show');
        $this->addSql('DROP TABLE user');
    }
}
