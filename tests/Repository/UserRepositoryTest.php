<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @covers \App\Repository\UserRepository
 */
class UserRepositoryTest extends KernelTestCase
{
    private ?EntityManagerInterface $entityManager = null;
    private UserRepository $userRepository;

    public function testAdd(): void
    {
        $user = new User();
        $user->setFirstName('Lorem');
        $user->setLastName('Ipsum');

        $this->userRepository->add($user);
        $userFound = $this->userRepository->findOneBy(['firstName' => 'Lorem', 'lastName' => 'Ipsum']);
        $usersCount = $this->userRepository->count([]);

        self::assertSame('Lorem', $userFound->getFirstName());
        self::assertSame('Ipsum', $userFound->getLastName());
        self::assertSame(1, $usersCount);
    }

    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();

        /** @var UserRepository $userRepository */
        $userRepository = $this->getEntityManager()->getRepository(User::class);

        $this->userRepository = $userRepository;
        $this->dropDatabaseSchema();
        $this->updateDatabaseSchema();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager === null) {
            $this->entityManager = static::$kernel
                ->getContainer()
                ->get('doctrine')
                ->getManager()
            ;
        }

        return $this->entityManager;
    }

    private function dropDatabaseSchema(): void
    {
        $allMetadata = $this->getAllEntitiesMeta();

        $this
            ->getSchemaTool()
            ->dropSchema($allMetadata)
        ;
    }

    private function getAllEntitiesMeta(): array
    {
        return $this
            ->getEntityManager()
            ->getMetadataFactory()
            ->getAllMetadata()
        ;
    }

    private function getSchemaTool(): SchemaTool
    {
        return new SchemaTool($this->getEntityManager());
    }

    private function updateDatabaseSchema(): void
    {
        $allMetadata = $this->getAllEntitiesMeta();

        $this
            ->getSchemaTool()
            ->updateSchema($allMetadata)
        ;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
