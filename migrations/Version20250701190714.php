<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250701190714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_post_hotness ON post (hotness)');
        $this->addSql('CREATE INDEX idx_post_view_count ON post (view_count)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_post_hotness');
        $this->addSql('DROP INDEX idx_post_view_count');
    }
}
