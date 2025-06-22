<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250622190816 extends AbstractMigration {
	public function getDescription(): string {
		return 'add a column to store the LS variant ID on product';
	}

	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE product ADD ls_variant_id VARCHAR(255) DEFAULT NULL');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_D34A04ADFB4C9E18 ON product (ls_variant_id)');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('DROP INDEX UNIQ_D34A04ADFB4C9E18 ON product');
		$this->addSql('ALTER TABLE product DROP ls_variant_id');
	}
}
