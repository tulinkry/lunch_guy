<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

/**
 * Class Mattes
 *
 * Parser implementation for 
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Mattes extends AbstractParser
{

    protected $filter = [];

    protected static $selector = 'div.dayOffer table tr';

    public function isSupported($format)
    {
        return ($format == 'mattes');
    }

    public function supports()
    {
        return [ 'mattes' ];
    }

    /**
     * Transforms data from the crawler to an internal array
     *
     * @param $data
     * @return array
     */
    protected function process($data) {
        $key = null;
        $result = [];

        foreach ($data as $row) {
            
            if (empty($row) || count($row) < 3)
                continue;

            if($key === null || empty($row[0]) || intval($row[2]) < 60) {
                $key = static::KEY_SOUPS;
            } else {
                $key = static::KEY_MAIN;
            }

            if ($key !== null) {
                // remove alergens
                $name = preg_replace('/(\d+\s*,)*(\s*\d+\s*)*$/', '', trim($row[1]));
                $result[$key][] = [
                    $name,
                    intval($row[2])
                ];
            }
        }
        return $result;
    }

}

//Zeleninový salát se smaženými kuřecími stripsy 1,3,7,12