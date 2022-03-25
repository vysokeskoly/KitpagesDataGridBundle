<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Functional;

use Kitpages\DataGridBundle\Hydrators\DataGridHydrator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConfigTest extends WebTestCase
{
    public function testConfigParsing(): void
    {
        $client = self::createClient();
        $gridParameters = $client->getContainer()->getParameter('kitpages_data_grid.grid');
        $this->assertIsArray($gridParameters);
        $this->assertEquals('@KitpagesDataGrid/Grid/grid-standard.html.twig', $gridParameters['default_twig']);
        $this->assertEquals(DataGridHydrator::class, $gridParameters['hydrator_class']);
    }
}
