<?php

namespace App\Tests\Controllers\Admin;

use Symfony\Component\Panther\PantherTestCase;
use App\Tests\RoleUser;

class AdminControllerSubscriptionTest extends PantherTestCase
{
    use RoleUser;

   public function testDeleteSubscription()
   {
       $crawler = $this->client->request('GET', '/admin/');
       $link = $crawler->filter('a:contains("cancel plan")')->link();
       $this->client->click($link);
       $this->client->request('GET', '/video-list/category/toys/2');
       $this->assertContains('<b>MEMBERS</b>',$this->client->getResponse()->getContent());
   }
}
