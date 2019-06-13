<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class PragueCatering
 *
 * Parser implementation for
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class PragueCatering extends AbstractParser
{

    protected static $selector = 'table.dennimenu tr';

    public function isSupported($format)
    {
        return ($format == 'praguecatering');
    }

    public function supports()
    {
        return ['praguecatering'];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data)
    {
        $key = null;
        $price = 0;
        $result = [];

        for ($i = 0; $i < count($data); $i++) {
            $row = $data[$i];

            if (preg_match('/^\s*$/ui', $row[1])) {
                if (preg_match('/Polévk/ui', $row[0])) {
                    $key = static::KEY_SOUPS;
                    $i++;
                    continue;
                } else if (preg_match('/Hlavní/ui', $row[0])) {
                    $key = static::KEY_MAIN;
                    $i++;
                    continue;
                } else if (preg_match('/^Pizza$/ui', $row[0])) {
                    $key = 'Pizza';
                    $price = trim($data[$i+1][1]);
                    $i ++;
                    $i ++;
                    continue;
                } else if (preg_match('/Těstoviny/ui', $row[0])) {
                    $key = 'Těstoviny';
                    $i++;
                    continue;
                } else if (preg_match('/Buffet/ui', $row[0])) {
                    $key = 'Buffet';
                    $i++;
                    continue;
                }
            }


            if ($key == 'Pizza' && !preg_match('/^\s*$/ui', $row[0])) {
                // pizza has more rows
                $name = trim($row[0]);

                $result[$key][] = [
                    $name,
                    intval($price)
                ];
                $i ++;
                continue;
            }

            if (!preg_match('/^\s*$/ui', $row[1]) && !preg_match('/^\s*$/ui', $row[0])) {
                $name = trim($row[0]);
                $price = str_replace(' Kč', '', trim($row[1]));

                if ($key !== null) {
                    $result[$key][] = [
                        $name,
                        $key === 'Těstoviny' ? $price : intval($price)
                    ];
                    $i++;
                    continue;
                }
            }
        }
        return $result;
    }
}