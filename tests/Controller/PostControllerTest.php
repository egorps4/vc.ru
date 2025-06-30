<?php

namespace App\Tests\Controller;

use App\Controller\PostController;
use App\Service\PostService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostControllerTest extends TestCase
{
    private PostController $controller;
    private MockObject $postService;

    protected function setUp(): void
    {
        $this->postService = $this->createMock(PostService::class);

        // Используем анонимный класс для переопределения responseSuccess и responseError
        $this->controller = new class($this->postService) extends PostController {
            public function responseSuccess(array $data, int $status = 200): JsonResponse
            {
                return new JsonResponse($data, $status);
            }

            public function responseError(array $data, int $status = 500): JsonResponse
            {
                return new JsonResponse($data, $status);
            }
        };
    }

    public function testGetPostsSuccess(): void
    {
        $request = new Request(['userId' => 1, 'page' => 1, 'limit' => 10, 'sortOrder' => 'ASC']);
        $postsData = [
            'posts' => [
                [
                    'id' => 1,
                    'title' => 'Test Post',
                    'content' => 'Content',
                    'hotness' => 100,
                    'viewCount' => 50,
                    'authorId' => 2,
                    'authorName' => 'Author',
                    'createdAt' => '2025-06-30',
                    'aboba' => 231
                ],
            ],
            'currentPage' => 1,
            'totalPages' => 1,
            'totalItems' => 1,
        ];

        $this->postService
            ->expects($this->once())
            ->method('getPosts')
            ->with(1, 1, 10, 'ASC')
            ->willReturn($postsData);

        $response = $this->controller->getPosts($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $expectedResponse = [
            'status' => 'success',
            'data' => $postsData,
        ];
        $this->assertJsonStringEqualsJsonString(json_encode($expectedResponse), $response->getContent());
    }

    public function testGetPostsInvalidUserId(): void
    {
        $request = new Request(['userId' => null]);

        $this->postService
            ->expects($this->once())
            ->method('getPosts')
            ->willThrowException(new BadRequestHttpException('Invalid or missing userId'));

        $response = $this->controller->getPosts($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'Invalid or missing userId',
        ]), $response->getContent());
    }

    public function testGetPostsUserNotFound(): void
    {
        $request = new Request(['userId' => 999]);

        $this->postService
            ->expects($this->once())
            ->method('getPosts')
            ->willThrowException(new NotFoundHttpException('User not found'));

        $response = $this->controller->getPosts($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'User not found',
        ]), $response->getContent());
    }

    public function testGetPostsServerError(): void
    {
        $request = new Request(['userId' => 1]);

        $this->postService
            ->expects($this->once())
            ->method('getPosts')
            ->willThrowException(new \Exception('Server error'));

        $response = $this->controller->getPosts($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'Server error',
        ]), $response->getContent());
    }

    public function testReadPostSuccess(): void
    {
        $postId = 1;
        $userId = 1;
        $request = new Request([], [], [], [], [], [], json_encode(['userId' => $userId]));
        $postData = [
            'id' => 1,
            'title' => 'Test Post',
            'content' => 'Content',
            'hotness' => 100,
            'view_count' => 51,
            'author_id' => 2,
        ];

        $this->postService
            ->expects($this->once())
            ->method('readPost')
            ->with($userId, $postId)
            ->willReturn($postData);

        $response = $this->controller->readPost($request, $postId);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'success',
            'data' => $postData,
        ]), $response->getContent());
    }

    public function testReadPostInvalidUserId(): void
    {
        $postId = 1;
        $request = new Request([], [], [], [], [], [], json_encode(['userId' => null]));

        $this->postService
            ->expects($this->once())
            ->method('readPost')
            ->willThrowException(new BadRequestHttpException('Invalid or missing userId'));

        $response = $this->controller->readPost($request, $postId);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'Invalid or missing userId',
        ]), $response->getContent());
    }

    public function testReadPostInvalidPostId(): void
    {
        $postId = -1;
        $request = new Request([], [], [], [], [], [], json_encode(['userId' => 1]));

        $this->postService
            ->expects($this->once())
            ->method('readPost')
            ->willThrowException(new BadRequestHttpException('Invalid or missing postId'));

        $response = $this->controller->readPost($request, $postId);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'Invalid or missing postId',
        ]), $response->getContent());
    }

    public function testReadPostsUserNotFound(): void
    {
        $postId = 1;
        $request = new Request([], [], [], [], [], [], json_encode(['userId' => 999]));

        $this->postService
            ->expects($this->once())
            ->method('readPost')
            ->willThrowException(new NotFoundHttpException('User not found'));

        $response = $this->controller->readPost($request, $postId);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'User not found',
        ]), $response->getContent());
    }

    public function testReadPostsPostNotFound(): void
    {
        $postId = 999;
        $request = new Request([], [], [], [], [], [], json_encode(['userId' => 1]));

        $this->postService
            ->expects($this->once())
            ->method('readPost')
            ->willThrowException(new NotFoundHttpException('Post not found'));

        $response = $this->controller->readPost($request, $postId);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'Post not found',
        ]), $response->getContent());
    }

    public function testReadPostAlreadyRead(): void
    {
        $postId = 1;
        $userId = 1;
        $request = new Request([], [], [], [], [], [], json_encode(['userId' => $userId]));

        $this->postService
            ->expects($this->once())
            ->method('readPost')
            ->willThrowException(new HttpException(409, 'User has already read the post'));

        $response = $this->controller->readPost($request, $postId);

        $this->assertEquals(409, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'User has already read the post',
        ]), $response->getContent());
    }

    public function testReadPostServerError(): void
    {
        $postId = 1;
        $userId = 1;
        $request = new Request([], [], [], [], [], [], json_encode(['userId' => $userId]));

        $this->postService
            ->expects($this->once())
            ->method('readPost')
            ->willThrowException(new \Exception('Unexpected'));

        $response = $this->controller->readPost($request, $postId);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => 'error',
            'message' => 'Server error',
        ]), $response->getContent());
    }
}
