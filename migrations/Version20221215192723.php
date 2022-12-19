<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221215192723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_306C486DE7927C74 ON gerente');
        $this->addSql('ALTER TABLE gerente ADD user_id INT DEFAULT NULL, DROP email, DROP roles, DROP password, DROP is_verified, DROP nome, DROP cpf, DROP celular, DROP tipo');
        $this->addSql('ALTER TABLE gerente ADD CONSTRAINT FK_306C486DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_306C486DA76ED395 ON gerente (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE gerente DROP FOREIGN KEY FK_306C486DA76ED395');
        $this->addSql('DROP INDEX UNIQ_306C486DA76ED395 ON gerente');
        $this->addSql('ALTER TABLE gerente ADD email VARCHAR(180) NOT NULL, ADD roles JSON NOT NULL, ADD password VARCHAR(255) NOT NULL, ADD is_verified TINYINT(1) NOT NULL, ADD nome VARCHAR(255) NOT NULL, ADD cpf VARCHAR(255) NOT NULL, ADD celular VARCHAR(255) NOT NULL, ADD tipo VARCHAR(255) NOT NULL, DROP user_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_306C486DE7927C74 ON gerente (email)');
    }
}
