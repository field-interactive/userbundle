<?php

namespace Field\UserBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DefaultControllerTest extends WebTestCase
{
    /**
     * PHPUnit's data providers allow to execute the same tests repeated times
     * using a different set of data each time.
     * See http://symfony.com/doc/current/cookbook/form/unit_testing.html#testing-against-different-sets-of-data.
     *
     * @dataProvider getPublicUrls
     */
    public function testPublicUrls($url)
    {
        $client = static::createClient();
        $client->followRedirects();
        $client->request('GET', $url);

        $this->assertSame(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode(),
            sprintf('The %s public URL loads correctly.', $url)
        );
    }

    /**
     * The application contains a lot of secure URLs which shouldn't be
     * publicly accessible. This tests ensures that whenever a user tries to
     * access one of those pages, a redirection to the login form is performed.
     *
     * @dataProvider getSecureUrls
     */
    public function testSecureUrls($url)
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $response = $client->getResponse();
        $this->assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        $this->assertSame(
            'http://localhost/login',
            $response->getTargetUrl(),
            sprintf('The %s secure URL redirects to the login form.', $url)
        );
    }

    public function getPublicUrls()
    {
        yield ['/'];
        yield ['/profile/'];
        yield ['/profile/edit'];
        yield ['/profile/edit/confirm'];
        yield ['/profile/change-password'];
        yield ['/profile/delete'];
        yield ['/register/'];
        yield ['/request'];
        yield ['/login'];
        yield ['/logout'];
    }

    public function getSecureUrls()
    {
        yield ['/admin/'];
        yield ['/admin/1'];
        yield ['/admin/1/edit'];
        yield ['/admin/1/reset-password'];
        yield ['/admin1/delete'];
        yield ['/admin1/deactivate'];
        yield ['/admin1/activate'];
    }
}
