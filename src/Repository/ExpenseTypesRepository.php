<?php

namespace App\Repository;

use App\Entity\ExpenseTypes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpenseTypes|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseTypes|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseTypes[]    findAll()
 * @method ExpenseTypes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseTypesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseTypes::class);
    }

    /**
     * Find an existing expense type
     *
     * @param int $id The expense type id
     * @return \App\Entity\ExpenseTypes|null
     */
    public function findExpenseTypeById(int $id): ?ExpenseTypes
    {
        $expenseType = $this->find($id);

        if ($expenseType instanceof ExpenseTypes === false) {
            return null;
        }

        return $expenseType;
    }
}
