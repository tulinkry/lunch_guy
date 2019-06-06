<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use DateTime;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Coolna
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Coolna extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '#lcolumn table tr';

    public function isSupported($format)
    {
        return ($format == 'coolna');
    }

    public function supports()
    {
        return ['coolna'];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data)
    {
        $data = array_slice(array_slice($data, 1), 0, count($data) - 6);
        $data = array_map(function ($el) {
            return array_values(array_filter($el, function ($x) {
                return !preg_match('/^\s*$/ui', $x);
            }));
        }, $data);

        $result = [];

        foreach ($data as $row) {
            if (preg_match('/polévka/ui', $row[0]) || preg_match('/boršč/ui', $row[0])) {
                $key = static::KEY_SOUPS;
            } else if (preg_match('/salát/ui', $row[0])) {
                $key = static::KEY_SALADS;
            } else {
                $key = static::KEY_MAIN;
            }

            $food = trim($row[0]);

            if ($key !== null && !empty($row[0]) && !empty($row[1])) {
                $result[$key][] = [
                    $food,
                    intval($row[1])
                ];
            }
        }

        return $result;
    }
}