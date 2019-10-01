<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Entity\Category;

class AdminControllerCategoriesTest extends WebTestCase
{
    public function setUp()
    {
    parent::setUp();
    $this->client = static::createClient();
    $this->client->disableReboot(); // prevents from shutting down the kernel between test request and thus losing transactions
    $this->entityManager = $this->client->getContainer()->get('doctrine.orm.entity_manager');
    // these lines helps isolate databse so it will roll back after request is done
    $this->entityManager->beginTransaction();
    $this->entityManager->getConnection()->setAutoCommit(false);
    }

    public function tearDown()
    {
        parent::tearDown();
        // these lines helps isolate databse so it will roll back after request is done
        $this->entityManager->rollBack();
        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }

    public function testTextOnPage()
    {
    $crawler = $this->client->request('GET', '/admin/categories');
    $this->assertSame('Categories list', $crawler->filter('h2')->text());
    $this->assertContains('Electronics', $this->client->getResponse()->getContent());
    }

    public function testNumberOfItems()
    {
        $crawler = $this->client->request('GET', '/admin/categories');
        $this->assertCount(21, $crawler->filter('option'));
    }

    public function testNewCategory()
    {
        $crawler = $this->client->request('GET', '/admin/categories');
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
    {
        $crawler = $this->client->request('GET', '/admin/edit-category/1');
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
    {
        $crawler = $this->client->request('GET', '/admin/delete-category1');
        $category = $this->entityManager->getRepository(Category::class)->find(1);
        $this->assertNull($category);
    }
}
