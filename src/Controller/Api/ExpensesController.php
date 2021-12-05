<?php
/**
 * ExpensesController
 *
 * @author David Yell <neon1024@gmail.com>
 */
declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Expenses;
use App\Entity\ExpenseTypes;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExpensesController extends AbstractController
{
    /**
     * List expenses
     *
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine Doctrine instance
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses", methods={"GET"})
     */
    public function index(ManagerRegistry $doctrine): Response
    {
        $expenses = $doctrine
            ->getRepository(Expenses::class)
            ->findAll();

        return $this->json($expenses);
    }

    /**
     * Read a specific expense
     *
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine Doctrine instance
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses/{id}", methods={"GET"})
     */
    public function read(ManagerRegistry $doctrine, int $id): Response
    {
        $expense = $doctrine
            ->getRepository(Expenses::class)
            ->find($id);

        if ($expense instanceof Expenses === false) {
            return $this->json(['error' => 'Expense cannot be found'], 404);
        }

        return $this->json($expense);
    }

    /**
     * Create a new expense
     *
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine Doctrine instance
     * @param \Symfony\Component\HttpFoundation\Request $request Parsed request object
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses/new", methods={"POST"})
     */
    public function create(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();

        $expense = new Expenses();
        $expense->setTitle($request->request->get('title'));
        $expense->setDescription($request->request->get('description'));
        $expense->setValue($request->request->get('value'));

        $expenseTypeId = $request->request->get('type_id');
        $expenseType = $doctrine->getRepository(ExpenseTypes::class)->find($expenseTypeId);

        if ($expenseType instanceof ExpenseTypes === false) {
            return $this->json(['error' => 'Invalid expense type id'], 400);
        }

        $expense->setType($expenseType);

        $entityManager->persist($expense);
        $entityManager->flush();

        return $this->json(['created' => $expense->getId()], 201);
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
