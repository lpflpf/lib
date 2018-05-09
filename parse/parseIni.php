<?php

class ParseIni{

    private $fileContent;
    private $data = [];

    public function __construct($fileContent){
        $this->fileContent = $fileContent;
    }

    public function getData(){
        if (empty($data)){
            $this->parse();
        }
        return $this->data;
    }

    private function parse(){
        $parent = '';
        foreach(explode("\n",$this->fileContent) as $row){
            $row = trim($row);
            if (empty($row)){
                continue;
            }
            $first = substr($row, 0, 1);

            if ($first === '#' || $first === ';'){
                continue;
            }

            if ($first === '['){
                $end = substr($row, -1);
                $parent = $this->strip($row);
                $this->data[$parent] = [];
                continue;
            }
            list($key, $value) = explode("=", $row,2);
            $value = $this->strip($value);

            $key = trim($key);
            if (substr($key, -1) === ']'){
                list($key, $subKey) = explode('[', $key);
                $subKey = $this->strip($subKey);
                if ($parent === ''){
                    $this->data[$key][$subKey] = $this->setValue($value);
                }else{
                    $this->data[$parent][$key][$subKey] = $this->setValue($value);
                }
                continue;
            }

            if ($parent === ''){
                $this->data[$key] = $this->setValue($value);
            }else{
                $this->data[$parent][$key] = $this->setValue($value);
            }
        }
    }

    private function strip($data){

        return trim($data, ';[]\'" ');
    }

    private function setValue($value){

        if ('true' == $value){
            return "1";}
        if ('false' == $value){
            return '';}
        return $value;
    }
}
