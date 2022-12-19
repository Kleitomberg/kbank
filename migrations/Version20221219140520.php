<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221219140520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transacao (id INT AUTO_INCREMENT NOT NULL, destinatario_id INT DEFAULT NULL, remetente_id INT DEFAULT NULL, descricao VARCHAR(255) NOT NULL, valor VARCHAR(255) NOT NULL, data DATETIME NOT NULL, INDEX IDX_6C9E60CEB564FBC1 (destinatario_id), INDEX IDX_6C9E60CEFA0A674B (remetente_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transacao ADD CONSTRAINT FK_6C9E60CEB564FBC1 FOREIGN KEY (destinatario_id) REFERENCES conta (id)');
        $this->addSql('ALTER TABLE transacao ADD CONSTRAINT FK_6C9E60CEFA0A674B FOREIGN KEY (remetente_id) REFERENCES conta (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transacao DROP FOREIGN KEY FK_6C9E60CEB564FBC1');
        $this->addSql('ALTER TABLE transacao DROP FOREIGN KEY FK_6C9E60CEFA0A674B');
        $this->addSql('DROP TABLE transacao');
    }
}
