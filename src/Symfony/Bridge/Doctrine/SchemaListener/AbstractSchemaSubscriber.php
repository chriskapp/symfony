<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bridge\Doctrine\SchemaListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

abstract class AbstractSchemaSubscriber implements EventSubscriber
{
    abstract public function postGenerateSchema(GenerateSchemaEventArgs $event): void;

    public function getSubscribedEvents(): array
    {
        if (!class_exists(ToolEvents::class)) {
            return [];
        }

        return [
            ToolEvents::postGenerateSchema,
        ];
    }

    protected function getIsSameDatabaseChecker(Connection $connection): \Closure
    {
        return static function (\Closure $exec) use ($connection): bool {
            $checkTable = 'schema_subscriber_check_'.bin2hex(random_bytes(7));
            $connection->executeStatement(sprintf('CREATE TABLE %s (id INTEGER NOT NULL)', $checkTable));

            try {
                $exec(sprintf('DROP TABLE %s', $checkTable));
            } catch (\Exception) {
                // ignore
            }

            try {
                $connection->executeStatement(sprintf('DROP TABLE %s', $checkTable));

                return false;
            } catch (TableNotFoundException) {
                return true;
            }
        };
    }
}
