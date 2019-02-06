<?php

namespace CanalTP\NmmPortalBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version003 extends AbstractMigration
{
    const VERSION = '0.0.3';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql("UPDATE tr_application_app SET app_default_route = '/realtime/disruptions/dashboard' WHERE app_canonical_name='realtime'");
    }

    public function down(Schema $schema)
    {
        $this->addSql("UPDATE tr_application_app SET app_default_route = '/realtime/network-status/' WHERE app_canonical_name='realtime'");
    }
}
