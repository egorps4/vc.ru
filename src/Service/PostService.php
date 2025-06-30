<?php

namespace App\Service;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use App\Repository\UserViewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Runtime\Symfony\Component\HttpKernel\HttpKernelInterfaceRuntime;

class PostService
{
    private UserRepository $userRepository;
    private PostRepository $postRepository;
    private UserViewRepository $userViewRepository;
    private EntityManagerInterface $em;

    public function __construct(
        UserRepository $userRepository,
        PostRepository $postRepository,
        UserViewRepository $userViewRepository,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
        $this->userViewRepository = $userViewRepository;
        $this->em = $em;
    }

    public function getPosts(int $userId, int $page, int $limit, string $sortOrder = 'DESC'): array
    {
        $user = $this->userRepository->getById($userId);

        if (empty($user)) {
            throw new NotFoundHttpException('User not found');
        }

        $result = $this->postRepository->getPosts($userId, $page, $limit, $sortOrder);

        $posts = array_map(fn(array $post) => [
            'id' => $post['id'],
            'title' => $post['title'],
            'content' => $post['content'],
            'hotness' => $post['hotness'],
            'viewCount' => $post['view_count'],
            'authorId' => $post['author_id'],
            'authorName' => $post['author_name'],
            'createdAt' => date("Y-m-d", strtotime($post['created_at'])),
        ], $result['posts']);

        return [
            'posts' => $posts,
            'currentPage' => $result['currentPage'],
            'totalPages' => $result['totalPages'],
            'totalItems' => $result['totalItems'],
        ];
    }

    public function readPost(int $userId, int $postId): array
    {
        $user = $this->userRepository->getById($userId);

        if (empty($user)) {
            throw new NotFoundHttpException('User not found');
        }

        $post = $this->postRepository->getById($postId);

        if (empty($post)) {
            throw new NotFoundHttpException('Post not found');
        }

        $userView = $this->userViewRepository->findByUserAndPost($userId, $postId);

        if (!empty($userView)) {
            throw new BadRequestHttpException('User has already read the post');
        }

        $connection = $this->em->getConnection();
        $connection->beginTransaction();

        try {
            $createUserViewSql = 'INSERT INTO user_view (user_id, post_id) VALUES (:userId, :postId)';
            $connection->executeQuery($createUserViewSql, [
                'userId' => $userId,
                'postId' => $postId,
            ]);
            
            $incrementViewCountSql = 'UPDATE post SET view_count = view_count + 1 WHERE id = :postId';
            $connection->executeQuery($incrementViewCountSql, [
                'postId' => $postId,
            ]);

            $connection->commit();
        } catch (\Exception $exc) {
            $connection->rollBack();
            throw new HttpException(500, 'Error viewing post');
        }

        return $this->postRepository->getById($postId);
    }
}
