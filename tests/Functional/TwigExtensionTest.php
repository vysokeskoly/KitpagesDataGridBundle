<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Templating\EngineInterface;

/**
 * @group Fonctionnal
 */
class TwigExtensionTest extends WebTestCase
{
    public function testConfigParsing(): void
    {
        $client = self::createClient();

        /** @var EngineInterface $templating */
        $templating = $client->getContainer()->get('twig');
        $this->assertEquals(
            '@KitpagesDataGrid/Grid/grid-standard.html.twig',
            $templating->render('globals.html.twig')
        );
    }
}
