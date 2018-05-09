<?php

class parseXml{
    private $content = '';
    public __construct(){
    }

    /**
     * 返回 array
     */
    public parse($content){
        return $this->xml_to_array($content);
    }

    private function xml_to_array($xml)
    {

        $contents = trim(convert_xml_encode($xml));
        if (empty($contents)) {

            return array();
        }

        $parser = xml_parser_create();
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, 'UTF-8');
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        if (xml_parse_into_struct($parser, $contents, $xml_values) == 0 || !$xml_values) {

            xml_parser_free($parser);
            return array();
        }
        xml_parser_free($parser);
        unset($contents, $parser);
        $response = $this->hhvm_xml_to_array_sub($xml_values, 0);
        return $response[2];
    }

    private function xml_to_array_sub($xml_values, $iterator = 0)
    {

        $first_node = $xml_values[$iterator];
        $tag = $first_node['tag'];
        $level = $first_node['level'];

        if ($first_node['type'] == 'complete') {

            return [$iterator + 1, $tag, $first_node['value'] ?? null, $first_node['attributes'] ?? null];
        }

        $result = [];
        $iterator = $iterator + 1;
        while ($iterator < count($xml_values)) {

            $node = $xml_values[$iterator];
            if ($node['level'] == $level) {

                break;
            }

            list($iterator, $key, $value, $attributes) = $this->xml_to_array_sub($xml_values, $iterator);
            if (!isset($result[$key])) {

                $result[$key] = $value;
            } elseif (is_array($result[$key]) && isset($result[$key][0])) {

                $result[$key][] = $value;
            } else {

                $before = $result[$key];
                unset($result[$key]);
                $result[$key] = [$before, $value];
            }

            if ($attributes) {

                $result['@' . $key] = $attributes;
            }
        }

        return [$iterator + 1, $tag, $result, $first_node['attributes'] ?? null];
    }
}
