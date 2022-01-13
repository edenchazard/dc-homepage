<?php
const CACHE_PATH = "cache";
const CACHE_TIME = 24; // cache for 24 hours
const SALT = "il+vecats";

function in_cache($filename) {
    // check that cache file exists and is not too old
    if(!file_exists($filename)){
        return false;
    }

    if(filemtime($filename) < time() - CACHE_TIME * 3600) return false;

    return true;
}

function retrieve_from_cache($filename){
    readfile($filename);
    exit;
}

function save_to_cache($filename, $data){
    return file_put_contents($filename, $data);
}

function get_breed_data(string $data_file){
    $has_dimorphism = function($line){
        return (strlen($line[5]) > 0);
    };

    $get_attributes = function($line, $gender){
        // keep default offset for males
        // and fix it for females
        // male (or default singular) data is columns 1-4
        // female data is 5-8
        $offset = ($gender == 0 ? 1 : 5);
        $data = new stdClass;
        $data->code = $line[$offset];
        $data->x = $line[$offset+1];
        $data->y = $line[$offset+2];
        $data->h = $line[$offset+3];

        return $data;
    };

    $arr = array();

    $file = fopen($data_file, 'r');
    // loop through lines
    while(!feof($file)) {
        $line = fgetcsv($file);
        // ignore empty lines
        if(isset($line[3])){
            $breed_name = $line[0];

            // skip header column
            if($breed_name == 'breed'){
                continue;
            }

            // dimorphism means there is a female
            if($has_dimorphism($line)){
                $breed_data = [
                    'has_dimorphism' => true,
                    "m" => $get_attributes($line, 0),
                    "f" => $get_attributes($line, 1)
                ];
            }
            // no dimo, just return the 'male' as default
            else{
                $breed_data = [
                    'has_dimorphism' => false,
                    's' => $get_attributes($line, 0)
                ];
            }

            $arr[$breed_name] = $breed_data;
        }
    }
    fclose($file);

    // dragcave time for nocturnes and others
    $datetime = new DateTime();
    $datetime->setTimezone(new DateTimeZone('America/New_York'));
    //Edit the nocturne's position if it is currently counted as daytime
    $hour = intval($datetime->format('H'));
    if($hour >= 6 && $hour <= 17){
        $arr['Nocturne']['m']->x = 0;
        $arr['Nocturne']['m']->y = -1;
        $arr['Nocturne']['f']->x = 0;
        $arr['Nocturne']['f']->y = -1;
    }
    return $arr;
}

function parse_dragons(string $csv_file){
    $arr = array();
    /*
    ---FORMAT---
        * bronze tin
            * code : type : mate : mate_gender
    */
    $file = fopen($csv_file, 'r');
    // loop through lines
    while(!feof($file)) {
        $line = fgetcsv($file);
        // ignore empty lines and headers
        if(isset($line[3]) && $line[0] !== 'code'){
            list($code, $gen, $prize_colour, $mate, $type, $mate_gender, $notes) = $line;
            $dragon = new stdClass;
            $dragon->code = $code;
            $dragon->gen = $gen;
            $dragon->prize_colour = $prize_colour;
            $dragon->type = $type;
            $dragon->mate = $mate;
            $dragon->mate_gender = $mate_gender;
            $arr[] = $dragon;
        }
    }
    fclose($file);
    return $arr;
}

function sort_into_prize_groups(array $dragons){
    $arr = array_flip(array('Tinsel (Gold)', 'Tinsel (Silver)', 'Tinsel (Bronze)', 'Tinsel (Penk)',
        'Shimmer-Scale (Gold)', 'Shimmer-Scale (Silver)', 'Shimmer-Scale (Bronze)', 'Shimmer-Scale (Jewel)'
    ));

    foreach ($arr as $key => $value){
        $arr[$key] = array(
            'prize' => $key,
            'dragons' => []
        );
    }
    foreach($dragons as $dragon){
        $arr[$dragon->prize_colour]['dragons'][] = $dragon;
    }

    // remove empties
    /*foreach($arr as $key => $value){
        if(!isset($value['dragons'][0])){
            unset($arr[$key]);
        }
    }*/

    return $arr;
}
?>