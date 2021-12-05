<?php
/**
 * ExpensesController
 *
 * @author David Yell <neon1024@gmail.com>
 */
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Expenses;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ExpensesController
{
    /**
     * @Route("/api/expenses", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $expenses = $doctrine
            ->getRepository(Expenses::class)
            ->findAll();

        return new JsonResponse($expenses);
    }

    /**
     * @Route("/api/create", methods={"POST"})
     */
    public function create(): Response
    {
        return new Response(null, 201);
    }

    /**
     * @Route("/api/expenses/{id}", methods={"GET"})
     */
    public function read(ManagerRegistry $doctrine, int $id): Response
    {
        $expense = $doctrine
            ->getRepository(Expenses::class)
            ->find($id);

        if ($expense instanceof Expenses === false) {
            return new JsonResponse(['error' => 'Expense cannot be found'], 404);
        }

        return new JsonResponse($expense);
    }

    /**
     * @Route("/api/update", methods={"PUT", "PATCH"})
     */
    public function update(): Response
    {
        return new Response(null, 200);
    }

    /**
     * @Route("/api/delete", methods={"DELETE"})
     */
    public function delete(): Response
    {
        return new Response(null, 200);
    }
}
