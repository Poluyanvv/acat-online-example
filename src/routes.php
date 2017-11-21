<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response) {
    $settings = Helper::getJSON($this->get('settings')['api']);
    if (
        !property_exists($settings,'token') ||
        property_exists($settings,'token') && strlen($settings->token) === 0
    ) {
        return $this->renderer->render($response, 'index.php', [
            'hrefPrefix' => $settings->urlBeforeCatalog,
            'error' => 'Укажите ваш API Token в файле src/settings.php:11'
        ]);
    }
    $types = Helper::getData($settings, false);

    return $this->renderer->render($response, 'index.php', [
        'hrefPrefix' => $settings->urlBeforeCatalog,
        'types' => $types
    ]);
});

// сюда поиск

$app->get('/{type}', function ($request, $response, $args) {
    return $response->withRedirect('/', 301);
});

$app->group('/{type:CARS_FOREIGN}/{mark:INFINITI|NISSAN}', function () {
    // страны и модели
    $this->get('[/{country:EL|ER|AR|GL|GR|CA|US|JP}]', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);
        $args['country'] = isset($args['country']) ? $args['country'] : 'EL';

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}");
        $data['models'] = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}")['models'];
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;
        $data['currentCountry'] = $args['country'];

        return $this->renderer->render($response, 'nissan/models.php', $data);
    });

    // модификации
    $this->get('/{country:EL|ER|AR|GL|GR|CA|US|JP}/{directory:[\d]{3}}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['directory']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'nissan/modifications.php', $data);
    });

    // группы
    $this->get('/{country:EL|ER|AR|GL|GR|CA|US|JP}/{directory:[\d]{3}}/{modification:[\d]+}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['directory']}/{$args['modification']}");
        $data['labels'] = [];
        $added = [];
        foreach ($data['groups'] as $group) {
            if ($group->coordinate) {
                $index = $group->group_short ? $group->group_short : $group->group_name;
                $coordinateIndex = $group->coordinate->bottom->x.$group->coordinate->bottom->y.$group->coordinate->top->x.$group->coordinate->top->y;
                if (!in_array($coordinateIndex, $added)) {
                    $added[] = $coordinateIndex;
                    $data['labels'][] = json_decode(json_encode([
                        'index' => $index,
                        'title' => $group->group_name,
                        'bottomX' => $group->coordinate->bottom->x,
                        'bottomY' => $group->coordinate->bottom->y,
                        'topX' => $group->coordinate->top->x,
                        'topY' => $group->coordinate->top->y
                    ]));
                }
            }
        }
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'nissan/groups.php', $data);
    });

    // подгруппы
    $this->get('/{country:EL|ER|AR|GL|GR|CA|US|JP}/{directory:[\d]{3}}/{modification:[\d]+}/{group}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['directory']}/{$args['modification']}/{$args['group']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'nissan/subgroups.php', $data);
    });

    // номера (артикулы) запчастей
    $this->get('/{country:EL|ER|AR|GL|GR|CA|US|JP}/{directory:[\d]{3}}/{modification:[\d]+}/{group}/{subgroup}[/{figure}/{section}]', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        if (!isset($args['figure'])) $urlParams = "/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['directory']}/{$args['modification']}/{$args['group']}/{$args['subgroup']}";
        else $urlParams = "/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['directory']}/{$args['modification']}/{$args['group']}/{$args['subgroup']}/{$args['figure']}/{$args['section']}";
        $data = Helper::getData($settings, true, $urlParams);
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;
//        dd2($data);
        return $this->renderer->render($response, 'nissan/numbers.php', $data);
    });
});

$app->group('/{type:CARS_FOREIGN}/{mark:RENAULT|DACIA}', function () {

    //models
    $this->get('', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'renault/models.php', $data);
    });

    //modifications
    $this->get('/{model}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['model']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'renault/modifications.php', $data);
    });

    //groups
    $this->get('/{model}/{modification}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['model']}/{$args['modification']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'renault/groups.php', $data);
    });

    //subgroups
    $this->get('/{model}/{modification}/{group}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['model']}/{$args['modification']}/{$args['group']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;
        $data['labels'] = [];
        $added = [];
        foreach ($data['unit']->groups as $group) {
            if ($group->coordinates && is_array($group->coordinates)) {
                foreach ($group->coordinates as $coordinate) {
                    $index = $group->name ? $group->name : '';
                    $coordinateIndex = $coordinate->bottom->x.$coordinate->bottom->y.$coordinate->top->x.$coordinate->top->y;
                    if (!in_array($coordinateIndex, $added)) {
                        $added[] = $coordinateIndex;
                        $data['labels'][] = json_decode(json_encode([
                            'index' => $index,
                            'title' => $index,
                            'bottomX' => $coordinate->bottom->x,
                            'bottomY' => $coordinate->bottom->y,
                            'topX' => $coordinate->top->x,
                            'topY' => $coordinate->top->y
                        ]));
                    }
                }
            }
        }


        return $this->renderer->render($response, 'renault/subgroups.php', $data);
    });

    //numbers
    $this->get('/{model}/{modification}/{group}/{subgroup}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['model']}/{$args['modification']}/{$args['group']}/{$args['subgroup']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;
        $data['labels'] = [];
        $added = [];
        foreach ($data['positions'] as $position) {
            if ($position->coordinates && is_array($position->coordinates)) {
                foreach ($position->coordinates as $coordinate) {
                    $coordinateIndex = $coordinate->bottom->x.$coordinate->bottom->y.$coordinate->top->x.$coordinate->top->y;
                    if (!in_array($coordinateIndex, $added)) {
                        $added[] = $coordinateIndex;
                        $data['labels'][] = json_decode(json_encode([
                            'index' => $position->index,
                            'title' => "{$position->name} ({$position->number})",
                            'bottomX' => $coordinate->bottom->x,
                            'bottomY' => $coordinate->bottom->y,
                            'topX' => $coordinate->top->x,
                            'topY' => $coordinate->top->y
                        ]));
                    }
                }
            }
        }

        $prev = $data['prev'];
        $next = $data['next'];
        $data['prevUrl'] = $prev ? "/{$settings->urlBeforeCatalog}{$prev->type}/{$prev->mark}/{$prev->model_short_name}/{$prev->modification_short_name}/{$prev->category_short_name}/{$prev->short_name}" : null;
        $data['nextUrl'] = $next ? "/{$settings->urlBeforeCatalog}{$next->type}/{$next->mark}/{$next->model_short_name}/{$next->modification_short_name}/{$next->category_short_name}/{$next->short_name}" : null;
        $data['title'] = $data['breadcrumbs'][6]->name;

        return $this->renderer->render($response, 'renault/numbers.php', $data);
    });

    //subNumbers
    $this->get('/{model}/{modification}/{group}/{subgroup}/{subNumber}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['model']}/{$args['modification']}/{$args['group']}/{$args['subgroup']}/{$args['subNumber']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;
        $data['labels'] = [];
        $added = [];
        if ($data['positions'] && is_array($data['positions']) && count($data['positions']) > 0) {
            foreach ($data['positions'] as $position) {
                if ($position->coordinates && is_array($position->coordinates)) {
                    foreach ($position->coordinates as $coordinate) {
                        $coordinateIndex = $coordinate->bottom->x.$coordinate->bottom->y.$coordinate->top->x.$coordinate->top->y;
                        if (!in_array($coordinateIndex, $added)) {
                            $added[] = $coordinateIndex;
                            $data['labels'][] = json_decode(json_encode([
                                'index' => $position->index,
                                'title' => "{$position->name} ({$position->number})",
                                'bottomX' => $coordinate->bottom->x,
                                'bottomY' => $coordinate->bottom->y,
                                'topX' => $coordinate->top->x,
                                'topY' => $coordinate->top->y
                            ]));
                        }
                    }
                }
            }
        }

        $prev = $data['prev'];
        $next = $data['next'];
        $data['prevUrl'] = $prev ? "/{$settings->urlBeforeCatalog}{$prev->type}/{$prev->mark}/{$prev->model_short_name}/{$prev->modification_short_name}/{$prev->category_short_name}/{$prev->short_name}" : null;
        $data['nextUrl'] = $next ? "/{$settings->urlBeforeCatalog}{$next->type}/{$next->mark}/{$next->model_short_name}/{$next->modification_short_name}/{$next->category_short_name}/{$next->short_name}" : null;
        $data['title'] = $data['breadcrumbs'][6]->name;
        unset($data['parent']->breadcrumbs);

        return $this->renderer->render($response, 'renault/subnumbers.php', $data);
    });

});

$app->group('/{type:CARS_FOREIGN|BUS|SPECIAL_TECH_FOREIGN|ENGINE|TRUCKS_FOREIGN}/{mark:MERCEDES_BENZ|SMART|MERCEDES_BENZ_PS}', function () {

    //countries
    $this->get('', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'mercedes/countries.php', $data);
    });

    //models
    $this->get('/{country}/{aggregation}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['aggregation']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'mercedes/models.php', $data);
    });

    //groups
    $this->get('/{country}/{aggregation}/{model}/{catalog}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['aggregation']}/{$args['model']}/{$args['catalog']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'mercedes/groups.php', $data);
    });

    //numbers
    $this->get('/{country}/{aggregation}/{model}/{catalog}/{group}/{subgroup}[/{position}]', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['aggregation']}/{$args['model']}/{$args['catalog']}/{$args['group']}/{$args['subgroup']}".($args['position'] ? "/{$args['position']}" : ''));
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'mercedes/numbers.php', $data);
    });

    //sa numbers
    $this->get('/{country}/{aggregation}/{model}/{catalog}/{group}/{subgroup}/{sa}/{stroke}[/{position}]', function ($request, $response, $args) {
        $this->get('/{country}/{aggregation}/{model}/{catalog}/{group}/{subgroup}[/{position}]', function ($request, $response, $args) {
            $settings = Helper::getJSON($this->get('settings')['api']);

            $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['country']}/{$args['aggregation']}/{$args['model']}/{$args['catalog']}/{$args['group']}/{$args['subgroup']}/{$args['sa']}/{$args['stroke']}".($args['position'] ? "/{$args['position']}" : ''));
            $data['hrefPrefix'] = $settings->urlBeforeCatalog;

            return $this->renderer->render($response, 'mercedes/numbers.php', $data);
        });
    });

});

$app->group('/{type:CARS_FOREIGN|CARS_NATIVE|TRUCKS_NATIVE|TRUCKS_FOREIGN|BUS|SPECIAL_TECH_NATIVE|SPECIAL_TECH_FOREIGN|AGRICULTURAL_MACHINERY|TRACTOR|ENGINE|MOTORCYCLE}/{mark:GAZ_ENGINE|ANDORIA_ENGINE|AVIA|BALKANCAR_ENGINE|BALKANCAR|BAW|BEARFORD_ENGINE|BEIFANG_BENCHI|BYD|CAMC|CASE|CATERPILLAR|CF_MOTO|CHENGGONG|CHEVROLET|CHEVROLET_SKD|CHRYSLER_ENGINE|CITROEN_SKD|CNHTC_SINOTRUK|CNHTC_SINOTRUK_ARCHIVE|CUMMINS_ENGINE|CUMMINS_ENGINE|DAEWOO|DAEWOO_SKD|DAEWOO_ARCHIVE|DAF|DERWAYS|DETVA|DEUTZ_ENGINE|DONGFENG_ENGINE|DONGFENG|DONGFENG|DOOSAN|EAGLE-WING|FAW_ARCHIVE|FAW|FORD_SKD|FORTSCHRITT|FOTON|FOTON|GEELY|GOLDEN_DRAGON|HAFEI|HANWOO|HBXG|HIDROMEK_ARCHIVE|HIDROMEK|HIGER|HONDA_SKD|HONDA_SKD|HONEY_BEE|IKARUS|IRAN_KHODRO|ISUZU_ENGINE|ISUZU_SKD|IVECO_ENGINE|IVECO|JAC|JAGUAR_SKD|JAWA|JCB|JEEP_SKD|JIANSHE|JMC|KING_LONG|KOMAT\'SU_ENGINE|KOMAT\'SU|LAND_ROVER_SKD|LIFAN|LIUGONG|LOCUST|MACDON|MADARA|MADARA|MAHINDRA|MAN_SKD|MAN|MAZDA_SKD|MECANICA_CEAHLAU|MERCEDES_BENZ_SKD|MERCEDES_BENZ_SKD|METAL-FACH_ARCHIVE|METAL-FACH|MITSUBER|MITSUBISHI_SKD|NEW_HOLLAND|NISSAN_ENGINE|OPEL_SKD|PEUGEOT_SKD|PORSCHE_SKD|RENAULT_SKD|RENAULT|ROVER_SKD|SAAB|SAAB_SKD|SARKANA_ZVAIGZNE|SCANIA_SKD|SDLG|SHAANXI|SHAANXI_FAST_GEAR|SHANTUI|SSANGYONG_SKD|SUBARU_SKD|SUZUKI|SUZUKI_SKD|SUZUKI_SKD|TATA|TATRA|TEREX|THERMO_KING|TIGARBO|TRACOM|VERSATILE|VOLVO|VOLVO|VOLVO_SKD|VOLVO_SKD|XCMG|XIAMEN|YANMAR_ENGINE|YUCHAI_ENGINE|YUTONG_ARCHIVE|YUTONG|ZETOR_ENGINE|ZF|ZONGSHEN|AVTOKRAN|AGRO|AZLK|ALTAJ|AMZ_ENGINE|AMKODOR_ARCHIVE|AMKODOR|ATZ|BARNAULTRANSMASH_ARCHIVE_ENGINE|BARNAULTRANSMASH_ENGINE|BELAZ_ARCHIVE|BELAZ|BELAZ|BELOVEZH_ARCHIVE|BELOVEZH|BELOCERKOVMAZ|BOBRUJSKAGROMASH_ARCHIVE|BOBRUJSKAGROMASH|BOBRUJSKSELMASH|BOGDAN|BRYANSKIJ_ARSENAL_ARCHIVE|BRYANSKIJ_ARSENAL|BRYANSKIJ_ARSENAL|BEHMZ|VAZ_ARCHIVE|VAZ|VGTZ|VSM|VTZ_ARCHIVE_ENGINE|VTZ_ENGINE|VTZ|VEHKS|GAZ_ARCHIVE|GAZ|GAZ_ARCHIVE|GAZ|GAZPROM-KRAN|GAKZ|GEOMASH|GIDROMASH|GOMSELMASH|GOMSELMASH_ARCHIVE|DKZ|DONEHKS|DORMASH|ELAZ|ZAZ_ARCHIVE|ZAZ|ZZGT_ARCHIVE|ZZGT|ZID|ZID|ZIK|ZIL|ZIL_ARCHIVE|ZIL_ARCHIVE|ZIL|ZLATEHKS|ZMZ_ARCHIVE_ENGINE|ZMZ_ENGINE|IZH|IZH|IZH_ARCHIVE|IZHNEFTEMASH|IZHORSKIE_ZAVODY|IMZ|KAVZ|KAZ|KAMAZ_ENGINE|KAMAZ_ARCHIVE|KAMAZ_ARCHIVE|KAMAZ_ARCHIVE_ENGINE|KAMAZ|KAMAZ|KANASH|KZK_ARCHIVE|KZK|KZKT|KLEVER_ARCHIVE|KLEVER|KMZ|KMZ_1_MAYA|KOVROVEC|KRAZ_ARCHIVE|KRAZ|KREDMASH|KEHZ|LAZ|LZA|LIAZ|LIAZ_ARCHIVE|LIDAGROPROMMASH|LIDSELMASH|LTZ|LUAZ|MAZ_ARCHIVE|MAZ|MAZ|MAZ_ARCHIVE|MAZ|MASHTEKHREMONT|MZKM_ARCHIVE|MZKM|MZKT|MMZ|MMZ_ARCHIVE_ENGINE|MMZ_ENGINE|MOAZ|MOAZ|MOAZ_ARCHIVE|MOLDAGROTEKHNIKA|MRMZ|MTZ_ARCHIVE|MTZ|MTM|MTM|NEFAZ_ARCHIVE|NEFAZ_ARCHIVE|NEFAZ|NEFAZ|NEFAZ|NKMZ|OREL-POGRUZCHIK|OSTA|OTZ|PAZ|PAZ_ARCHIVE|PENZADIZELMASH_ENGINE|PROMTRAKTOR|PTZ|RASKAT|RAF|RMZ|ROSTSELMASH_ARCHIVE|ROSTSELMASH|RUSSKAYA_MEKHANIKA|RUSSKAYA_MEKHANIKA_ARCHIVE|SAZ|SALSKSELMASH|SAREHKS|SZAP_ARCHIVE|SZAP|SIBSELMASH|SINERGIYA|SMD_ENGINE|STROJDORMASH|STROMNEFTEMASH|TVEHKS_ARCHIVE|TVEHKS|TEPLOSTAR_ENGINE|TZA|TKZ|TMZ_ARCHIVE_ENGINE|TMZ_ENGINE|TMZ|TONAR_ARCHIVE|TONAR|TORFMASH|UAZ_ARCHIVE|UAZ|UVZ|UVZ_ARCHIVE|UMZ_ARCHIVE_ENGINE|UMZ_ENGINE|UMZ_-2|UMPO|UMPO|UNISIBMASH|URAL|URAL_ARCHIVE|URAL|URALMASH|HZTSSH|HTZ_ARCHIVE|HTZ|CHERVONA_ZIRKA|CHZTS|CHMZ|CHSDM|CHTZ|SHAAZ|EHKSKO|EHKSMASH|EHLTRA_ENGINE|YUMZ|YURMASH|YAMZ_ARCHIVE_ENGINE|YAMZ_ENGINE}', function () {

    // модели
    $this->get('', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'a2d/models.php', $data);
    });

    // группы
    $this->get('/{modelId}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['modelId']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'a2d/groups.php', $data);
    });

    // номера (артикулы) запчастей
    $this->get('/{modelId}/{groupId}', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getData($settings, true,"/{$args['type']}/{$args['mark']}/{$args['modelId']}/{$args['groupId']}");
        $data['hrefPrefix'] = $settings->urlBeforeCatalog;

        return $this->renderer->render($response, 'a2d/numbers.php', $data);
    });

    //изображение
    $this->get('/{modelId}/{groupId}/image', function ($request, $response, $args) {
        $settings = Helper::getJSON($this->get('settings')['api']);

        $data = Helper::getImage($settings, "/{$args['type']}/{$args['mark']}/{$args['modelId']}/{$args['groupId']}/image");
        $response->write($data);

        return $response->withHeader('Content-Type', FILEINFO_MIME_TYPE);
    });
});




//$app->group('/{type}/{mark}', function() {
////    $app->group('/{type:(CARS_FOREIGN|CARS_NATIVE|TRUCKS_NATIVE|TRUCKS_FOREIGN|BUS|SPECIAL_TECH_NATIVE|SPECIAL_TECH_FOREIGN|AGRICULTURAL_MACHINERY|TRACTOR|ENGINE|MOTORCYCLE)}', function() {
//        $this->get('', function ($request, $response, $args) {
//            return $this->renderer->render($response, 'example.php', $args);
//        });
////    });
//});
//$newResponse = $response->withHeader('Content-type', 'image/png');
//$newResponse->withStatus(404, 'Image not found');
//$request->getAttribute('host');