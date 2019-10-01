<?php
namespace App\Tests\Controller;

use App\Tests\Rollback;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use App\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdminControllerCategoriesTest extends WebTestCase
{
    use Rollback;

//    public function setUp()
//    {
//    parent::setUp();
//        $this->client = static::createClient([], [
//            'PHP_AUTH_USER' => 'john@gmail.com',
//            'PHP_AUTH_PW' => 'password',
//        ]);
//
//    $this->client->disableReboot(); // prevents from shutting down the kernel between test request and thus losing transactions
//    $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
//    // these lines helps isolate databse so it will roll back after request is done
//    $this->entityManager->beginTransaction();
//    $this->entityManager->getConnection()->setAutoCommit(false);
//    }

//    public function tearDown()
//    {
//        parent::tearDown();
//        // these lines helps isolate databse so it will roll back after request is done
//        $this->entityManager->rollBack();
//        $this->entityManager->close();
//        $this->entityManager = null; // avoid memory leaks
//    }

    public function testTextOnPage()
    {
        $this->logIn();
        $crawler = $this->client->request('GET', '/admin/su/categories');
        $this->assertSame('Categories list', $crawler->filter('h2')->text());
        $this->assertContains('Electronics', $this->client->getResponse()->getContent());
    }

    public function testNumberOfItems()
    {
        $crawler = $this->client->request('GET', '/admin/su/categories');
        $this->assertCount(21, $crawler->filter('option'));
    }

    public function testNewCategory()
    {   $this->logIn();
        $crawler = $this->client->request('GET', '/admin/su/categories');
        $form = $crawler->selectButton('Add')->form([
            'category[parent]' => 1,
            'category[name]' => 'Other electronics'
        ]);
        $this->client->submit($form);

        $category = $this->entityManager->getRepository(Category::class)->findOneBy(
            ['name' => 'Other electronics']
        );

        $this->assertNotNull($category);
        $this->assertSame('Other electronics', $category->getName());
    }

    public function testEditCategory()
    {   $this->logIn();
        $crawler = $this->client->request('GET', '/admin/su/edit-category/1');
        $form  = $crawler->selectButton('Save')->form([
            'category[parent]' => 0,
            'category[name]' => 'Electronics 2'
        ]);
        $this->client->submit($form);

        $category = $this->entityManager->getRepository(Category::class)->findOneBy(
            ['id' => '1']
        );

        $this->assertSame('Electronics 2', $category->getName());
    }

    public function testDeleteCategory()
    {   $this->logIn();
        $crawler = $this->client->request('GET', '/admin/su/delete-category/1');
        $category = $this->entityManager->getRepository(Category::class)->find(1);
        $this->assertNull($category);
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
