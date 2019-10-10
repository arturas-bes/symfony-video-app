<?php

namespace App\Tests\Controllers\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Tests\RoleAdmin;
use App\Entity\User;

class AdminControllerUsersTest extends WebTestCase
{
   use RoleAdmin;

   public function testUserDeleted()
   {
       $crawler = $this->client->request('GET', '/admin/su/delete-user/4');
       $user = $this->entityManager->getRepository(User::class)->find(4);

       $this->assertNull($user);
   }

}
