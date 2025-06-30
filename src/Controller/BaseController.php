<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController extends AbstractController
{
    public function responseSuccess(array $data, int $status = 200): JsonResponse
    {
        return $this->json($data, $status, [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
    }

    public function responseError(array $data, int $status = 500): JsonResponse
    {
        return $this->json($data, $status, [
            'Content-Type' => 'application/json; charset=utf-8'
        ]);
    }
}
