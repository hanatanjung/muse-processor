<?php namespace App\Controllers;

use SimpleXMLElement;
use ZipArchive;

class Combine extends BaseController
{
    protected $helpers = ['url', 'form', 'xml'];
    private $isWIndows = false;

    public function index()
    {
        return view('combine');
    }

    public function upload()
    {
        $langs = ['id', 'jp', 'en'];
        $filename = $this->request->getVar('filename');

        // determine user's OS
        if(strpos(getenv("HTTP_USER_AGENT"), "Win") !== FALSE) {
            $this->isWIndows = true;
        }

        // get xmls
        $xmls = [];
        foreach ($langs as $lang) {
            $input_data = $this->request->getFile('muse.' . $lang);
            if ($input_data->isFile()) {
                $fetchedXML = new SimpleXMLElement(file_get_contents($input_data->getTempName()));
                $xmls[$lang] = $this->normalize($fetchedXML);
            }
        }

        // get total verses
        $score_infos = [];
        foreach ($xmls as $lang => $xml) {
            $score_infos[$lang] = $this->getScoreInfo($xml);
        }

        // get jp and en title
        $en_title = isset($xmls['en']) ? $this->getTitle($xmls['en']) : '';
        $jp_title = isset($xmls['en']) ? $this->getTitle($xmls['jp']) : '';

        // prepare for zipping
        $zip = new ZipArchive();
        $zip_file_path = WRITEPATH . 'generated/' . $filename . '.zip';
        $zip->open($zip_file_path, ZipArchive::CREATE);

        // copy lyrics
        for ($verse_num = 1; $verse_num <= $score_infos['id']['total_verses']; $verse_num++) {
            $new_xml = new SimpleXMLElement($xmls['id']->saveXML());

            // change title & remove subtitle
            $this->setTitle($new_xml, $en_title, $jp_title);

            // remove all lyrics
            $this->cleanScore($new_xml);

            for($measure_num = 1; $measure_num <= $score_infos['id']['total_measures']; $measure_num++) {
                foreach ($new_xml->xpath('/museScore/Score/Staff/Measure[' . $measure_num . ']/voice/Chord') as $chord_num => $chord) {
                    // add Lyric
                    foreach ($langs as $lang_num => $lang) {
                        if (isset ($xmls[$lang])) {
                            $this->addLyricsTag($chord, $xmls[$lang], $lang, $measure_num, $chord_num + 1, $verse_num, $lang_num, $score_infos[$lang]);
                        }
                    }
                }
            }

            $name = $filename . '_' . $verse_num . '.mscx';
            $zip->addFromString($name, $new_xml->saveXML());
        }

        $zip->close();
        $this->downloadZipFile($zip_file_path);
    }

    /**
     * Normalize score
     * @param SimpleXMLElement $fetchedXml
     * @return SimpleXMLElement
     */
    private function normalize(SimpleXMLElement $fetchedXml): SimpleXMLElement
    {
        // Remove empty lyric tags
        foreach ($fetchedXml->xpath('/museScore/Score/Staff/Measure/voice/Chord/Lyrics') as $lyric) {
            if($lyric->text == '') {
                unset($lyric[0]);
            }
        }

        // Set page scale (to make score big)
        foreach ($fetchedXml->xpath('/museScore/Score/Style/Spatium') as $node) {
            $node[0] = 2.600;
        }
        foreach ($fetchedXml->xpath('/museScore/Score/Style/titleFontSize') as $node) {
            $node[0] = 20;
        }

        return $fetchedXml;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param $measure_num
     * @param $chord_num
     * @param $verse_num
     * @return bool|SimpleXMLElement
     */
    private function getLyricChildValue(SimpleXMLElement $xml, $measure_num, $chord_num, $verse_num)
    {
        $lyrics = $xml->xpath('/museScore/Score/Staff/Measure[' . $measure_num . ']/voice/Chord[' . $chord_num . ']/Lyrics');
        if (count($lyrics) > 0) {
            foreach ($lyrics as $lyric) {
                if (($verse_num == 1 && !isset($lyric->no) || (intval($lyric->no) + 1 == $verse_num))) {
                    return $lyric;
                }
            }
        }

        return false;
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
                if (!empty($jp_title)) {
                    $text[0]->text .= PHP_EOL . $jp_title;
                }
                if (!empty($en_title)) {
                    $text[0]->text .= PHP_EOL . $en_title;
                }
            } else if ($text[0]->style == 'Subtitle') { // remove subtitle
                unset($text[0]);
            }
        }

        // set height of VBox
        foreach ($new_xml->xpath('/museScore/Score/Staff/VBox/height') as $height) {
            $height[0] = 10.6;
        }
    }

    /**
     * @param SimpleXMLElement $new_xml
     * @return void
     */
    private function cleanScore(SimpleXMLElement &$new_xml): void
    {
        // remove all lyric
        foreach ($new_xml->xpath('/museScore/Score/Staff/Measure/voice/Chord/Lyrics') as $ol) {
            unset($ol[0]);
        }

        // remove vspacerFixed
        foreach ($new_xml->xpath('/museScore/Score/Staff/Measure/vspacerFixed') as $tape) {
            unset($tape[0]);
        }
    }

    /**
     * Add lyric per syllable
     * @param SimpleXMLElement $chord
     * @param SimpleXMLElement $xml
     * @param string $lang
     * @param int $measure_num
     * @param int $chord_num
     * @param int $verse_num
     * @param int $lang_num
     * @param array $score_info
     * @return void
     */
    private function addLyricsTag(
        SimpleXMLElement &$chord,
        SimpleXMLElement $xml,
        string $lang,
        int $measure_num,
        int $chord_num,
        int $verse_num,
        int $lang_num,
        array $score_info
    ): void {
        $lyric = $chord[0]->addChild('Lyrics');

        // check if single refrain
        if ($score_info['measure_reff_start'] > 0 && $score_info['chord_reff_start'] > 0) {
            if ($measure_num > $score_info['measure_reff_start'] || ($measure_num == $score_info['measure_reff_start'] && $chord_num >= $score_info['chord_reff_start'])) {
                $verse_num = 1;
            }
        }

        // get text lyric
        $oriLyric = $this->getLyricChildValue($xml, $measure_num, $chord_num, $verse_num);

        if ($oriLyric) {
            // get syllabic of lyric
            $tag_names = ['syllabic', 'text', 'ticks', 'ticks_f'];
            foreach ($tag_names as $tag_name) {
                if ($oriLyric->{$tag_name}) {
                    $lyric->addChild($tag_name, $oriLyric->{$tag_name});
                }
            }

            // add lyric number
            if ($lang !== 'id') {
                $lyric->addChild('no', $lang_num);
            }

            // set font to Yu Mincho for Japanese
            if ($this->isWIndows && $lang == 'jp') {
                $lyric->addChild('family', 'Yu Mincho Demibold');
                $offset = $lyric->addChild('offset');
                $offset->addAttribute('x', '0');
                $offset->addAttribute('y', '2');
            }
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

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    private function getScoreInfo(SimpleXMLElement $xml): array
    {
        $measures = $xml->xpath('/museScore/Score/Staff/Measure');
        $score_info = [
            'total_verses' => 0,
            'total_measures' => count($measures),
            'measure_reff_start' => 0,
            'chord_reff_start' => 0
        ];

        foreach ($measures as $m_index => $measure) {
            $c_index = 1;
            foreach ($measure->voice->children() as $c) {
                if (count($c->Lyrics) == 1 && $score_info['measure_reff_start'] == 0) {
                    $score_info['measure_reff_start'] = $m_index + 1;
                    $score_info['chord_reff_start'] = $c_index;
                } else {
                    if (count($c->Lyrics) > 1) {
                        $score_info['measure_reff_start'] = 0;
                        $score_info['chord_reff_start'] = 0;
                    }
                }

                // get the largest number of lyrics
                if ($score_info['total_verses'] < count($c->Lyrics)) {
                    $score_info['total_verses'] = count($c->Lyrics);
                }

                $c_index++;
            }
        }

        return $score_info;
    }
}
