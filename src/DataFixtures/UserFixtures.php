<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->getUserData() as [$name, $last_name, $email, $password, $api_key, $roles]) {
            $user = new User();
            $user->setName($name);
            $user->setLastName($last_name);
            $user->setEmail($email);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $user->setRoles($roles);
            $user->setVimeoApiKey($api_key);
            $manager->persist($user);
        }
        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            ['John', 'Wayne', 'john@gmail.com', 'password', '4bee732885565b209c2482cc39adc8fd', ['ROLE_ADMIN']],
            ['John', 'Doe', 'john.doe@gmail.com', 'password', null, ['ROLE_ADMIN']],
            ['Keven', 'Baker', 'keven@gmail.com', 'password', null, ['ROLE_USER']],
            ['admin', 'admin', 'admin@admin.com', 'kaskas', '4bee732885565b209c2482cc39adc8fd', ['ROLE_USER', 'ROLE_ADMIN']],
            ['user', 'user', 'user@user.com', 'kaskas', null, ['ROLE_USER']],
            ['Ted', 'Bundy', 'ted@user.com', 'password', null, ['ROLE_USER']],
        ];
    }
}
