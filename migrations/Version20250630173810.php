<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630173810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX idx_post_hotness ON post (hotness)');
        $this->addSql('CREATE INDEX idx_post_view_count ON post (view_count)');
        $this->addSql('CREATE INDEX idx_user_view_user_post ON user_view (user_id, post_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_post_hotness');
        $this->addSql('DROP INDEX idx_post_view_count');
        $this->addSql('DROP INDEX idx_user_view_user_post');
    }
}
