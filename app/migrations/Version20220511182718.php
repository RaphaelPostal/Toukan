<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220511182718 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE establishment_user');
        $this->addSql('ALTER TABLE `order` ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE user ADD session_id VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE establishment_user (establishment_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_22EE7D5A8565851 (establishment_id), INDEX IDX_22EE7D5AA76ED395 (user_id), PRIMARY KEY(establishment_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE establishment_user ADD CONSTRAINT FK_22EE7D5A8565851 FOREIGN KEY (establishment_id) REFERENCES establishment (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE establishment_user ADD CONSTRAINT FK_22EE7D5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `order` DROP status');
        $this->addSql('ALTER TABLE user DROP session_id');
    }
}
