<?php declare(strict_types=1);

namespace Kitpages\DataGridBundle\Tool;

use Kitpages\DataGridBundle\DataGridException;

class UrlTool
{
    public function changeRequestQueryString(string $url, string|array $mixedKey = [], mixed $value = null): string
    {
        if (is_string($mixedKey)) {
            $changeTab = ["$mixedKey" => $value];
        } else {
            $changeTab = $mixedKey;
        }

        $parseTab = parse_url($url);
        if (!is_array($parseTab)) {
            throw new DataGridException(sprintf('Url "%s" is not valid.', $url));
        }

        $queryString = '';

        if (array_key_exists('query', $parseTab)) {
            $queryString = $parseTab['query'];
        }

        parse_str($queryString, $query);

        foreach ($changeTab as $key => $val) {
            $query[$key] = $val;
        }

        $parseTab['query'] = http_build_query($query, '', '&');

        return ((isset($parseTab['scheme'])) ? $parseTab['scheme'] . '://' : '')
            . ((isset($parseTab['user'])) ? $parseTab['user'] . ((isset($parseTab['pass'])) ? ':' . $parseTab['pass'] : '') . '@' : '')
            . ((isset($parseTab['host'])) ? $parseTab['host'] : '')
            . ((isset($parseTab['port'])) ? ':' . $parseTab['port'] : '')
            . ((isset($parseTab['path'])) ? $parseTab['path'] : '')
            . ((!empty($parseTab['query'])) ? '?' . $parseTab['query'] : '')
            . ((isset($parseTab['fragment'])) ? '#' . $parseTab['fragment'] : '');
    }
}
