<?php

namespace Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser;

use Ottosmops\Pdftotext\Extract;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class PerfectCanteen
 * @package Net\TomasKadlec\LunchGuy\BaseBundle\Service\Parser
 */
class PerfectCanteen extends AbstractParser
{
    private $days = [
        'pondělí',
        'úterý',
        'středa',
        'čtvrtek',
        'pátek',
        'sobota',
        'neděle',
    ];

    /** @inheritdoc */
    public function isSupported($format)
    {
        return ($format == 'pf');
    }

    /** @inheritdoc */
    public function supports()
    {
        return ['pf'];
    }

    /** @inheritdoc */
    public function parse($format, $data, $charset = 'UTF-8')
    {
        if (!$this->isSupported($format))
            return new \RuntimeException("Format {$format} is not upported.");

        $text = $this->readPdfContent($data);
        return $this->process(explode("\n", $text));
    }

    /** @inheritdoc */
    protected function process($data)
    {
        $key = null;
        $reading = $week = false;
        $result = [];

        foreach($data as $row) {
            if(mb_convert_case($row, 1) == 'týdenní nabídka') {
                $reading = false;
                $week = true;
                continue;
            }

            if(mb_convert_case($row, 1) == 'přílohy') {
                break;
            }

            if(in_array(mb_convert_case($row, 1), $this->days)) {
                $reading = false;
            }

            if(in_array(mb_convert_case($row, 1), $this->days) && $this->days[date('N') - 1] == mb_convert_case($row, 1)) {
                $reading = true;
                continue;
            }

            if(mb_substr(mb_convert_case($row, 1), 0, 9) == 'každý den') {
                continue;
            }

            if($reading) {
                $price = intval(preg_replace("/.*?(\d+)(,-|\s*Kč).*/ui", '\1', $row));
                $food = preg_replace("/(\d+)(,-|\s*Kč).*/ui", '', $row);
                $food = preg_replace('/(\s*\d+\w*\s*,)*(\s*\d+\w*\s*)*\s*$/ui', '', $food);
                $food = preg_replace('/^\s*\d+\s*g\s*(-\s*)?/ui', '', $food);
                $result[self::KEY_MAIN][] = [$food, $price];
            }

            if($week) {
                if (preg_match('/pasta fresca/ui', $row)) {
                    $key = 'Týdenní těstoviny';
                    continue;
                } else if (preg_match('/special/ui', $row)) {
                    $key = 'Týdenní specialita';
                    continue;
                } else if (preg_match('/perfect steak/ui', $row)) {
                    $key = 'Týdenní steak';
                    continue;
                }

                $price = intval(preg_replace("/.*?(\d+)(,-|\s*Kč).*/ui", '\1', $row));
                $food = preg_replace("/(\d+)(,-|\s*Kč).*/ui", '', $row);
                $food = preg_replace('/(\s*\d+\w*\s*,)*(\s*\d+\w*\s*)*\s*$/ui', '', $food);
                $food = preg_replace('/^\s*\d+\s*g\s*(-\s*)?/ui', '', $food);
                $result[$key][] = [$food, $price];
            }
        }

        return $result;
    }

    /**
     * @param $data
     * @return mixed
     * @throws \Ottosmops\Pdftotext\Exceptions\FileNotFound
     */
    protected function readPdfContent($data)
    {
        $pdf = tempnam(sys_get_temp_dir(), "perfect-canteen");
        file_put_contents($pdf, $data);

        $text = (new Extract())
            ->pdf($pdf)
            ->text();

        unlink($pdf);
        return $text;
    }
}