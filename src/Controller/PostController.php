<?php

namespace App\Controller;

use App\Service\PostService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends BaseController
{
    private PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    #[Route('/api/posts', methods: ['GET'])]
    public function getPosts(Request $request): JsonResponse
    {
        try {
            $userId = $request->query->get('userId');
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 50);
            $sortOrder = $request->query->get('sortOrder', 'DESC') === 'ASC' ? 'ASC' : 'DESC';

            $result = $this->postService->getPosts($userId, $page, $limit, $sortOrder);

            return $this->responseSuccess([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Exception $exc) {
            if ($exc instanceof HttpException) {
                return $this->responseError([
                    'status' => 'error',
                    'message' => $exc->getMessage(),
                ]);
            } 
            return $this->responseError([
                'status' => 'error',
                'message' => 'Ошибка сервера'
            ]);
        }
    }
}
