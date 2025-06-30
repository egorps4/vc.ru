<?php

namespace App\Repository;

use App\Entity\UserView;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;

class UserViewRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function findByUserAndPost(int $userId, int $postId): ?array
    {
        $sql = 'SELECT id FROM user_view
                WHERE user_id = :userId
                AND post_id = :postId';
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('postId', $postId, \PDO::PARAM_INT);

        return $stmt->executeQuery()->fetchAssociative() ?: null;
    }
}