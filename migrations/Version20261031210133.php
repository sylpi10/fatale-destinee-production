<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261031210133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Users log in with a username instead of an email';
    }

    public function up(Schema $schema): void
    {
        // Keep existing accounts: the part before the "@" becomes the username.
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON user');
        $this->addSql("UPDATE user SET email = LEFT(SUBSTRING_INDEX(email, '@', 1), 50)");
        $this->addSql('ALTER TABLE user CHANGE email username VARCHAR(50) NOT NULL, DROP is_verified');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON user (username)');
    }

    public function down(Schema $schema): void
    {
        // The username is kept in the email column: emails have to be fixed by hand afterwards.
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_USERNAME ON user');
        $this->addSql('ALTER TABLE user CHANGE username email VARCHAR(180) NOT NULL, ADD is_verified TINYINT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON user (email)');
    }
}
