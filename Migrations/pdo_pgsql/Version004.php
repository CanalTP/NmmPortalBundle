<?php

namespace CanalTP\NmmPortalBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\AbstractMigration;

class Version004 extends AbstractMigration
{
    const VERSION = '0.0.4';

    public function getName()
    {
        return self::VERSION;
    }

    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE t_language (lang_id INT NOT NULL, lang_code VARCHAR(2) NOT NULL, PRIMARY KEY(lang_id));');
        $this->addSql("INSERT INTO t_language (lang_id, lang_code) VALUES (1, 'fr'), (2, 'en');");
        $this->addSql('ALTER TABLE tr_customer_cus ADD COLUMN cus_lang INT NOT NULL DEFAULT 1;');
        $this->addSql('ALTER TABLE tr_customer_cus ADD FOREIGN KEY(cus_lang) REFERENCES t_language(lang_id) ON DELETE NO ACTION;');
        $this->addSql('ALTER TABLE t_user_usr ADD COLUMN usr_lang INT DEFAULT NULL;');
        $this->addSql('ALTER TABLE t_user_usr ADD FOREIGN KEY(usr_lang) REFERENCES t_language(lang_id) ON DELETE SET NULL;');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE t_user_usr DROP COLUMN usr_lang;');
        $this->addSql('ALTER TABLE tr_customer_cus DROP COLUMN cus_lang;');
        $this->addSql('DROP TABLE t_language;');
    }
}
