<?php

class itaDocumentMappingMaggioli implements itaDocumentMapping {

    private $missingVars = array();

    public function convert($text, $dictionary) {
        $this->missingVars = $matches = $sub_matches = array();

        preg_match_all('/\\\\(.*?)\\\\/', $text, $matches);
        $matches[1] = array_map('strip_tags', $matches[1]);

        foreach ($matches[1] as $k => $match) {
            if (strpos($match, ' ') !== false) {
                continue;
            }

            if (preg_match('/(scan|if)\(([\w:()]+)\)/', $match, $sub_matches)) {
                if (!isset($dictionary[strtoupper($sub_matches[2])])) {
                    $this->missingVars[] = $sub_matches[2];
                }

                $var = $dictionary[strtoupper($sub_matches[2])] ?: '$dummy';
                $key = str_replace('.', '_', trim($var, '$'));

                switch ($sub_matches[1]) {
                    case 'if':
                        $replace_with = '@{if ' . ($var ?: 'false') . '}@';
                        break;

                    case 'scan':
                        $replace_with = '@{foreach from=' . $var . ' key=' . $key . '_key item=' . $key . '_item}@';
                        break;
                }
            } elseif (preg_match('/(else|endif|endscan)/', $match)) {
                switch ($match) {
                    case 'else':
                        $replace_with = '@{else}@';
                        break;

                    case 'endif':
                        $replace_with = '@{/if}@';
                        break;

                    case 'endscan':
                        $replace_with = '@{/foreach}@';
                        break;
                }
            } else {
                if (!isset($dictionary[strtoupper($match)])) {
                    $this->missingVars[] = $match;
                }

                $var_content = $dictionary[strtoupper($match)];
                if (strpos($var_content, ' ') !== false) {
                    $var_content = str_replace(' ', '}@ @{', $var_content);
                }

                if (!$var_content) {
                    continue;
                }

                $replace_with = '@{' . $var_content . '}@';
            }

            $text = str_replace($matches[0][$k], $replace_with, $text);
        }

        return $text;
    }

    public function getMissingVars() {
        return $this->missingVars;
    }

}
