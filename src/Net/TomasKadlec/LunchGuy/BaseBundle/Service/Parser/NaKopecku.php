<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 25/04/2018
 * Time: 09:03
 */

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;


/**
 * Class NaKopecku
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class NaKopecku extends AbstractParser
{
    protected static $selector = 'table.dailyMenuTable tr';

    public function isSupported($format)
    {
        return ($format == 'nakopecku');
    }

    public function supports()
    {
        return ['nakopecku'];
    }


    /**
     * Takes decision on filtering data resulting from the crawler
     *
     * @param $row
     * @return bool
     */
    protected function filter($row)
    {
        if (empty($row))
            return true;
        return false;
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
        $result = [];
        foreach ($data as $row) {
            if (count($row) == 1) {
                if (preg_match('/Polévk/ui', $row[0]))
                    $key = static::KEY_SOUPS;
                else if (preg_match('/(Minutka)|(Hlavní chod)|(Dieta)/ui', $row[0]))
                    $key = static::KEY_MAIN;
                else if (preg_match('/(Menu)/ui', $row[0]))
                    $key = null; // ommit menu
                else if (preg_match('/(Dezert)/ui', $row[0]))
                    $key = static::KEY_DESERTS;
                else if (preg_match('/(Zeleninový talíř|Salát)/ui', $row[0]))
                    $key = static::KEY_SALADS;
                else
                    $key = null;
                continue;
            }

            if ($key !== null && !empty($row[1])) {
                $result[$key][] = [
                    trim($row[1]),
                    (!empty($row[2]) ? intval($row[2]) : '-')
                ];
            }
        }
        return $result;
    }
}