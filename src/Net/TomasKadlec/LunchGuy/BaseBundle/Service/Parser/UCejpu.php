<?php
namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

/**
 * Class UCejpu
 *
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class UCejpu extends AbstractParser
{
    protected $filter = [
    ];

    protected static $selector = '#daily-meals table.list[style="display:table;"] tr';

    protected static $selectorDate = '#daily-meals div.bookmarks div.item.active';

    public function isSupported($format)
    {
        return ($format == 'ucejpu');
    }

    public function supports()
    {
        return [ 'ucejpu' ];
    }


    public function parse($format, $data, $charset = 'UTF-8')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not supported.");
        $date = $this
            ->getCrawler($data, $charset)
            ->filter(static::$selectorDate)
            ->first()
            ->text();
        $today = true;
        if (!empty($date) && is_string($date) && preg_match_all('/^\s*\w+\s*(\d+)\.\s+(\d+)\..*/u', $date, $parts)) {
            $date = \DateTime::createFromFormat('j.n.Y', $parts[1][0] . '.' . $parts[2][0] . '.' . date('Y'));
            if ($date !== false && ((new \DateTime('today'))->getTimestamp() - $date->getTimestamp()) > (24 * 60 * 60))
                $today = false;
        }

        if ($today) {
            return parent::parse($format, $data, $charset);
        } else {
            return [];
        }
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
        $soup = true;
        foreach ($data as $row) {
            if ($soup) {
                $key = static::KEY_SOUPS;
                $soup = false;
            } else {
                $key = static::KEY_MAIN;
            }

            if ($key !== null && !empty($row[1])) {
                $result[$key][] = [
                    trim(preg_replace('/[(][^)]*[)]+/', '', $row[1])),
                    (!empty($row[2]) ? intval($row[2]) : '-')
                ];
            }
        }
        return $result;
    }
}