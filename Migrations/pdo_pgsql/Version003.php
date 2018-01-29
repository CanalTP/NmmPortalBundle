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

    /**
     * Update / create constraints on customer / user tables
     *
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE public.t_user_usr
          ADD CONSTRAINT fk_customer_user FOREIGN KEY (cus_id)
            REFERENCES public.tr_customer_cus(cus_id)
            ON UPDATE CASCADE ON DELETE CASCADE'
        );

        $this->addSql('ALTER TABLE public.tj_user_role_ur
          DROP CONSTRAINT fk_cc9b191bc69d3fb,
          ADD CONSTRAINT fk_cc9b191bc69d3fb FOREIGN KEY (usr_id)
          REFERENCES public.t_user_usr (usr_id) MATCH SIMPLE
          ON UPDATE CASCADE ON DELETE CASCADE'
        );

    }

    /**
     * Resets constraints on customer / user tables
     *
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE public.tj_user_role_ur
          DROP CONSTRAINT fk_cc9b191bc69d3fb,
          ADD CONSTRAINT fk_cc9b191bc69d3fb FOREIGN KEY (usr_id)
          REFERENCES public.t_user_usr (usr_id) MATCH SIMPLE
          ON UPDATE NO ACTION ON DELETE NO ACTION'
        );

        $this->addSql('ALTER TABLE public.t_user_usr
          DROP CONSTRAINT fk_customer_user'
        );
    }
}
