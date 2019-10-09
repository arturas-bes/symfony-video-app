<?php


namespace App\Tests;


use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait RoleAdmin
{
    public function setUp()
    {
        $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'john@gmail.com',
            'PHP_AUTH_PW' => 'password',
        ]);
// we dont use this because we use package for that dama/doctrine-test-bundle
        $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
//        $this->entityManager->beginTransaction();
//        $this->entityManager->getConnection()->setAutoCommit(false);
    }

    public function tearDown()
    {
        // these lines helps isolate databse so it will roll back after request is done
        //    $this->entityManager->rollBack();
        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks

    }

    private function logIn()
    {
        $session = $this->client->getContainer()->get('session');

        $firewallName = 'secure_area';
        // if you don't define multiple connected firewalls, the context defaults to the firewall name
        // See https://symfony.com/doc/current/reference/configuration/security.html#firewall-context
        $firewallContext = 'secured_area';

        // you may need to use a different token class depending on your application.
        // for example, when using Guard authentication you must instantiate PostAuthenticationGuardToken
        $token = new UsernamePasswordToken('admin', null, $firewallName, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }
}