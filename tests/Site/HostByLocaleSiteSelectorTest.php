<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Tests\Site;

use Sonata\PageBundle\CmsManager\DecoratorStrategyInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\PageBundle\Request\SiteRequest;
use Sonata\PageBundle\Site\HostByLocaleSiteSelector;
use Sonata\SeoBundle\Seo\SeoPageInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @author Rémi Marseille <marseille@ekino.com>
 */
final class HostByLocaleSiteSelectorTest extends BaseLocaleSiteSelectorTest
{
    protected function setUp(): void
    {
        $siteManager = $this->createMock(SiteManagerInterface::class);
        $decoratorStrategy = $this->createMock(DecoratorStrategyInterface::class);
        $seoPage = $this->createMock(SeoPageInterface::class);

        $this->siteSelector = $this->getMockBuilder(HostByLocaleSiteSelector::class)
            ->setConstructorArgs([$siteManager, $decoratorStrategy, $seoPage])
            ->setMethods(['getSites'])
            ->getMock();
    }

    /**
     * Tests handleKernelRequest method selects the site /en.
     */
    public function testHandleKernelRequestSelectsEn(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com');

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteSelector
            ->expects(static::once())
            ->method('getSites')
            ->with($request)
            ->willReturn($this->getSites());

        $this->siteSelector->handleKernelRequest($event);

        // Ensure request locale is en
        static::assertSame('en', $request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure we retrieved the site "/en"
        static::assertSame('/en', $site->getRelativePath());
    }

    /**
     * Tests handleKernelRequest method selects the site /fr.
     */
    public function testHandleKernelRequestSelectsFr(): void
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = SiteRequest::create('http://www.example.com', 'GET', [], [], [], [
            'HTTP_ACCEPT_LANGUAGE' => 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4',
        ]);

        // Ensure request locale is null
        static::assertNull($request->attributes->get('_locale'));

        $event = new GetResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST);

        $this->siteSelector
            ->expects(static::once())
            ->method('getSites')
            ->with($request)
            ->willReturn($this->getSites());

        $this->siteSelector->handleKernelRequest($event);

        // Ensure request locale is fr
        static::assertSame('fr', $request->attributes->get('_locale'));

        $site = $this->getSite();

        // Ensure we retrieved the site "/fr"
        static::assertSame('/fr', $site->getRelativePath());
    }
}
