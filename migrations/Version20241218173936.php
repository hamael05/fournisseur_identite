<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241218173936 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE jeton_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE jeton_inscription_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE tentative_mdp_failed_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE utilisateur_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE jeton (id INT NOT NULL, jeton TEXT NOT NULL, expiration_util_date_insertion TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expiration_util_date_expiration TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expiration_util_duree DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE jeton_inscription (id INT NOT NULL, id_jeton INT DEFAULT NULL, mail VARCHAR(255) NOT NULL, mdp TEXT NOT NULL, nom VARCHAR(100) NOT NULL, date_naissance DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_12BAF9C2EC4A254B ON jeton_inscription (id_jeton)');
        $this->addSql('CREATE TABLE tentative_mdp_failed (id INT NOT NULL, utilisateur_id INT NOT NULL, compteur_tentative INT NOT NULL, date_derniere_tentative TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_locked BOOLEAN NOT NULL, unlock_time TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3990186EFB88E14F ON tentative_mdp_failed (utilisateur_id)');
        $this->addSql('CREATE TABLE utilisateur (id INT NOT NULL, mail VARCHAR(255) NOT NULL, mdp VARCHAR(255) NOT NULL, nom VARCHAR(100) NOT NULL, date_naissance DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B35126AC48 ON utilisateur (mail)');
        $this->addSql('ALTER TABLE jeton_inscription ADD CONSTRAINT FK_12BAF9C2EC4A254B FOREIGN KEY (id_jeton) REFERENCES jeton (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE tentative_mdp_failed ADD CONSTRAINT FK_3990186EFB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE jeton_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE jeton_inscription_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE tentative_mdp_failed_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE utilisateur_id_seq CASCADE');
        $this->addSql('ALTER TABLE jeton_inscription DROP CONSTRAINT FK_12BAF9C2EC4A254B');
        $this->addSql('ALTER TABLE tentative_mdp_failed DROP CONSTRAINT FK_3990186EFB88E14F');
        $this->addSql('DROP TABLE jeton');
        $this->addSql('DROP TABLE jeton_inscription');
        $this->addSql('DROP TABLE tentative_mdp_failed');
        $this->addSql('DROP TABLE utilisateur');
    }
}
