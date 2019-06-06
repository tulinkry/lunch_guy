<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use DateTime;
use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CafeEmpiria
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class CafeEmpiria extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '.active ul';

    public function isSupported($format)
    {
        return ($format == 'cafeempiria');
    }

    public function supports()
    {
        return ['cafeempiria'];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data)
    {
        $result = [];
        $result = $this->extractKeyFood($data[0], self::KEY_SOUPS, $result);
        $result = $this->extractKeyFood($data[1], self::KEY_MAIN, $result);
        $result = $this->extractKeyFood($data[2], 'Bezmasé jídlo', $result);
        $result = $this->extractKeyFood($data[3], self::KEY_MAIN, $result);

        return $result;
    }

    /**
     * @param $data
     * @param array $result
     * @return array
     */
    protected function extractKeyFood($data, $key, array $result)
    {
        foreach ($data as $food) {
            $food = preg_replace('/\n/', ' ', $food);
            $price = (preg_replace('/.*?(\d+)\s*$/', '\1', $food));
            $food = (preg_replace('/(\d+)\s*$/', '', $food));
            $food = preg_replace('/(\d+\s*,)*(\s*\d+\s*)*\s*$/', '', $food);
            $food = preg_replace('/\[\s+kcal\]/ui', '', $food);
            $result[$key][] = [
                $food,
                $price
            ];
        }
        return $result;
    }
}