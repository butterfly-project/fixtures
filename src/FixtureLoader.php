<?php

namespace Butterfly\Component\Fixtures;

use Doctrine\DBAL\Connection;

class FixtureLoader
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $cachedData = array();

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param array $listDataOfTables
     * @throws \Doctrine\DBAL\DBALException
     */
    public function load(array $listDataOfTables)
    {
        $tables = array();
        foreach ($listDataOfTables as $dataOfTable) {
            $tables[] = $dataOfTable['table'];
        }

        $tables = array_reverse(array_unique($tables));

        foreach ($tables as $table) {
            $this->connection->executeQuery(sprintf('TRUNCATE TABLE %s CASCADE;', $table));
        }

        foreach ($listDataOfTables as $dataOfTable) {
            $tableName      = $dataOfTable['table'];
            $tableData      = $dataOfTable['data'];
            $tableDefaults  = isset($dataOfTable['defaults']) ? $dataOfTable['defaults'] : array();
            $defaultsKeys   = array_keys($tableDefaults);
            $tableRelations = isset($dataOfTable['relations']) ? $dataOfTable['relations'] : array();

            if (!array_key_exists($tableName, $this->cachedData)) {
                $this->cachedData[$tableName] = array();
            }

            foreach ($tableData as $record) {
                $fixtureItemId = null;

                if (array_key_exists('__id', $record)) {
                    $fixtureItemId = $record['__id'];
                    unset($record['__id']);
                }

                $needleKeys = array_diff($defaultsKeys, array_keys($record));

                foreach ($needleKeys as $key) {
                    $record[$key] = $tableDefaults[$key];
                }

                foreach ($tableRelations as $key => $table) {
                    if (empty($record[$key])) {
                        continue;
                    }

                    $refId = $record[$key];

                    if (!isset($this->cachedData[$table])) {
                        throw new \RuntimeException(sprintf('Table %s is not loaded', $table));
                    }

                    if (!isset($this->cachedData[$table][$refId])) {
                        throw new \RuntimeException(sprintf('Fixture %s in table %s is not found', $refId, $table));
                    }

                    $record[$key] = $this->cachedData[$table][$refId];
                }

                $types = array();
                foreach ($record as $key => $value) {
                    $types[$key] = $this->getValueType($value);
                }

                $this->connection->insert($tableName, $record, $types);

                if (!empty($fixtureItemId)) {
                    $this->cachedData[$tableName][$fixtureItemId] = (int)$this->connection->lastInsertId($tableName . '_id_seq');
                }
            }
        }
    }

    /**
     * @param mixed $value
     * @return int
     */
    protected function getValueType($value)
    {
        if (is_bool($value)) {
            return \PDO::PARAM_BOOL;
        } elseif (is_int($value)) {
            return \PDO::PARAM_INT;
        } else {
            return \PDO::PARAM_STR;
        }
    }
}
