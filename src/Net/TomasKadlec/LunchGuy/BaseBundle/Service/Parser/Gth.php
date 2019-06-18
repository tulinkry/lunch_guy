<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use DateTime;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Gth
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Gth extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '.dishes_list_wrapper .day_wrapper .meat_wrapper';

    public function isSupported($format)
    {
        return ($format == 'gth');
    }

    public function supports()
    {
        return ['gth'];
    }

    /** @inheritdoc */
    public function parse($format, $data, $charset = 'UTF-8')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");

        $date = $this
            ->getCrawler($data, $charset)
            ->filter('.dishes_list_wrapper .day_wrapper h4')
            ->each(function (Crawler $node) {
                return $node->children()->each(function (Crawler $child) {
                    return $child->text();
                });
            });

        if (count($date) === 0 || count($date[0]) === 0) {
            // no date
            return [];
        }

        list($day, $date) = explode(' - ', $date[0][0]);
        $date = DateTime::createFromFormat('j.n.Y', $date);
        if ($date === false ||
            ($date->getTimestamp() - (new DateTime('today'))->getTimestamp()) < 0 ||
            ($date->getTimestamp() - (new DateTime('today'))->getTimestamp()) > (24 * 60 * 60)) {
            // date is not today
            return [];
        }

        return parent::parse($format, $data, $charset);
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
        foreach ($data as $row) {
            $key = null;

            if (empty($row))
                continue;


            if (count($row) !== 3) {
                continue;
            }

            if (preg_match('/Polévka/', $row[0]))
                $key = static::KEY_SOUPS;
            else if (preg_match('/(Menu)|(Minutka)/', $row[0]))
                $key = static::KEY_MAIN;
            else if (preg_match('/(Teplý pult)/', $row[0]))
                $key = 'Teplý pult';
            else if (preg_match('/Zeleninový talíř/', $row[0]))
                $key = static::KEY_SALADS;

            $row[0] = preg_replace('/Polévka\s*\d*\s*/', '', $row[0]);
            $row[0] = preg_replace('/((Menu)|(Minutka))\s*\d*\s*/', '', $row[0]);
            $row[0] = preg_replace('/(Teplý pult)\s*\d*\s*/', '', $row[0]);
            $row[0] = preg_replace('/Zeleninový talíř\s*\d*\s*/', '', $row[0]);

            if ($key !== null && isset($row[0]) && isset($row[2])) {
                $result[$key][] = [
                    $row[0],
                    intval($row[2])
                ];
            }
        }
        return $result;
    }
}