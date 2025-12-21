<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221032105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comentario ADD comentariopai_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E7026F930810 FOREIGN KEY (comentariopai_id) REFERENCES comentario (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_4B91E7026F930810 ON comentario (comentariopai_id)');
        $this->addSql('ALTER TABLE "user" ALTER nome DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ALTER apelido DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE comentario DROP CONSTRAINT FK_4B91E7026F930810');
        $this->addSql('DROP INDEX IDX_4B91E7026F930810');
        $this->addSql('ALTER TABLE comentario DROP comentariopai_id');
        $this->addSql('DROP INDEX "primary"');
        $this->addSql('ALTER TABLE "user" ALTER nome SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE "user" ALTER apelido SET DEFAULT \'\'');
    }
}
