<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251220190533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ALTER introducao DROP DEFAULT');
        $this->addSql('ALTER TABLE "user" ADD nome VARCHAR(255) NOT NULL default \'\'');
        $this->addSql('ALTER TABLE "user" ADD apelido VARCHAR(255) NOT NULL default \'\'');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D64954BD530C ON "user" (nome)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D6493707AD ON "user" (apelido)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_8D93D649E7927C74');
        $this->addSql('DROP INDEX UNIQ_8D93D64954BD530C');
        $this->addSql('DROP INDEX UNIQ_8D93D6493707AD');
        $this->addSql('ALTER TABLE "user" DROP nome');
        $this->addSql('ALTER TABLE "user" DROP apelido');
        $this->addSql('ALTER TABLE post ALTER introducao SET DEFAULT \'\'');
    }
}
