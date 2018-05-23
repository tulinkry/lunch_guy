<?php
/**
 * Created by PhpStorm.
 * User: ktulinger
 * Date: 23/05/2018
 * Time: 08:01
 */

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;


class Nola extends AbstractParser
{

    protected $filter = [];

    protected static $selector = '#lunch div.one .menu_post';

    public function isSupported($format) {
        return ($format == 'nola');
    }

    public function supports() {
        return [ 'nola' ];
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

            if (preg_match('/polévk(a|y)/ui', $row[0])) {
                $key = static::KEY_SOUPS;
            } else {
                $key = static::KEY_MAIN;
            }

            if ($key !== null) {
                $result[$key][] = [
                    trim($row[0]),
                    intval($row[2])
                ];
            }
        }
        return $result;
    }

}