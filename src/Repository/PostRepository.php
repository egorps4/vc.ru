<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Predis\Client;

class PostRepository extends ServiceEntityRepository
{
    private Connection $connection;
    private Client $redis;

    public function __construct(Connection $connection, Client $redis)
    {
        $this->connection = $connection;
        $this->redis = $redis;
    }

    public function getById(int $id): ?array
    {
        $sql = 'SELECT * FROM post WHERE id = :id';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('id', $id, \PDO::PARAM_INT);

        return $stmt->executeQuery()->fetchAssociative() ?: null;
    }

    public function getPosts(int $userId, int $page = 1, int $limit = 50, string $sortOrder = 'DESC'): array
    {
        $cacheKey = sprintf('posts:user:%d:page:%d:limit:%d:order:%d', $userId, $page, $limit, $sortOrder);
        $cached = $this->redis->get($cacheKey);

        if ($cached) {
            return json_decode($cached, true);
        }

        $offset = $limit * ($page - 1);

        $sql = 'SELECT p.*, u.name as author_name
                FROM post p
                LEFT JOIN user_view uw ON p.id = uw.post_id AND uw.user_id = :userId
                LEFT JOIN "user" u ON u.id = p.author_id 
                WHERE uw.user_id IS NULL AND p.view_count <= :maxViews
                ORDER BY p.hotness ' . $sortOrder .
            ' LIMIT :limit OFFSET :offset';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('maxViews', 1000, \PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $posts = $stmt->executeQuery()->fetchAllAssociative();

        $totalItems = $this->getUserPostsTotalCount($userId);

        $result = [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => (int)ceil($totalItems / $limit),
            'totalItems' => $totalItems,
        ];

        //Для удобства проверки время жизни кеша занижено
        $this->redis->setex($cacheKey, 10, json_encode($result));

        return [
            'posts' => $posts,
            'currentPage' => $page,
            'totalPages' => (int)ceil($totalItems / $limit),
            'totalItems' => $totalItems,
        ];
    }

    private function getUserPostsTotalCount(?int $userId): int
    {
        $sql = 'SELECT COUNT(p.*) as total
                FROM post p
                LEFT JOIN user_view uw ON p.id = uw.user_id AND uw.user_id = :userId
                WHERE uw.user_id IS NULL AND p.view_count <= :maxViews';

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userId', $userId, \PDO::PARAM_INT);
        $stmt->bindValue('maxViews', 1000, \PDO::PARAM_INT);

        return (int)$stmt->executeQuery()->fetchOne();
    }
}
