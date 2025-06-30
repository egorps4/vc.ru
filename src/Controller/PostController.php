<?php

namespace App\Controller;

use App\Service\PostService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/post')]
class PostController extends BaseController
{
    private PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    #[Route('', methods: ['GET'])]
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
            /*В реальном проекте для обработки ошибок можно было настроить мидлвар для глобальной обработки ошибок.
            Без необходимости писать trycatch в каждом контроллере*/
            if ($exc instanceof HttpException) {
                return $this->responseError([
                    'status' => 'error',
                    'message' => $exc->getMessage(),
                ], $exc->getStatusCode());
            }
            return $this->responseError([
                'status' => 'error',
                'message' => 'Ошибка сервера'
            ]);
        }
    }

    #[Route('/{postId}/read', methods: ['POST'])]
    public function readPost(Request $request, int $postId): JsonResponse
    {
        try {
            //В реальном проекте userId извлекался бы из токена авторизации
            $userId = $request->request->get('userId');

            $result = $this->postService->readPost($userId, $postId);

            return $this->responseSuccess([
                'status' => 'success',
                'data' => $result,
            ]);
        } catch (\Exception $exc) {
            /*В реальном проекте для обработки ошибок можно было настроить мидлвар для глобальной обработки ошибок.
            Без необходимости писать trycatch в каждом контроллере*/
            if ($exc instanceof HttpException) {
                return $this->responseError([
                    'status' => 'error',
                    'message' => $exc->getMessage(),
                ], $exc->getStatusCode());
            }
            return $this->responseError([
                'status' => 'error',
                'message' => 'Ошибка сервера'
            ]);
        }
    }
}
