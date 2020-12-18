<?php namespace App\Controllers;

use SimpleXMLElement;
use ZipArchive;

class Combine extends BaseController
{
    protected $helpers = ['url', 'form', 'xml'];

    public function index()
    {
        return view('combine');
    }

    public function upload()
    {
        $id = $this->request->getFile('muse.id');
        $jp = $this->request->getFile('muse.jp');
        $en = $this->request->getFile('muse.en');
        $filename = $this->request->getVar('filename');

        $xmls = [
            'id' => new SimpleXMLElement(file_get_contents($id->getTempName())),
            'jp' => new SimpleXMLElement(file_get_contents($jp->getTempName())),
            'en' => new SimpleXMLElement(file_get_contents($en->getTempName())),
        ];

        $total_verses = count($xmls['id']->xpath('/museScore/Score/Staff/Measure[1]/voice/Chord[2]/Lyrics'));

        $new_xml = new SimpleXMLElement($xmls['id']->saveXML());

        // remove all lyrics
        foreach ($new_xml->xpath('/museScore/Score/Staff/Measure/voice/Chord/Lyrics') as $ol) {
            unset($ol[0]);
        }

        // prepare for zipping
        $zip = new ZipArchive();
        $zip_file_path = WRITEPATH . 'generated/' . $filename . '.zip';
        $zip->open($zip_file_path, ZipArchive::CREATE);

        // copy lyrics
        for ($verse_num = 1; $verse_num <= $total_verses; $verse_num++) {
            $measure_num = 1;
            while ($measure_num <= count($new_xml->xpath('/museScore/Score/Staff/Measure'))) {
                foreach ($new_xml->xpath('/museScore/Score/Staff/Measure[' . $measure_num . ']/voice/Chord') as $chord_num => $chord) {
                    // add Lyric
                    $langs = ['id', 'jp', 'en'];
                    foreach ($langs as $lang_num => $lang) {
                        $lyric = $chord[0]->addChild('Lyrics');
                        $syllabic = $this->getLyricChildValue($xmls[$lang], $measure_num, $chord_num + 1, $verse_num,
                            'syllabic');
                        if ($syllabic) {
                            $lyric->addChild('syllabic', $syllabic);
                        }
                        $text = $this->getLyricChildValue($xmls[$lang], $measure_num, $chord_num + 1, $verse_num,
                            'text');
                        if ($text) {
                            $lyric->addChild('text', $text);
                        }

                        // add lyric number
                        if ($lang !== 'id') {
                            $lyric->addChild('no', $lang_num);
                        }
                    }

                }

                $measure_num++;
            }

            $name = $filename . '_' . $verse_num . '.mscx';
            $zip->addFromString($name, $new_xml->saveXML());
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zip_file_path) . '"');
        header('Content-Length: ' . filesize($zip_file_path));

        flush();
        readfile($zip_file_path);
        unlink($zip_file_path); // delete file
    }

    private function getLyricChildValue($xmls, $measure_num, $chord_num, $verse_num, $tag_name)
    {
        $val = $xmls->xpath('/museScore/Score/Staff/Measure[' . $measure_num . ']/voice/Chord[' . $chord_num . ']/Lyrics[' . $verse_num . ']/' . $tag_name . '/text()');
        return (count($val) > 0) ? (string)$val[0] : false;
    }
}
