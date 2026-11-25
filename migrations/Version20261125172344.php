<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261125172344 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Legal notice details of the company (form, SIRET, address, publication director)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company ADD legal_form VARCHAR(100) DEFAULT NULL, ADD registration_number VARCHAR(50) DEFAULT NULL, ADD address LONGTEXT DEFAULT NULL, ADD publication_director VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company DROP legal_form, DROP registration_number, DROP address, DROP publication_director');
    }
}
