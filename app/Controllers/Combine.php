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

        // get total verses
        $total_verses = count($xmls['id']->xpath('/museScore/Score/Staff/Measure[1]/voice/Chord[2]/Lyrics'));

        // get jp and en title
        $en_title = $this->getTitle($xmls['en']);
        $jp_title = $this->getTitle($xmls['jp']);

        // prepare for zipping
        $zip = new ZipArchive();
        $zip_file_path = WRITEPATH . 'generated/' . $filename . '.zip';
        $zip->open($zip_file_path, ZipArchive::CREATE);

        // copy lyrics
        for ($verse_num = 1; $verse_num <= $total_verses; $verse_num++) {
            $new_xml = new SimpleXMLElement($xmls['id']->saveXML());
            $measure_num = 1;

            // change title & remove subtitle
            $this->setTitle($new_xml, $en_title, $jp_title);

            // remove all lyrics
            $this->removeAllLyrics($new_xml);

            while ($measure_num <= count($new_xml->xpath('/museScore/Score/Staff/Measure'))) {
                foreach ($new_xml->xpath('/museScore/Score/Staff/Measure[' . $measure_num . ']/voice/Chord') as $chord_num => $chord) {
                    // add Lyric
                    $langs = ['id', 'jp', 'en'];
                    foreach ($langs as $lang_num => $lang) {
                        $this->addLyricsTag($chord, $xmls[$lang], $lang, $measure_num, $chord_num, $verse_num, $lang_num);
                    }
                }
                $measure_num++;
            }

            $name = $filename . '_' . $verse_num . '.mscx';
            $zip->addFromString($name, $new_xml->saveXML());
        }

        $zip->close();
        $this->downloadZipFile($zip_file_path);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param $measure_num
     * @param $chord_num
     * @param $verse_num
     * @param $tag_name
     * @return bool|string
     */
    private function getLyricChildValue(SimpleXMLElement $xml, $measure_num, $chord_num, $verse_num, $tag_name)
    {
        $val = $xml->xpath('/museScore/Score/Staff/Measure[' . $measure_num . ']/voice/Chord[' . $chord_num . ']/Lyrics[' . $verse_num . ']/' . $tag_name . '/text()');
        return (count($val) > 0) ? (string)$val[0] : false;
    }

    /**
     * @param SimpleXMLElement $xml
     * @return String
     */
    private function getTitle(SimpleXMLElement $xml): String
    {
        foreach ($xml->xpath('/museScore/Score/Staff/VBox/Text') as $text) {
            if ($text[0]->style == 'Title') {
                return $text[0]->text;
            }
        }
    }

    /**
     * @param SimpleXMLElement $new_xml
     * @param string $en_title
     * @param string $jp_title
     * @return void
     */
    private function setTitle(SimpleXMLElement &$new_xml, string $en_title, string $jp_title): void
    {
        foreach ($new_xml->xpath('/museScore/Score/Staff/VBox/Text') as $text) {
            if ($text[0]->style == 'Title') { // set tiitle
                $text[0]->text .= PHP_EOL . $en_title . PHP_EOL . $jp_title;
            } else if ($text[0]->style == 'Subtitle') { // remove subtitle
                unset($text[0]);
            }
        }

        foreach ($new_xml->xpath('/museScore/Score/Staff/VBox/height') as $height) {
            $height[0] = 10.6;
        }
    }

    /**
     * @param SimpleXMLElement $new_xml
     * @return void
     */
    private function removeAllLyrics(SimpleXMLElement &$new_xml): void
    {
        foreach ($new_xml->xpath('/museScore/Score/Staff/Measure/voice/Chord/Lyrics') as $ol) {
            unset($ol[0]);
        }
    }

    /**
     * @param SimpleXMLElement $chord
     * @param SimpleXMLElement $xml
     * @param $lang
     * @param int $measure_num
     * @param $chord_num
     * @param int $verse_num
     * @param $lang_num
     * @return SimpleXMLElement
     */
    private function addLyricsTag(
        SimpleXMLElement &$chord,
        SimpleXMLElement $xml,
        string $lang,
        int $measure_num,
        int $chord_num,
        int $verse_num,
        $lang_num
    ): void {
        $lyric = $chord[0]->addChild('Lyrics');
        $syllabic = $this->getLyricChildValue($xml, $measure_num, $chord_num + 1, $verse_num,
            'syllabic');
        if ($syllabic) {
            $lyric->addChild('syllabic', $syllabic);
        }
        $text = $this->getLyricChildValue($xml, $measure_num, $chord_num + 1, $verse_num,
            'text');
        if ($text) {
            $lyric->addChild('text', $text);
        }

        // add lyric number
        if ($lang !== 'id') {
            $lyric->addChild('no', $lang_num);
        }
    }

    /**
     * @param string $zip_file_path
     */
    private function downloadZipFile(string $zip_file_path): void
    {
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zip_file_path) . '"');
        header('Content-Length: ' . filesize($zip_file_path));

        flush();
        readfile($zip_file_path);
        unlink($zip_file_path); // delete file
    }
}
