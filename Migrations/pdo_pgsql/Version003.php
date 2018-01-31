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

        $this->addSql('
          ALTER TABLE public.tj_customer_application_cap
          DROP CONSTRAINT fk_9425a57f19eb6921, 
          ADD CONSTRAINT fk_9425a57f19eb6921 
          FOREIGN KEY (customer_id) 
          REFERENCES public.tr_customer_cus (cus_id) 
          ON DELETE CASCADE;');

        $this->addSql('
          ALTER TABLE public.tj_customer_application_cap
          DROP CONSTRAINT fk_9425a57f3e030acd, 
          ADD CONSTRAINT fk_9425a57f3e030acd 
          FOREIGN KEY (application_id) 
          REFERENCES public.tr_application_app (app_id) 
          ON DELETE CASCADE;');

        $this->addSql('
          ALTER TABLE public.t_navitia_token_nat
          DROP CONSTRAINT fk_356e8dadf03a7216, 
          ADD CONSTRAINT fk_356e8dadf03a7216 
          FOREIGN KEY (nav_id) 
          REFERENCES public.t_navitia_entity_nav (nav_id) 
          ON DELETE CASCADE;');

        $this->addSql('
          ALTER TABLE public.t_role_rol
          DROP CONSTRAINT fk_6ed6a71f7987212d, 
          ADD CONSTRAINT fk_6ed6a71f7987212d 
          FOREIGN KEY (app_id) 
          REFERENCES public.tr_application_app (app_id) 
          ON DELETE CASCADE;');

        $this->addSql('
          ALTER TABLE public.tr_customer_cus
          DROP CONSTRAINT fk_784fec5fdb6a43b2, 
          ADD CONSTRAINT fk_784fec5fdb6a43b2 
          FOREIGN KEY (cus_navitia_entity) 
          REFERENCES public.t_navitia_entity_nav (nav_id) 
          ON DELETE CASCADE;');

        $this->addSql('
          ALTER TABLE public.tj_user_role_ur
          DROP CONSTRAINT fk_cc9b191b4bab96c, 
          ADD CONSTRAINT fk_cc9b191b4bab96c 
          FOREIGN KEY (rol_id) 
          REFERENCES public.t_role_rol (rol_id) 
          ON DELETE CASCADE;');
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

        $this->addSql('
          ALTER TABLE public.tj_customer_application_cap
          DROP CONSTRAINT fk_9425a57f19eb6921, 
          ADD CONSTRAINT fk_9425a57f19eb6921 
          FOREIGN KEY (customer_id) 
          REFERENCES public.tr_customer_cus (cus_id);');

        $this->addSql('
          ALTER TABLE public.tj_customer_application_cap
          DROP CONSTRAINT fk_9425a57f3e030acd, 
          ADD CONSTRAINT fk_9425a57f3e030acd 
          FOREIGN KEY (application_id) 
          REFERENCES public.tr_application_app (app_id);');

        $this->addSql('
          ALTER TABLE public.t_navitia_token_nat
          DROP CONSTRAINT fk_356e8dadf03a7216, 
          ADD CONSTRAINT fk_356e8dadf03a7216 
          FOREIGN KEY (nav_id) 
          REFERENCES public.t_navitia_entity_nav (nav_id);');

        $this->addSql('
          ALTER TABLE public.t_role_rol
          DROP CONSTRAINT fk_6ed6a71f7987212d, 
          ADD CONSTRAINT fk_6ed6a71f7987212d 
          FOREIGN KEY (app_id) 
          REFERENCES public.tr_application_app (app_id);');

        $this->addSql('
          ALTER TABLE public.tr_customer_cus
          DROP CONSTRAINT fk_784fec5fdb6a43b2, 
          ADD CONSTRAINT fk_784fec5fdb6a43b2 
          FOREIGN KEY (cus_navitia_entity) 
          REFERENCES public.t_navitia_entity_nav (nav_id);');

        $this->addSql('
          ALTER TABLE public.tj_user_role_ur
          DROP CONSTRAINT fk_cc9b191b4bab96c, 
          ADD CONSTRAINT fk_cc9b191b4bab96c 
          FOREIGN KEY (rol_id) 
          REFERENCES public.t_role_rol (rol_id);');
    }
}
