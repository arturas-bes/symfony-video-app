<?php

namespace App\Tests\Controllers\Admin;

use App\Tests\RoleUser;
use Symfony\Component\Panther\PantherTestCase;

class AdminControllerTranslationTest extends PantherTestCase
{
    use RoleUser;

    public function testDeleteSubscription()
    {
        $this->client->request('GET', '/lt/admin/');

        $this->assertContains('Mano profilis',$this->client->getResponse()->getContent());
        $this->assertNotContains('vaizdo-irašai',$this->client->getResponse()->getContent());
    }
}
