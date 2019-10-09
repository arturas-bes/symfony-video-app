<?php

namespace App\Tests\Controller\Front;


use App\Tests\RoleUser;
use Symfony\Component\Panther\PantherTestCase;

class FrontControllerLikesTest extends PantherTestCase
{
    use RoleUser;

    public function testLike()
    {
        $this->client->request('POST', '/video-list/11/like');
        $crawler = $this->client->request('GET', '/video-list/category/movies/4');

        $this->assertSame('(3)', $crawler->filter('small.number-of-likes-11')->text());
    }

    public function testDislike()
    {
        $this->client->request('POST', '/video-list/10/dislike');
        $crawler = $this->client->request('GET', '/video-list/category/movies/4');

        $this->assertSame('(4)', $crawler->filter('small.number-of-dislikes-10')->text());
    }

    public function testsNumberOfLikedVideos1()
    {
        $this->client->request('POST', '/video-list/11/like');
        // do the same request again so we make sure that the app is secured about this
        $this->client->request('POST', '/video-list/11/like');

        $crawler = $this->client->request('GET', '/admin/videos');

        $this->assertEquals(4, $crawler->filter('tr')->count());

    }

    public function testsNumberOfLikedVideos2()
    {
        $this->client->request('POST', '/video-list/1/unlike');
        $this->client->request('POST', '/video-list/12/unlike');


        $crawler = $this->client->request('GET', '/admin/videos');

        $this->assertEquals(1, $crawler->filter('tr')->count());

    }
}
