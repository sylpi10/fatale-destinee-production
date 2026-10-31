<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261031212715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Company presentation; users become members of the company (name, photo, bio) and carry shows';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, image VARCHAR(255) DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(80) DEFAULT NULL, ADD last_name VARCHAR(80) DEFAULT NULL, ADD photo VARCHAR(255) DEFAULT NULL, ADD bio LONGTEXT DEFAULT NULL, ADD member TINYINT DEFAULT 0 NOT NULL, ADD position INT DEFAULT 0 NOT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE theater_show ADD member_id INT DEFAULT NULL');

        // Keep "Porté par": each author becomes a member (without password, so no login)
        // and gets its shows back. "Teddy Moulin" → username "teddy.moulin".
        $this->addSql(<<<'SQL'
            INSERT INTO user (username, roles, password, first_name, last_name, member)
            SELECT DISTINCT LOWER(REPLACE(TRIM(author), ' ', '.')), '[]', NULL,
                SUBSTRING_INDEX(TRIM(author), ' ', 1),
                NULLIF(TRIM(SUBSTRING(TRIM(author), LENGTH(SUBSTRING_INDEX(TRIM(author), ' ', 1)) + 1)), ''),
                1
            FROM theater_show
            WHERE author IS NOT NULL AND TRIM(author) <> ''
                AND LOWER(REPLACE(TRIM(author), ' ', '.')) NOT IN (SELECT username FROM user)
            SQL);
        $this->addSql("UPDATE theater_show s JOIN user u ON u.username = LOWER(REPLACE(TRIM(s.author), ' ', '.')) SET s.member_id = u.id");

        $this->addSql('ALTER TABLE theater_show DROP author');
        $this->addSql('ALTER TABLE theater_show ADD CONSTRAINT FK_D42F5FAF7597D3FE FOREIGN KEY (member_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D42F5FAF7597D3FE ON theater_show (member_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE company');
        $this->addSql('ALTER TABLE theater_show DROP FOREIGN KEY FK_D42F5FAF7597D3FE');
        $this->addSql('DROP INDEX IDX_D42F5FAF7597D3FE ON theater_show');
        $this->addSql('ALTER TABLE theater_show ADD author VARCHAR(150) DEFAULT NULL');
        $this->addSql("UPDATE theater_show s JOIN user u ON u.id = s.member_id SET s.author = TRIM(CONCAT_WS(' ', u.first_name, u.last_name))");
        $this->addSql('ALTER TABLE theater_show DROP member_id');
        // Members without password can't go back to a NOT NULL password column.
        $this->addSql('DELETE FROM user WHERE password IS NULL');
        $this->addSql('ALTER TABLE user DROP first_name, DROP last_name, DROP photo, DROP bio, DROP member, DROP position, CHANGE password password VARCHAR(255) NOT NULL');
    }
}
