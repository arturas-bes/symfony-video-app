<?php

namespace App\Tests\Controllers\Admin;

use App\Tests\RoleUser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminControllerTranslationTest extends WebTestCase
{
    use RoleUser;

    public function testDeleteSubscription()
    {
        $this->client->request('GET', '/lt/admin/');

        $this->assertContains('Mano profilis',$this->client->getResponse()->getContent());
        $this->assertNotContains('vaizdo-irašai',$this->client->getResponse()->getContent());
    }
}
