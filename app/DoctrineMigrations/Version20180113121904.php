<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180113121904 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abonament DROP Picture');
        $this->addSql('ALTER TABLE curs_abonament DROP FOREIGN KEY curs_abonament__abonament_fk');
        $this->addSql('ALTER TABLE curs_abonament DROP FOREIGN KEY curs_abonament__curs_fk');
        $this->addSql('DROP INDEX curs_abonament__abonament_fk ON curs_abonament');
        $this->addSql('CREATE INDEX IDX_78B0011D85BD5BCC ON curs_abonament (IdAbonament)');
        $this->addSql('DROP INDEX curs_fk_idx ON curs_abonament');
        $this->addSql('CREATE INDEX IDX_78B0011DFAB94D10 ON curs_abonament (IdCurs)');
        $this->addSql('ALTER TABLE curs_abonament ADD CONSTRAINT curs_abonament__abonament_fk FOREIGN KEY (IdAbonament) REFERENCES abonament (AbonamentId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE curs_abonament ADD CONSTRAINT curs_abonament__curs_fk FOREIGN KEY (IdCurs) REFERENCES curs (CursId) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE curs DROP Picture');
        $this->addSql('ALTER TABLE feedback CHANGE Id Id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE observatii_curs CHANGE Id Id INT AUTO_INCREMENT NOT NULL');
        $this->addSql('ALTER TABLE profile DROP Picture');
        $this->addSql('ALTER TABLE schedule CHANGE Id Id INT AUTO_INCREMENT NOT NULL, CHANGE IdCurs IdCurs INT DEFAULT NULL, CHANGE IdTrainer IdTrainer INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE RolId RolId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_abonament CHANGE IdUser IdUser INT DEFAULT NULL, CHANGE IdAbonament IdAbonament INT DEFAULT NULL, CHANGE Id Id INT AUTO_INCREMENT NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abonament ADD Picture VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE curs ADD Picture VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE curs_abonament DROP FOREIGN KEY FK_78B0011D85BD5BCC');
        $this->addSql('ALTER TABLE curs_abonament DROP FOREIGN KEY FK_78B0011DFAB94D10');
        $this->addSql('DROP INDEX idx_78b0011dfab94d10 ON curs_abonament');
        $this->addSql('CREATE INDEX curs_fk_idx ON curs_abonament (IdCurs)');
        $this->addSql('DROP INDEX idx_78b0011d85bd5bcc ON curs_abonament');
        $this->addSql('CREATE INDEX curs_abonament__abonament_fk ON curs_abonament (IdAbonament)');
        $this->addSql('ALTER TABLE curs_abonament ADD CONSTRAINT FK_78B0011D85BD5BCC FOREIGN KEY (IdAbonament) REFERENCES abonament (AbonamentId)');
        $this->addSql('ALTER TABLE curs_abonament ADD CONSTRAINT FK_78B0011DFAB94D10 FOREIGN KEY (IdCurs) REFERENCES curs (CursId)');
        $this->addSql('ALTER TABLE feedback CHANGE Id Id INT NOT NULL');
        $this->addSql('ALTER TABLE observatii_curs CHANGE Id Id INT NOT NULL');
        $this->addSql('ALTER TABLE profile ADD Picture VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE schedule CHANGE Id Id INT NOT NULL, CHANGE IdCurs IdCurs INT NOT NULL, CHANGE IdTrainer IdTrainer INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE RolId RolId INT NOT NULL');
        $this->addSql('ALTER TABLE user_abonament CHANGE Id Id INT NOT NULL, CHANGE IdAbonament IdAbonament INT NOT NULL, CHANGE IdUser IdUser INT NOT NULL');
    }
}
