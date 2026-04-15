<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251219211307 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE IF EXISTS configuracao_id_seq CASCADE');
        $this->addSql('CREATE TABLE aula (id SERIAL NOT NULL, usuario_id INT NOT NULL, modulo_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nome VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_31990A4DB38439E ON aula (usuario_id)');
        $this->addSql('CREATE INDEX IDX_31990A4C07F55F5 ON aula (modulo_id)');
        $this->addSql('COMMENT ON COLUMN aula.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN aula.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE comentario (id SERIAL NOT NULL, usuario_id INT NOT NULL, post_id INT NOT NULL, conteudo TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, prioridade INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4B91E702DB38439E ON comentario (usuario_id)');
        $this->addSql('CREATE INDEX IDX_4B91E7024B89032C ON comentario (post_id)');
        $this->addSql('COMMENT ON COLUMN comentario.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN comentario.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN comentario.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE curso (id SERIAL NOT NULL, usuario_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nome VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CA3B40ECDB38439E ON curso (usuario_id)');
        $this->addSql('COMMENT ON COLUMN curso.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN curso.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE invitation_token (id SERIAL NOT NULL, user_id INT DEFAULT NULL, invitation_token VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_33FC351AA76ED395 ON invitation_token (user_id)');
        $this->addSql('CREATE TABLE modulo (id SERIAL NOT NULL, usuario_id INT NOT NULL, curso_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nome VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_ECF1CF36DB38439E ON modulo (usuario_id)');
        $this->addSql('CREATE INDEX IDX_ECF1CF3687CB4A1F ON modulo (curso_id)');
        $this->addSql('COMMENT ON COLUMN modulo.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN modulo.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE post (id SERIAL NOT NULL, usuario_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, nome VARCHAR(255) NOT NULL, conteudo TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DDB38439E ON post (usuario_id)');
        $this->addSql('COMMENT ON COLUMN post.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN post.deleted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE aula ADD CONSTRAINT FK_31990A4DB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE aula ADD CONSTRAINT FK_31990A4C07F55F5 FOREIGN KEY (modulo_id) REFERENCES modulo (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E702DB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE comentario ADD CONSTRAINT FK_4B91E7024B89032C FOREIGN KEY (post_id) REFERENCES post (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE curso ADD CONSTRAINT FK_CA3B40ECDB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE invitation_token ADD CONSTRAINT FK_33FC351AA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modulo ADD CONSTRAINT FK_ECF1CF36DB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE modulo ADD CONSTRAINT FK_ECF1CF3687CB4A1F FOREIGN KEY (curso_id) REFERENCES curso (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DDB38439E FOREIGN KEY (usuario_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE aula DROP CONSTRAINT FK_31990A4DB38439E');
        $this->addSql('ALTER TABLE aula DROP CONSTRAINT FK_31990A4C07F55F5');
        $this->addSql('ALTER TABLE comentario DROP CONSTRAINT FK_4B91E702DB38439E');
        $this->addSql('ALTER TABLE comentario DROP CONSTRAINT FK_4B91E7024B89032C');
        $this->addSql('ALTER TABLE curso DROP CONSTRAINT FK_CA3B40ECDB38439E');
        $this->addSql('ALTER TABLE invitation_token DROP CONSTRAINT FK_33FC351AA76ED395');
        $this->addSql('ALTER TABLE modulo DROP CONSTRAINT FK_ECF1CF36DB38439E');
        $this->addSql('ALTER TABLE modulo DROP CONSTRAINT FK_ECF1CF3687CB4A1F');
        $this->addSql('ALTER TABLE post DROP CONSTRAINT FK_5A8A6C8DDB38439E');
        $this->addSql('DROP TABLE aula');
        $this->addSql('DROP TABLE comentario');
        $this->addSql('DROP TABLE curso');
        $this->addSql('DROP TABLE invitation_token');
        $this->addSql('DROP TABLE modulo');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE "user"');
    }
}
