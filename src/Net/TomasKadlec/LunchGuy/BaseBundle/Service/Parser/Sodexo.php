<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use DateTime;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Gth
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Sodexo extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '.food tr';

    public function isSupported($format)
    {
        return ($format == 'sodexo');
    }

    public function supports()
    {
        return ['sodexo'];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data)
    {
        $data = array_filter($data, function($el) { return count($el) > 2; });
        $data = array_map('array_filter', $data);
        $data = array_map('array_values', $data);

        $result = [];
        foreach ($data as $row) {
            $key = null;

            if (count($row) < 3 or count($row) > 4) {
                continue;
            }

            if (preg_match('/Polévka/ui', $row[0]))
                $key = static::KEY_SOUPS;
            else if (preg_match('/(Menu|Minutka|Klasika)/ui', $row[0])) {
                unset($row[1]);
                $row = array_values($row);
                $key = static::KEY_MAIN;
            }
            else if (preg_match('/(Food Market)/ui', $row[0])) {
                $row[2] .= ' ' . $row[1];
                unset($row[1]);
                $row = array_values($row);
                $key = 'Teplý pult';
            }

            if ($key !== null && isset($row[1]) && isset($row[2])) {
                $result[$key][] = [
                    $row[1],
                    intval($row[2])
                ];
            }
        }
        return $result;
    }
}