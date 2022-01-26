<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220124174811 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE partner_sales (id INT AUTO_INCREMENT NOT NULL, partner_id_id INT NOT NULL, item_date_time DATETIME NOT NULL, client_name VARCHAR(255) NOT NULL, product_name VARCHAR(255) NOT NULL, quantity INT NOT NULL, piece_price DOUBLE PRECISION NOT NULL, delivery_type VARCHAR(255) NOT NULL, delivery_city VARCHAR(255) NOT NULL, delivery_price DOUBLE PRECISION NOT NULL, total_price DOUBLE PRECISION NOT NULL, INDEX IDX_78BDDC216C783232 (partner_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE partners (id INT AUTO_INCREMENT NOT NULL, partner_name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE partner_sales ADD CONSTRAINT FK_78BDDC216C783232 FOREIGN KEY (partner_id_id) REFERENCES partners (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE partner_sales DROP FOREIGN KEY FK_78BDDC216C783232');
        $this->addSql('DROP TABLE partner_sales');
        $this->addSql('DROP TABLE partners');
    }
}
