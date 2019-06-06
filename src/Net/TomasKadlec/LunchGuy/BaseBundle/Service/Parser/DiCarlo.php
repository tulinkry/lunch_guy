<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use DateTime;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class DiCarlo
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class DiCarlo extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '.food-menu__table-table tr';

    public function isSupported($format)
    {
        return ($format == 'dicarlo');
    }

    public function supports()
    {
        return ['dicarlo'];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data)
    {
        $data = array_map(function ($el) {
            return array_values(array_filter($el));
        }, $data);

        $result = [];
        foreach ($data as $row) {
            if (preg_match('/polévka/ui', $row[0])) {
                $row[0] = preg_replace('/polévka:\s*/ui', '', $row[0]);
                $key = static::KEY_SOUPS;
            } else if (preg_match('/salát/ui', $row[0])) {
                $key = static::KEY_SALADS;
            } else {
                $key = static::KEY_MAIN;
            }

            $food = trim($row[0]);
            $food = preg_replace('/(\d+\s*,\s*)*(\s*\d+\s*)*\s*$/', '', $food);
            $food = mb_split('\n', $food)[0];

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