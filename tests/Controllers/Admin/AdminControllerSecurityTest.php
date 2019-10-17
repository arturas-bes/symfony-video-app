<?php

namespace App\Tests\Controllers\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\PantherTestCase;

class AdminControllerSecurityTest extends PantherTestCase
{
    /**
     * @param string $httpMethod
     * @param string $url
     * @dataProvider getUrlsForRegularUsers
     */
    public function testAccessDeniedForRegularUsers(string $httpMethod, string $url)
    {
       $client = $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'user@user.com',
            'PHP_AUTH_PW' => 'kaskas',
        ]);
        $client->request($httpMethod, $url);
        $this->assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
    }

    public function getUrlsForRegularUsers()
    {
        yield ['GET', '/admin/su/categories'];
        yield ['GET', '/admin/su/edit-category/1'];
        yield ['GET', '/admin/su/delete-category/1'];
        yield ['GET', '/admin/su/users'];
        yield ['GET', '/admin/su/upload-video-locally'];
    }

    public function testAdminSu()
    {
        $client = $this->client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin@admin.com',
            'PHP_AUTH_PW' => 'kaskas',
        ]);
        $crawler = $client->request('GET', '/admin/su/categories');
        $this->assertSame('Categories list', $crawler->filter('h2')->text());
    }
}
