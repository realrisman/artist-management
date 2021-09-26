<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210926104533 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE celebrity (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, bio LONGTEXT DEFAULT NULL, profession VARCHAR(255) DEFAULT NULL, birthdate DATE DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(40) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, price VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, youtube VARCHAR(40) DEFAULT NULL, created DATETIME NOT NULL, valid_from DATETIME NOT NULL, valid_till DATETIME NOT NULL, unid INT NOT NULL, deleted SMALLINT NOT NULL, direct_address VARCHAR(255) DEFAULT NULL, source LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, image_title VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(255) DEFAULT NULL, deceased TINYINT(1) NOT NULL, hiatus TINYINT(1) NOT NULL, previous_hits_count INT DEFAULT NULL, last_week_hits INT DEFAULT NULL, last_verified DATE DEFAULT NULL, needs_verify_flag NUMERIC(10, 2) DEFAULT NULL, verification_log TEXT DEFAULT NULL, remove_reason VARCHAR(500) DEFAULT NULL, self_managed TINYINT(1) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, unable_to_verify TINYINT(1) NOT NULL, spot_checked TINYINT(1) NOT NULL, INDEX IDX_88B7697A76ED395 (user_id), INDEX unid_idx (unid), INDEX needsverify_idx (needs_verify_flag), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE celebrity_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, unid INT NOT NULL, date DATETIME NOT NULL, old MEDIUMTEXT NOT NULL, new MEDIUMTEXT NOT NULL, spot_checked TINYINT(1) NOT NULL, INDEX IDX_64137572A76ED395 (user_id), INDEX unid_idx (unid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, deleted SMALLINT NOT NULL, status VARCHAR(255) NOT NULL, result VARCHAR(255) NOT NULL, last_updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phone (id INT AUTO_INCREMENT NOT NULL, phone VARCHAR(255) NOT NULL, deleted SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE celebrity ADD CONSTRAINT FK_88B7697A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE celebrity_log ADD CONSTRAINT FK_64137572A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE celebrity');
        $this->addSql('DROP TABLE celebrity_log');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE phone');
    }
}
