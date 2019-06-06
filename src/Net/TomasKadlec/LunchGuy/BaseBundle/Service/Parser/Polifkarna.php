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
class Polifkarna extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '#content .item p';

    public function isSupported($format)
    {
        return ($format == 'polifkarna');
    }

    public function supports()
    {
        return ['polifkarna'];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data)
    {
        $data = array_map(function ($row) {
            return $row[0];
        }, $data);

        $result = [];
        foreach ($data as $row) {
            $row = preg_replace('/(\d+\s*,)*(\s*\d+\s*)*\s*$/', '', $row);
            $result[self::KEY_SOUPS][] = [
                trim($row),
                '-'
            ];
        }
        return $result;
    }
}