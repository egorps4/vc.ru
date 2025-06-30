<?php

namespace App\Service;

use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostService
{
    private UserRepository $userRepository;
    private PostRepository $postRepository;

    public function __construct(UserRepository $userRepository, PostRepository $postRepository)
    {
        $this->userRepository = $userRepository;
        $this->postRepository = $postRepository;
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
}
