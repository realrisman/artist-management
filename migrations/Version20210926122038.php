<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210926122038 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE representative_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, unid INT NOT NULL, date DATETIME NOT NULL, old MEDIUMTEXT NOT NULL, new MEDIUMTEXT NOT NULL, spot_checked TINYINT(1) NOT NULL, INDEX IDX_D749A93A76ED395 (user_id), INDEX unid_idx (unid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE representative_log ADD CONSTRAINT FK_D749A93A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD first_name VARCHAR(255) DEFAULT NULL, ADD last_name VARCHAR(255) DEFAULT NULL, ADD monthly_limit INT DEFAULT NULL, ADD limit_used INT DEFAULT NULL, ADD email_sync VARCHAR(255) DEFAULT NULL, CHANGE username login VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE representative_log');
        $this->addSql('ALTER TABLE user DROP first_name, DROP last_name, DROP monthly_limit, DROP limit_used, DROP email_sync, CHANGE login username VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
