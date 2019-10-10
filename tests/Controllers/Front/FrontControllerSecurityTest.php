<?php

namespace App\Tests\Controllers\Front;


use Symfony\Component\Panther\PantherTestCase;

class FrontControllerSecurityTest extends PantherTestCase
{
    /**
     * @param string $url
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls(string $url)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', $url);
        $this->assertContains('/login', $client->getResponse()->getTargeturl());
    }

    public function getSecureUrls()
    {
        yield ['/admin/videos'];
        yield ['/admin'];
        yield ['/admin/su/categories'];
        yield ['/admin/su/delete-category/1'];
    }

    public function testVideoForMembersOnly()
    {
        $client = static::createClient();
        $client->request('GET', '/video-list/category/movies/4');
        $this->assertContains( '<b>MEMBERS</b>', $client->getResponse()->getContent() );

    }
}
