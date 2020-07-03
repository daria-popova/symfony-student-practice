<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200708222048 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE offer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE property_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE property_value_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE section_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE offer (id INT NOT NULL, product_id INT NOT NULL, name VARCHAR(255) NOT NULL, xml_id VARCHAR(255) DEFAULT NULL, price DOUBLE PRECISION NOT NULL, quantity INT NOT NULL, unit VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, picture VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_29D6873E4584665A ON offer (product_id)');
        $this->addSql('CREATE TABLE product (id INT NOT NULL, name VARCHAR(255) NOT NULL, active BOOLEAN NOT NULL, vendor VARCHAR(255) DEFAULT NULL, vat_rate DOUBLE PRECISION NOT NULL, xml_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_section (product_id INT NOT NULL, section_id INT NOT NULL, PRIMARY KEY(product_id, section_id))');
        $this->addSql('CREATE INDEX IDX_FCAA615F4584665A ON product_section (product_id)');
        $this->addSql('CREATE INDEX IDX_FCAA615FD823E37A ON product_section (section_id)');
        $this->addSql('CREATE TABLE property (id INT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, sort INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE property_value (id INT NOT NULL, offer_id INT NOT NULL, property_id INT NOT NULL, value VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DB64993953C674EE ON property_value (offer_id)');
        $this->addSql('CREATE INDEX IDX_DB649939549213EC ON property_value (property_id)');
        $this->addSql('CREATE TABLE section (id INT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, xml_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D737AEF727ACA70 ON section (parent_id)');
        $this->addSql('ALTER TABLE offer ADD CONSTRAINT FK_29D6873E4584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_section ADD CONSTRAINT FK_FCAA615F4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE product_section ADD CONSTRAINT FK_FCAA615FD823E37A FOREIGN KEY (section_id) REFERENCES section (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_value ADD CONSTRAINT FK_DB64993953C674EE FOREIGN KEY (offer_id) REFERENCES offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE property_value ADD CONSTRAINT FK_DB649939549213EC FOREIGN KEY (property_id) REFERENCES property (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE section ADD CONSTRAINT FK_2D737AEF727ACA70 FOREIGN KEY (parent_id) REFERENCES section (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE property_value DROP CONSTRAINT FK_DB64993953C674EE');
        $this->addSql('ALTER TABLE offer DROP CONSTRAINT FK_29D6873E4584665A');
        $this->addSql('ALTER TABLE product_section DROP CONSTRAINT FK_FCAA615F4584665A');
        $this->addSql('ALTER TABLE property_value DROP CONSTRAINT FK_DB649939549213EC');
        $this->addSql('ALTER TABLE product_section DROP CONSTRAINT FK_FCAA615FD823E37A');
        $this->addSql('ALTER TABLE section DROP CONSTRAINT FK_2D737AEF727ACA70');
        $this->addSql('DROP SEQUENCE offer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE property_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE property_value_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE section_id_seq CASCADE');
        $this->addSql('DROP TABLE offer');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_section');
        $this->addSql('DROP TABLE property');
        $this->addSql('DROP TABLE property_value');
        $this->addSql('DROP TABLE section');
    }
}
