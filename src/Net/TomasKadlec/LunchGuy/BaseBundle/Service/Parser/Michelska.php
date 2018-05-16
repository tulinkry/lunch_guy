<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Michelska
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class Michelska extends AbstractParser
{

    protected $filter = [
    ];

    protected static $selector = 'table.MsoNormalTable:first-of-type tr';

    public function parse($format, $data, $charset = 'UTF-8')
    {
        return parent::parse($format, $data, 'windows-1250');
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
        foreach ($this->filter as $skip) {
            if (isset($row[1]) && preg_match("/{$skip}/", $row[1]))
                return true;
        }
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
            if(preg_match('/polévky/ui', $row[1])) {
                $key = static::KEY_SOUPS;
                continue;
            } else if (preg_match('/salát/ui', $row[1])) {
                $key = static::KEY_SALADS;
                continue;
            } else if (
                       preg_match('/bezlepková\s*nabídka/ui', $row[1]) ||
                       preg_match('/zvýhodněné\s*menu/ui', $row[1]) ||
                       preg_match('/hotových\s*jídel/ui', $row[1]) ||
                       preg_match('/specialita\s*dne/ui', $row[1])) {
                $key = static::KEY_MAIN;
                continue;
            } else if (preg_match('/^\s*\(.*\)\s*$/ui', $row[1]) ||
                       preg_match('/^\s*\*/ui', $row[1])) {
                continue;
            } else if (intval($row[2]) === 0) {
                continue;
            }

            if ($key !== null && !empty($row[1]) && mb_strlen(trim($row[1])) > 1) {
                $result[$key][] = [
                    trim($row[1]),
                    (!empty($row[2]) ? 0 + intval($row[2]) : '-')
                ];
            }
        }

        return $result;
    }

    /** @inheritdoc */
    public function isSupported($format)
    {
        return ($format == 'michelska');
    }

    /** @inheritdoc */
    public function supports()
    {
        return ['michelska'];
    }

}