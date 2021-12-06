<?php

namespace App\Tests\Repository;

use App\Entity\ExpenseTypes;
use App\Repository\ExpenseTypesRepository;
use PHPUnit\Framework\TestCase;

class ExpenseTypesRepositoryTest extends TestCase
{
    public function testFindingAnExpenseTypeById()
    {
        $expenseType = new ExpenseTypes();
        $expenseType->setName('Bills');

        $repository = $this->getMockBuilder(ExpenseTypesRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $repository->expects($this->once())
            ->method('find')
            ->willReturn($expenseType);

        $result = $repository->findExpenseTypeById(1);

        $this->assertInstanceOf(ExpenseTypes::class, $result);
    }

    public function testFindingANonExistentExpenseTypeById()
    {
        $repository = $this->getMockBuilder(ExpenseTypesRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['find'])
            ->getMock();
        $repository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $result = $repository->findExpenseTypeById(99);

        $this->assertNull($result);
    }

}
