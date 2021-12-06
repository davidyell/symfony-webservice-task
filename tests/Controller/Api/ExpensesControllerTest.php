<?php

namespace App\Tests\Controller\Api;

use App\Controller\Api\ExpensesController;
use App\Entity\Expenses;
use App\Entity\ExpenseTypes;
use App\Repository\ExpensesRepository;
use App\Repository\ExpenseTypesRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class ExpensesControllerTest extends KernelTestCase
{
    /**
     * Build an expense entity
     *
     * @param string $title Title
     * @param string $description Description
     * @param float $value Value
     * @param string $typeName Expense type name
     * @return \App\Entity\Expenses
     */
    private function buildExpense($title, $description, $value, $typeName): Expenses
    {
        $expense = new Expenses();
        $expense->setTitle($title);
        $expense->setDescription($description);
        $expense->setValue($value);

        $type = new ExpenseTypes();
        $type->setName($typeName);
        $expense->setType($type);

        return $expense;
    }

    /**
     * Mock the Doctrine components required to allow a fixed return to sidestep testing the database
     *
     * @param mixed $returnValue
     * @param $repositoryMethod
     * @return \Doctrine\Persistence\ManagerRegistry|mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private function buildDoctrineMock($returnValue, string $repositoryMethod)
    {
        $mockRepository = $this->getMockBuilder(ExpensesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->once())
            ->method($repositoryMethod)
            ->willReturn($returnValue);

        $mockManagerRegistry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockManagerRegistry->expects($this->once())
            ->method('getRepository')
            ->willReturn($mockRepository);

        return $mockManagerRegistry;
    }

    public function setUp(): void
    {
        parent::setUp();

        self::bootKernel();
    }


    public function testListingAllExpenses()
    {
        $expenses = [
            $this->buildExpense('First expense', 'Example expense item', 24.99, 'Bills'),
            $this->buildExpense('Second expense', 'Example expense item', 11.99, 'Other'),
        ];

        $mockRepository = $this->getMockBuilder(ExpensesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->once())
            ->method('findAll')
            ->willReturn($expenses);

        $controller = new ExpensesController();
        $controller->setContainer(static::getContainer());
        $result = $controller->index($mockRepository);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertNotNull($result->getContent());

        $data = \json_decode($result->getContent(), true);

        $this->assertArrayHasKey('title', $data[0]);
        $this->assertEquals('First expense', $data[0]['title']);

        $this->assertArrayHasKey('description', $data[0]);
        $this->assertEquals('Example expense item', $data[0]['description']);

        $this->assertArrayHasKey('value', $data[0]);
        $this->assertEquals(24.99, $data[0]['value']);

        $this->assertArrayHasKey('title', $data[1]);
        $this->assertEquals('Second expense', $data[1]['title']);

        $this->assertArrayHasKey('description', $data[1]);
        $this->assertEquals('Example expense item', $data[1]['description']);

        $this->assertArrayHasKey('value', $data[1]);
        $this->assertEquals(11.99, $data[1]['value']);
    }

    public function testReadingAnExpense()
    {
        $expense = $this->buildExpense('First expense', 'Example expense item', 24.99, 'Bills');

        $mockRepository = $this->getMockBuilder(ExpensesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->willReturn($expense);

        $controller = new ExpensesController();
        $controller->setContainer(static::getContainer());
        $result = $controller->read($mockRepository, 1);

        $this->assertEquals(200, $result->getStatusCode());
        $this->assertNotNull($result->getContent());

        $data = \json_decode($result->getContent(), true);
        $this->assertEquals('First expense', $data['title']);
        $this->assertEquals('Example expense item', $data['description']);
        $this->assertEquals(24.99, $data['value']);
    }

    public function testReadingANonExistentExpense()
    {
        $mockRepository = $this->getMockBuilder(ExpensesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $controller = new ExpensesController();
        $controller->setContainer(static::getContainer());
        $result = $controller->read($mockRepository, 99);

        $this->assertEquals(404, $result->getStatusCode());
        $this->assertNotNull($result->getContent());
        $this->assertStringContainsString('Expense cannot be found', $result->getContent());
    }

    public function testCreatingAnExpense()
    {
        $request = new Request();
        $request->initialize(
            [],
            [
                'type_id' => 3,
                'title' => 'Example expense',
                'description' => 'An example expense',
                'value' => '12.34'
            ]
        );

        $expenseType = new ExpenseTypes();
        $expenseType->setName('Transport');

        $mockRepository = $this->getMockBuilder(ExpenseTypesRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->once())
            ->method('findExpenseTypeById')
            ->willReturn($expenseType);

        $mockObjectManager = $this->getMockBuilder(ObjectManager::class)
            ->getMock();
        $mockObjectManager->expects($this->once())
            ->method('persist');
        $mockObjectManager->expects($this->once())
            ->method('flush');

        $mockManagerRegistry = $this->getMockBuilder(ManagerRegistry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockManagerRegistry->expects($this->once())
            ->method('getRepository')
            ->willReturn($mockRepository);
        $mockManagerRegistry->expects($this->once())
            ->method('getManager')
            ->willReturn($mockObjectManager);

        $controller = new ExpensesController();
        $controller->setContainer(static::getContainer());
        $result = $controller->create($mockManagerRegistry, $request);

        $this->assertEquals(201, $result->getStatusCode());
    }

    public function testCreatingAnExpenseWithPartialData()
    {
        $this->markTestIncomplete();
    }

    public function testCreatingAnExpenseWithInvalidData()
    {
        $this->markTestIncomplete();
    }

    public function testUpdatingAnExpense()
    {
        $this->markTestIncomplete();
    }

    public function testUpdatingAnExpenseWhichDoesNotExist()
    {
        $this->markTestIncomplete();
    }

    public function testUpdatingOnlyTheExpenseType()
    {
        $this->markTestIncomplete();
    }

    public function testUpdatingAnExpenseWithAnInvalidType()
    {
        $this->markTestIncomplete();
    }

    public function testDeletingAnExpense()
    {
        $this->markTestIncomplete();
    }

    public function testDeletingAnExpenseWhichDoesNotExist()
    {
        $this->markTestIncomplete();
    }
}
