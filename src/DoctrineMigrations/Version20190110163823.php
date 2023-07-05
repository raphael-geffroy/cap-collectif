<?php

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20190110163823 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
    }

    public function down(Schema $schema): void
    {
    }

    public function postUp(Schema $schema): void
    {
        $date = (new \DateTime())->format('Y-m-d H:i:s');
        $this->connection->insert('site_parameter', [
            'keyname' => 'global.site.communication_from',
            'value' => 'de Cap Collectif',
            'position' => 2,
            'category' => 'settings.global',
            'type' => 0,
            'created_at' => $date,
            'updated_at' => $date,
            'is_enabled' => 1,
        ]);
    }

    public function postDown(Schema $schema): void
    {
    }
}
