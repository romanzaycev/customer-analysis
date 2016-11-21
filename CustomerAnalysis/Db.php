<?php

namespace CustomerAnalysis;

require_once __DIR__ . '/Traits/Singleton.php';

use CustomerAnalysis\Traits\Singleton;

/**
 * Class Db
 * @package CustomerAnalysis
 */
class Db
{
    /**
     * Simple config section
     */
    private $host = '127.0.0.1';
    private $dbName = 'rmanalysis';
    private $user = 'root';
    private $password = '';

    use Singleton;

    /**
     * @var \PDO
     */
    private $connection;

    /**
     * @var bool
     */
    private $isRfmFieldExists = false;

    /**
     * Db initializer.
     */
    protected function init()
    {
        $handler = new \PDO(sprintf('mysql:dbname=%s;host=%s', $this->dbName, $this->host), $this->user, $this->password);
        $handler->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->connection = $handler;
    }

    /**
     * Get DB connection.
     *
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get orders by status identifier.
     *
     * @param int $statusId
     * @return array
     */
    public function getOrders($statusId)
    {
        $statusId = (int)$statusId;

        $sql = "
            SELECT
                O.customerID,
                SUM(O.order_amount) AS totalAmount,
                COUNT(*) AS totalOrders
            FROM
                SC_orders AS O
            JOIN SC_customers AS C ON C.customerID = O.customerID
            WHERE
                O.statusID = :statusId
                AND C.Email IS NOT NULL
            GROUP BY O.customerID
        ";

        $statement = $this->connection->prepare($sql);
        $statement->execute(['statusId' => $statusId]);

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param int $customerId
     * @param array $data
     */
    public function saveAnalysisData($customerId, $data)
    {
        $customerId = (int)$customerId;

        if ($this->checkRmfField()) {
            $sql = "
                UPDATE SC_customers SET rfm = :rfm WHERE customerID = :id
            ";
            $statement = $this->connection->prepare($sql);
            $statement->execute(
                [
                    'rfm' => implode(
                        ':',
                        array_map(function ($k, $v) {
                            return sprintf('%s%d', $k, $v);
                        }, array_keys($data), array_values($data))
                    ),
                    'id' => $customerId
                ]
            );
        }
    }

    /**
     * @return bool
     */
    protected function checkRmfField()
    {
        if (!$this->isRfmFieldExists) {
            $sql = "
                SELECT
                    TABLE_SCHEMA
                FROM
                    information_schema.`COLUMNS`
                WHERE
                    TABLE_SCHEMA = :dbName
                    AND TABLE_NAME = 'SC_customers'
                    AND COLUMN_NAME = 'rfm';
            ";

            $statement = $this->connection->prepare($sql);
            $statement->execute(['dbName' => $this->dbName]);

            if ($statement->rowCount() > 0) {
                $this->isRfmFieldExists = true;

                return true;
            } else {
                $createColumnSql = "
                    ALTER TABLE
                      SC_customers 
                    ADD
                      rfm VARCHAR(32) NULL DEFAULT NULL;
                ";
                $this->connection->query($createColumnSql);
                $this->isRfmFieldExists = true;

                return true;
            }
        }

        return true;
    }

}

// EOF Db.php