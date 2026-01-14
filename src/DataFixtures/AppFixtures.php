<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // gestion de l'admin
        $adminUser = new User();
        $adminUser->setFirstName('Admin')
                ->setLastName('Admin')
            ->setEmail('admin@myepse.be')
            ->setPassword($this->passwordHasher->hashPassword($adminUser, 'password'))
            ->setRoles(['ROLE_ADMIN']);
        $manager->persist($adminUser);

        // gestion des utilisateurs
        for($u=0; $u<10; $u++)
        {
            $chrono = 1;
            $user = new User();
            $user->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setEmail($faker->email)
                ->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);

            //gestion des clients
            for($c=0; $c<rand(5,20); $c++)
            {
                $customer = new Customer();
                $customer->setFirstName($faker->firstName)
                        ->setLastName($faker->lastName)
                    ->setEmail($faker->email)
                    ->setUser($user);
                $manager->persist($customer);

                //gestion des factures
                for($i=0; $i<rand(1,5); $i++)
                {
                    $invoice = new Invoice();
                    $invoice->setAmount($faker->randomFloat(2, 250, 5000))
                        ->setSentAt($faker->dateTimeBetween('-6 months'))
                        ->setStatus($faker->randomElement(['SENT', 'PAID','CANCELED']))
                        ->setCustomer($customer)
                        ->setChrono($chrono);
                    $chrono++;
                    $manager->persist($invoice);
                }
            }
        }

        $manager->flush();
    }
}
