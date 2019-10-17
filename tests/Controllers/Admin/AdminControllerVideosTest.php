<?php

namespace App\Tests\Controllers\Admin;

use App\Entity\Video;
use Symfony\Component\Panther\PantherTestCase;
use App\Tests\RoleAdmin;

class AdminControllerVideosTest extends PantherTestCase
{
    use RoleAdmin;

    public function testDeleteVideo()
    {

         $this->client->request('GET', '/admin/su/delete-video/1/367009399');
         $video = $this->entityManager->getRepository(Video::class)->find(1);
         $this->assertNull($video);
    }
}
