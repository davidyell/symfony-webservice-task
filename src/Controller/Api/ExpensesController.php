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
use App\Repository\ExpensesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @OA\Info(
 *     title="Expenses endpoint",
 *     version="0.1",
 *     @OA\Contact(
 *       email="neon1024@gmail.com"
 *     )
 * )
 */
class ExpensesController extends AbstractController
{
    /**
     * List expenses
     *
     * @param \App\Repository\ExpensesRepository $repository Doctrine repository instance
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses", methods={"GET"})
     * @OA\Get(
     *     path="/api/expenses",
     *     @OA\Response(response="200", description="List all expenses")
     * )
     */
    public function index(ExpensesRepository $repository): Response
    {
        $expenses = $repository->findAll();

        return $this->json($expenses);
    }

    /**
     * Read a specific expense
     *
     * @param \App\Repository\ExpensesRepository $repository Doctrine repository instance
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses/{id}", methods={"GET"})\
     * @OA\Get(
     *     path="/api/expenses/{id}",
     *     @OA\Response(response="200", description="View a single expense"),
     *     @OA\Response(response="404", description="Expense cannot be found")
     * )
     */
    public function read(ExpensesRepository $repository, int $id): Response
    {
        $expense = $repository->find($id);

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
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator Validator instance
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses", methods={"POST"})
     * @OA\Post(
     *     path="/api/expenses",
     *     summary="Create new expenses",
     *     @OA\Response(response="201", description="List all expenses"),
     *     @OA\Response(response="400", description="Invalid expense type id"),
     * )
     */
    public function create(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): Response
    {
        $entityManager = $doctrine->getManager();

        $expense = new Expenses();
        $expense->setTitle($request->request->get('title', ''));
        $expense->setDescription($request->request->get('description'));
        $expense->setValue($request->request->get('value', ''));

        $expenseType = $doctrine
            ->getRepository(ExpenseTypes::class)
            ->findExpenseTypeById((int)$request->request->get('type_id'));

        if ($expenseType instanceof ExpenseTypes === false) {
            return $this->json(['error' => 'Invalid expense type id'], 400);
        }

        $expense->setType($expenseType);

        $errors = $validator->validate($expense);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $violation) {
                /* @var \Symfony\Component\Validator\ConstraintViolation $violation */
                $messages[] = $violation->getMessage();
            }

            return $this->json([
                'error' => 'Expense could not be created',
                'messages' => $messages
            ], 422);
        }

        $entityManager->persist($expense);
        $entityManager->flush();

        // TODO: How to manage a failure to persist an entity?
        return $this->json(['created' => $expense->getId()], 201);
    }

    /**
     * Update an existing expense
     *
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine Doctrine instance
     * @param \Symfony\Component\HttpFoundation\Request $request Parsed request object
     * @param int $id Expense id
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses/{id}", methods={"PUT", "PATCH"})
     * @OA\Put(
     *     path="/api/expenses/{id}",
     *     summary="Update an existing expense",
     *     @OA\Response(response="200", description="Update an expense"),
     *     @OA\Response(response="404", description="Expense cannot be found"),
     *     @OA\Response(response="400", description="Invalid expense type id")
     * )
     * @OA\Patch(
     *     path="/api/expenses/{id}",
     *     summary="Update an existing expense",
     *     @OA\Response(response="200", description="Update an expense"),
     *     @OA\Response(response="404", description="Expense cannot be found"),
     *     @OA\Response(response="400", description="Invalid expense type id")
     * )
     */
    public function update(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $expense = $doctrine
            ->getRepository(Expenses::class)
            ->find($id);

        if ($expense instanceof Expenses === false) {
            return $this->json(['error' => 'Expense cannot be found'], 404);
        }

        $expense->setTitle($request->request->get('title', $expense->getTitle()));
        $expense->setDescription($request->request->get('description', $expense->getDescription()));
        $expense->setValue($request->request->get('value', $expense->getValue()));

        if ($request->request->get('type_id') !== null) {
            $expenseType = $doctrine
                ->getRepository(ExpenseTypes::class)
                ->findExpenseTypeById((int)$request->request->get('type_id'));

            if ($expenseType instanceof ExpenseTypes === false) {
                return $this->json(['error' => 'Invalid expense type id'], 400);
            }

            $expense->setType($expenseType);
        }

        $doctrine->getManager()->flush();

        return $this->json(['updated' => 'Expense has been updated']);
    }

    /**
     * Delete an expense
     *
     * @param \Doctrine\Persistence\ManagerRegistry $doctrine Doctrine instance
     * @param int $id Expense id
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/api/expenses/{id}", methods={"DELETE"})
     * @OA\Delete (
     *     path="/api/expenses/{id}",
     *     summary="Delete an existing expense",
     *     @OA\Response(response="200", description="Update an expense"),
     *     @OA\Response(response="404", description="Expense cannot be found")
     * )
     */
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $expense = $doctrine
            ->getRepository(Expenses::class)
            ->find($id);

        if ($expense instanceof Expenses === false) {
            return $this->json(['error' => 'Expense cannot be found'], 404);
        }

        $manager = $doctrine->getManager();
        $manager->remove($expense);
        $manager->flush();

        return $this->json(['success' => 'Expense has been deleted']);
    }
}
