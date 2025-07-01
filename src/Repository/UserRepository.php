<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;

class UserRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT * FROM "user" WHERE id = :id';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);

        return $stmt->executeQuery()->fetchAssociative() ?: null;
    }
}
