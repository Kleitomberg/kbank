<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221217131640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conta ADD tipo_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE conta ADD CONSTRAINT FK_485A16C3A9276E6C FOREIGN KEY (tipo_id) REFERENCES tipo_conta (id)');
        $this->addSql('CREATE INDEX IDX_485A16C3A9276E6C ON conta (tipo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conta DROP FOREIGN KEY FK_485A16C3A9276E6C');
        $this->addSql('DROP INDEX IDX_485A16C3A9276E6C ON conta');
        $this->addSql('ALTER TABLE conta DROP tipo_id');
    }
}
