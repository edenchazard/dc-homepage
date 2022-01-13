<?php
require_once __DIR__ . '/vendor/autoload.php';
require("data/functions.php");
const APP_PATH = "/dc";

$klein = new \Klein\Klein();
$request = \Klein\Request::createFromGlobals();

// Grab the server-passed "REQUEST_URI"
$uri = $request->server()->get('REQUEST_URI');
// Set the request URI to a modified one (without the "subdirectory") in it
$request->server()->set('REQUEST_URI', substr($uri, strlen(APP_PATH)));

$klein->respond(function ($request, $response, $service, $app) use ($klein) {
    $app->register('twig', function () {
        $loader = new \Twig\Loader\FilesystemLoader("views");
        return new \Twig\Environment($loader);
    });
});

$klein->respond('/', function ($request, $response, $service, $app) {
    return $app->twig->render('home.twig');
});

$klein->respond('/prizes' /*/[:format]?'*/, function ($request, $response, $service, $app) {
    $filter = $request->param('filter', '');
    //$format = $request->param('format', 'new');

    $dragon_breeds = get_breed_data('data/breed-data.csv');
    $dragons = parse_dragons('data/prize-lines.csv');

    // has to be done before filtering. smart moves, chaz
    $unique_breeds = array_unique(array_column($dragons, 'mate'));
    sort($unique_breeds);

    // Apply filter
    if($filter !== ''){
        // validate valid breed
        if(isset($dragon_breeds[$filter])){
            $dragons = array_filter($dragons, function($var) use ($filter){ return $var->mate === $filter;});
        }
    }


    $data = array();
    $gens = array_unique(array_column($dragons, 'gen'));
    foreach($gens as $gen){
        $dragons_in_gen = array_filter($dragons, function($dragon) use ($gen){ return $dragon->gen === $gen; });

        $data[] = [
            'gen' => $gen,
            'prizes' => sort_into_prize_groups($dragons_in_gen)
        ];
    };

    foreach($data as &$gen){
        foreach($gen['prizes'] as &$prize_colour){
            $prize_colour['sprite'] = $dragon_breeds[$prize_colour['prize']]['s'];

            foreach($prize_colour['dragons'] as &$dragon){
                $breed_name = $dragon->mate;
                $breed = $dragon_breeds[$breed_name];
                $mate_details = $breed[$breed['has_dimorphism'] ? $dragon->mate_gender : 's'];
                $mate_details->breed = $dragon->mate;
                $dragon->mate = $mate_details;
                //$dragon->mate_gender = ($dragon->mate_gender === 'f' ? '&#9792;' : '&#9794;');
            }
        }
    }

   /* if($format == 'old'){
        $template = 'prizes-old.twig';
    }
    else{*/
        $template = 'prizes.twig';
    //}

    return $app->twig->render($template, array(
        'data' => $data,
        'filter_crit' => $filter,
        'unique_breeds' => $unique_breeds
    ));
});

$klein->respond('/trades', function ($request, $response, $service, $app) {
    return $app->twig->render('trading.twig', array("updated" => filemtime('views/trading.twig')));
});
/*$klein->respond('/thuweds', function ($request, $response, $service, $app) {
});

$klein->respond('/lineage-builder', function ($request, $response, $service, $app){
    return $service->render('lineage-builder/index.html');
});
*/
/*
$klein->respond('GET', '/lineage-builder/sprites.json', function ($request, $response, $service, $app) {
    $filename = CACHE_PATH."/".md5('test'.SALT.".json");

    if(!in_cache($filename)){
        $data = json_encode(get_breed_data('data/breed-data.csv'));
        else if($format == "csv"){
            $data = file_get_contents('../breed-data.csv');
        }
        save_to_cache($filename, $data);
    }

    //header('Content-type: '.$mimetype);
    $response->json(retrieve_from_cache($filename));
    
    $response->json(json_encode(get_breed_data('data/breed-data.csv')));
});*/
/*

$klein->respond('@^/lineage/', function ($request, $response, $service, $app){
    //echo 
    $file = "lineage-builder/".substr($request->uri(), strlen('lineage/'));
    $service->render($file);
    return $response->file($file);
});
*/

$klein->onHttpError(function ($code, $router){
    switch ($code) {
        case 404: $message = "This page does not exist."; break;
        case 403: $message = "Unauthorized."; break;
		case 500: $message = "Internal server error."; break;
        default: $message = "HTTP Error {$code}";
    }
    echo $router->app()->twig->render('error.twig', ['code' => $code, 'message' => $message]);
});

// Pass our request to our dispatch method
$klein->dispatch($request);
?>