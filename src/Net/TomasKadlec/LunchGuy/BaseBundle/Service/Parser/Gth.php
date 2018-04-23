<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;
use DateTime;

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

    protected static $selector = 'div#menu_1 ul.foodmenu li.food, div#menu_1 ul.foodmenu li.day';

    public function isSupported($format)
    {
        return ($format == 'gth');
    }

    public function supports()
    {
        return [ 'gth' ];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data) {
        $result = [];

        $today = false;
        foreach ($data as $row) {
            $key = null;
            if (empty($row))
                continue;

            $food = array_values(array_map(function($e) {
                return preg_replace('/^\s*(.*)\s*$/u', '$1', $e);
            }, array_filter(explode("\n", $row[0]), function($e) {
                return !preg_match('/^\s*$/u', $e);
            })));

            if(count($food) === 1) {
                // date
                list($day, $date) = explode(' ', $food[0]);
                $date = DateTime::createFromFormat('j.n.Y', $date);
                if ($date !== false &&
                    ($date->getTimestamp() - (new DateTime('today'))->getTimestamp()) >= 0 &&
                    ($date->getTimestamp() - (new DateTime('today'))->getTimestamp()) <= (24 * 60 * 60)) {
                    $today = true;
                } else {
                    $today = false;
                }
                continue;
            }

            if(!$today) {
                continue;
            }

            if(count($food) !== 5) {
                continue;
            }

            if (preg_match('/Polévka/', $food[0]))
                $key = static::KEY_SOUPS;
            else if (preg_match('/(Menu)|(Minutka)/', $food[0]))
                $key = static::KEY_MAIN;
            else if (preg_match('/(Teplý pult)/', $food[0]))
                $key = 'Teplý pult';
            else if (preg_match('/Zeleninový talíř/', $food[0]))
                $key = static::KEY_SALADS;

            if ($key !== null && isset($food[1]) && isset($food[4])) {
                $result[$key][] = [
                    $food[1],
                    intval($food[4])
                ];
            }
        }
        return $result;
    }

}