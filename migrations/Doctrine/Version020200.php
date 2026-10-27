<?php

declare(strict_types=1);

namespace Netgen\Layouts\RemoteMedia\Migrations\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

use function is_a;

final class Version020200 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // DBAL 3 has AbstractMySQLPlatform (MySQL and MariaDB), DBAL 2 has MySqlPlatform.
        $platform = $this->connection->getDatabasePlatform();

        $this->abortIf(
            !is_a($platform, 'Doctrine\DBAL\Platforms\AbstractMySQLPlatform')
            && !is_a($platform, 'Doctrine\DBAL\Platforms\MySqlPlatform'),
            'Migration can only be executed safely on MySQL.',
        );

        // nglayouts_remote_media_item table

        $this->addSql(
            <<<'EOT'
            CREATE TABLE nglayouts_remote_media_item (
              id int NOT NULL AUTO_INCREMENT,
              remote_resource_location_id int DEFAULT NULL,
              value varchar(255) NOT NULL,
              created_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              updated_at datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
              PRIMARY KEY (id),
              UNIQUE KEY UNIQ_7BAC206E1D775834 (value),
              UNIQUE KEY UNIQ_7BAC206EB29166B0 (remote_resource_location_id),
              CONSTRAINT FK_7BAC206EB29166B0 FOREIGN KEY (remote_resource_location_id)
                REFERENCES ngrm_remote_resource_location (id) ON DELETE CASCADE
            ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE utf8mb4_unicode_ci
            EOT
        );
    }
}
