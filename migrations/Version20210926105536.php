<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210926105536 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_64C19C1727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE celebrity (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, bio LONGTEXT DEFAULT NULL, profession VARCHAR(255) DEFAULT NULL, birthdate DATE DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(40) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, price VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, youtube VARCHAR(40) DEFAULT NULL, created DATETIME NOT NULL, valid_from DATETIME NOT NULL, valid_till DATETIME NOT NULL, unid INT NOT NULL, deleted SMALLINT NOT NULL, direct_address VARCHAR(255) DEFAULT NULL, source LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, image_title VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(255) DEFAULT NULL, deceased TINYINT(1) NOT NULL, hiatus TINYINT(1) NOT NULL, previous_hits_count INT DEFAULT NULL, last_week_hits INT DEFAULT NULL, last_verified DATE DEFAULT NULL, needs_verify_flag NUMERIC(10, 2) DEFAULT NULL, verification_log TEXT DEFAULT NULL, remove_reason VARCHAR(500) DEFAULT NULL, self_managed TINYINT(1) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, unable_to_verify TINYINT(1) NOT NULL, spot_checked TINYINT(1) NOT NULL, INDEX IDX_88B7697A76ED395 (user_id), INDEX unid_idx (unid), INDEX needsverify_idx (needs_verify_flag), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE celebrity_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, unid INT NOT NULL, date DATETIME NOT NULL, old MEDIUMTEXT NOT NULL, new MEDIUMTEXT NOT NULL, spot_checked TINYINT(1) NOT NULL, INDEX IDX_64137572A76ED395 (user_id), INDEX unid_idx (unid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company (id INT AUTO_INCREMENT NOT NULL, primary_category_id INT DEFAULT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(255) NOT NULL, wp_id INT DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL, last_verified DATE DEFAULT NULL, needs_verify_flag NUMERIC(10, 2) DEFAULT NULL, verification_log TEXT DEFAULT NULL, last_updated_at DATETIME DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, image_title VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(255) DEFAULT NULL, source LONGTEXT DEFAULT NULL, INDEX IDX_4FBF094FB6A9FD63 (primary_category_id), INDEX IDX_4FBF094FA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_category (company_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_1EDB0CAC979B1AD6 (company_id), INDEX IDX_1EDB0CAC12469DE2 (category_id), PRIMARY KEY(company_id, category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE company_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, unid INT NOT NULL, date DATETIME NOT NULL, old MEDIUMTEXT NOT NULL, new MEDIUMTEXT NOT NULL, INDEX IDX_8BCCECADA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE email (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, deleted SMALLINT NOT NULL, status VARCHAR(255) NOT NULL, result VARCHAR(255) NOT NULL, last_updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE location (id INT AUTO_INCREMENT NOT NULL, company_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, postal_address LONGTEXT DEFAULT NULL, visitor_address LONGTEXT DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_5E9E89CB979B1AD6 (company_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE phone (id INT AUTO_INCREMENT NOT NULL, phone VARCHAR(255) NOT NULL, deleted SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE representative (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, location_id INT DEFAULT NULL, companyName VARCHAR(255) DEFAULT NULL, mailing_address VARCHAR(255) DEFAULT NULL, visitor_address VARCHAR(255) DEFAULT NULL, created DATETIME NOT NULL, valid_from DATETIME NOT NULL, valid_till DATETIME NOT NULL, unid INT NOT NULL, deleted SMALLINT NOT NULL, name VARCHAR(255) DEFAULT NULL, status VARCHAR(10) DEFAULT NULL, source LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, image_title VARCHAR(255) DEFAULT NULL, image_alt VARCHAR(255) DEFAULT NULL, previous_hits_count INT DEFAULT NULL, last_week_hits INT DEFAULT NULL, last_verified DATE DEFAULT NULL, needs_verify_flag NUMERIC(10, 2) DEFAULT NULL, verification_log TEXT DEFAULT NULL, remove_reason VARCHAR(500) DEFAULT NULL, allows_to_add_phone TINYINT(1) NOT NULL, instagram VARCHAR(255) DEFAULT NULL, unable_to_verify TINYINT(1) NOT NULL, spot_checked TINYINT(1) NOT NULL, INDEX IDX_2507390EA76ED395 (user_id), INDEX IDX_2507390E64D218E (location_id), INDEX unid_idx (unid), INDEX needsverify_idx (needs_verify_flag), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE representative_representative_type (representative_id INT NOT NULL, representative_type_id INT NOT NULL, INDEX IDX_BB768E5CFC3FF006 (representative_id), INDEX IDX_BB768E5C8D510F91 (representative_type_id), PRIMARY KEY(representative_id, representative_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE representative_company (representative_id INT NOT NULL, company_id INT NOT NULL, INDEX IDX_7A28A4DFC3FF006 (representative_id), INDEX IDX_7A28A4D979B1AD6 (company_id), PRIMARY KEY(representative_id, company_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE representative_connection (id INT AUTO_INCREMENT NOT NULL, representative_id INT DEFAULT NULL, company_id INT DEFAULT NULL, celebrity_id INT NOT NULL, territory VARCHAR(255) DEFAULT NULL, type VARCHAR(255) NOT NULL, last_verified DATE DEFAULT NULL, needs_verify_flag NUMERIC(10, 2) NOT NULL, verification_log TEXT DEFAULT NULL, created DATETIME DEFAULT NULL, is_company TINYINT(1) DEFAULT \'0\' NOT NULL, position INT DEFAULT NULL, INDEX IDX_59DB0011FC3FF006 (representative_id), INDEX IDX_59DB0011979B1AD6 (company_id), INDEX IDX_59DB00119D12EF95 (celebrity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE representative_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, unid INT NOT NULL, date DATETIME NOT NULL, old MEDIUMTEXT NOT NULL, new MEDIUMTEXT NOT NULL, spot_checked TINYINT(1) NOT NULL, INDEX IDX_D749A93A76ED395 (user_id), INDEX unid_idx (unid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE representative_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C1727ACA70 FOREIGN KEY (parent_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE celebrity ADD CONSTRAINT FK_88B7697A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE celebrity_log ADD CONSTRAINT FK_64137572A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FB6A9FD63 FOREIGN KEY (primary_category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE company ADD CONSTRAINT FK_4FBF094FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE company_category ADD CONSTRAINT FK_1EDB0CAC979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_category ADD CONSTRAINT FK_1EDB0CAC12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_log ADD CONSTRAINT FK_8BCCECADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE location ADD CONSTRAINT FK_5E9E89CB979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE representative ADD CONSTRAINT FK_2507390EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE representative ADD CONSTRAINT FK_2507390E64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE representative_representative_type ADD CONSTRAINT FK_BB768E5CFC3FF006 FOREIGN KEY (representative_id) REFERENCES representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE representative_representative_type ADD CONSTRAINT FK_BB768E5C8D510F91 FOREIGN KEY (representative_type_id) REFERENCES representative_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE representative_company ADD CONSTRAINT FK_7A28A4DFC3FF006 FOREIGN KEY (representative_id) REFERENCES representative (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE representative_company ADD CONSTRAINT FK_7A28A4D979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE representative_connection ADD CONSTRAINT FK_59DB0011FC3FF006 FOREIGN KEY (representative_id) REFERENCES representative (id)');
        $this->addSql('ALTER TABLE representative_connection ADD CONSTRAINT FK_59DB0011979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id)');
        $this->addSql('ALTER TABLE representative_connection ADD CONSTRAINT FK_59DB00119D12EF95 FOREIGN KEY (celebrity_id) REFERENCES celebrity (id)');
        $this->addSql('ALTER TABLE representative_log ADD CONSTRAINT FK_D749A93A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE category DROP FOREIGN KEY FK_64C19C1727ACA70');
        $this->addSql('ALTER TABLE company DROP FOREIGN KEY FK_4FBF094FB6A9FD63');
        $this->addSql('ALTER TABLE company_category DROP FOREIGN KEY FK_1EDB0CAC12469DE2');
        $this->addSql('ALTER TABLE representative_connection DROP FOREIGN KEY FK_59DB00119D12EF95');
        $this->addSql('ALTER TABLE company_category DROP FOREIGN KEY FK_1EDB0CAC979B1AD6');
        $this->addSql('ALTER TABLE location DROP FOREIGN KEY FK_5E9E89CB979B1AD6');
        $this->addSql('ALTER TABLE representative_company DROP FOREIGN KEY FK_7A28A4D979B1AD6');
        $this->addSql('ALTER TABLE representative_connection DROP FOREIGN KEY FK_59DB0011979B1AD6');
        $this->addSql('ALTER TABLE representative DROP FOREIGN KEY FK_2507390E64D218E');
        $this->addSql('ALTER TABLE representative_representative_type DROP FOREIGN KEY FK_BB768E5CFC3FF006');
        $this->addSql('ALTER TABLE representative_company DROP FOREIGN KEY FK_7A28A4DFC3FF006');
        $this->addSql('ALTER TABLE representative_connection DROP FOREIGN KEY FK_59DB0011FC3FF006');
        $this->addSql('ALTER TABLE representative_representative_type DROP FOREIGN KEY FK_BB768E5C8D510F91');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE celebrity');
        $this->addSql('DROP TABLE celebrity_log');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE company_category');
        $this->addSql('DROP TABLE company_log');
        $this->addSql('DROP TABLE email');
        $this->addSql('DROP TABLE location');
        $this->addSql('DROP TABLE phone');
        $this->addSql('DROP TABLE representative');
        $this->addSql('DROP TABLE representative_representative_type');
        $this->addSql('DROP TABLE representative_company');
        $this->addSql('DROP TABLE representative_connection');
        $this->addSql('DROP TABLE representative_log');
        $this->addSql('DROP TABLE representative_type');
    }
}
