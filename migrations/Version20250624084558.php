<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250624084558 extends AbstractMigration {
	public function getDescription(): string {
		return 'Add customerId property to user entity';
	}

	public function up(Schema $schema): void {
		$this->addSql('ALTER TABLE user ADD ls_customer_id VARCHAR(255) DEFAULT NULL');
		$this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649C650C0EB ON user (ls_customer_id)');
	}

	public function down(Schema $schema): void {
		$this->addSql('DROP INDEX UNIQ_8D93D649C650C0EB ON user');
		$this->addSql('ALTER TABLE user DROP ls_customer_id');
	}
}
