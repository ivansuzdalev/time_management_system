<?php

namespace App\DataFixtures;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixture extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)

    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        $user = new User();
        $user->setUsername('test@gmail.com');
        $user->setRoles(['ROLE_USER']);

        $password = $this->passwordEncoder->encodePassword($user, '123');
        $user->setPassword($password);
        $manager->persist($user);

        // add more products

        $manager->flush();
    }
}
