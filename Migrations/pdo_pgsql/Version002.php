<?php

namespace CanalTP\NmmPortalBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version002 extends AbstractMigration
{
    const VERSION = '0.0.2';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = 'CanalTPMttBundle' WHERE app_canonical_name='mtt'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = 'CanalTPMatrixBundle' WHERE app_canonical_name='matrix'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = 'CanalTPNmpAdminBundle' WHERE app_canonical_name='nmpadmin'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = 'CanalTPRealTimeBundle' WHERE app_canonical_name='realtime'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = 'CanalTPNmmPortalBridgeBundle' WHERE app_canonical_name='samcore'");
    }

    public function down(Schema $schema)
    {
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = '' WHERE app_canonical_name='mtt'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = '' WHERE app_canonical_name='matrix'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = '' WHERE app_canonical_name='nmpadmin'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = '' WHERE app_canonical_name='realtime'");
        $this->addSql("UPDATE tr_application_app SET app_bundle_name = '' WHERE app_canonical_name='samcore'");
    }
}
