<?php

namespace Genealogy\App\Routing;

class Router
{
    // *** REMARK: be aware of the order! Path "tree_index" must be used before "index" ***
    public $routes_array = [
        // *** Must be before address ***
        ['path' => 'addresses', 'title' => 'Addresses', 'page' => 'addresses', 'vars' => 'select_tree_id'],
        ['path' => 'address', 'title' => 'Address', 'page' => 'address', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_report_pdf', 'title' => 'Ancestor report', 'page' => 'ancestor_report_pdf', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_report_rtf', 'title' => 'Ancestor report', 'page' => 'ancestor_report_rtf', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_report', 'title' => 'Ancestor report', 'page' => 'ancestor_report', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_chart', 'title' => 'Ancestor chart', 'page' => 'ancestor_chart', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_sheet_pdf', 'title' => 'Ancestor sheet', 'page' => 'ancestor_sheet_pdf', 'vars' => 'select_tree_id,id'],
        ['path' => 'ancestor_sheet', 'title' => 'Ancestor sheet', 'page' => 'ancestor_sheet', 'vars' => 'select_tree_id,id'],
        ['path' => 'anniversary', 'title' => 'Birthday calendar', 'page' => 'anniversary'],
        ['path' => 'chat_genealogy_api', 'title' => 'Chat Genealogy API', 'page' => 'chat_genealogy_api'],
        ['path' => 'chat_genealogy', 'title' => 'Chat Genealogy', 'page' => 'chat_genealogy'],
        ['path' => 'cms_pages', 'title' => 'Information', 'page' => 'cms_pages', 'vars' => 'id'],
        ['path' => 'cookies', 'title' => 'Cookie information', 'page' => 'cookies'],
        ['path' => 'close_relatives', 'title' => 'Close Relatives', 'page' => 'close_relatives', 'vars' => 'select_tree_id,id'],
        ['path' => 'descendant_report', 'title' => 'Descendants', 'page' => 'family', 'vars' => 'select_tree_id,id'],
        ['path' => 'descendant_chart', 'title' => 'Descendants', 'page' => 'descendant_chart', 'vars' => 'select_tree_id,id'],
        // *** Must be before family ***
        ['path' => 'family_pdf', 'title' => 'Family Page', 'page' => 'family_pdf', 'vars' => 'select_tree_id,id'],
        ['path' => 'family_rtf', 'title' => 'Family Page', 'page' => 'family_rtf'],
        ['path' => 'family', 'title' => 'Family Page', 'page' => 'family', 'vars' => 'select_tree_id,id'],
        ['path' => 'fanchart', 'title' => 'Fanchart', 'page' => 'fanchart', 'vars' => 'select_tree_id,id'],
        ['path' => 'help', 'title' => 'Help', 'page' => 'help'],
        ['path' => 'hourglass', 'title' => 'Hourglass', 'page' => 'hourglass', 'vars' => 'select_tree_id,id'],
        // *** Must be before index ***
        ['path' => 'tree_index', 'title' => 'Family tree index', 'page' => 'tree_index', 'vars' => 'select_tree_id'],
        ['path' => 'index', 'title' => 'Main index', 'page' => 'index'],
        ['path' => 'latest_changes', 'title' => 'Latest changes', 'page' => 'latest_changes'],

        // *** Must be before places and before list (because of list in link) ***
        ['path' => 'list_places_families', 'title' => 'Places', 'page' => 'list_places_families'],
        // *** Must be before list***
        // ['path' => 'places', 'title' => 'Places', 'page' => 'places'],
        // *** Must be before list ***

        ['path' => 'list_names', 'title' => 'Names', 'page' => 'list_names', 'vars' => 'select_tree_id,last_name'],
        ['path' => 'list', 'title' => 'Persons', 'page' => 'list'],
        ['path' => 'login', 'title' => 'Login', 'page' => 'login'],
        ['path' => 'mailform', 'title' => 'Mail form', 'page' => 'mailform'],
        ['path' => 'maps', 'title' => 'World map', 'page' => 'maps'],
        ['path' => 'outline_report_pdf', 'title' => 'Outline Report', 'page' => 'outline_report_pdf'],
        ['path' => 'outline_report', 'title' => 'Outline Report', 'page' => 'outline_report'],
        ['path' => 'photoalbum', 'title' => 'Photobook', 'page' => 'photoalbum', 'vars' => 'select_tree_id'],
        ['path' => 'register', 'title' => 'Register', 'page' => 'register'],
        ['path' => 'relations', 'title' => 'Relationship calculator', 'page' => 'relations'],
        ['path' => 'reset_password', 'title' => 'Reset password', 'page' => 'reset_password'],
        // *** Must be before source ***
        ['path' => 'show_media_file', 'title' => 'Show media file', 'page' => 'show_media_file'],
        ['path' => 'sources', 'title' => 'Sources', 'page' => 'sources', 'vars' => 'select_tree_id'],
        ['path' => 'source', 'title' => 'Source', 'page' => 'source', 'vars' => 'select_tree_id,id'],
        ['path' => 'statistics', 'title' => 'Statistics', 'page' => 'statistics'],
        ['path' => 'timeline', 'title' => 'Timelines', 'page' => 'timeline', 'vars' => 'select_tree_id,id'],
        ['path' => 'user_settings', 'title' => 'Settings', 'page' => 'user_settings'],

        // Backwards compatibility only:
        ['path' => 'gezin', 'title' => 'Family Page', 'page' => 'family'],
        ['path' => 'lijst_namen', 'title' => 'Names', 'page' => 'list_names'],
        ['path' => 'lijst', 'title' => 'Persons', 'page' => 'list'],
    ];
    /*
    Examples:
    ['path' => '/cookies', 'title' => 'cookie_list', 'file' => 'cookies.php'],
    ['path' => '/help', 'title' => 'help', 'file' => 'help.php'],

    ['path' => '/tree-([0-9]+)', 'title' => 'tree_home', 'file' => 'tree_index.php', 'vars' => 'tree_id'],
    ['path' => "/([a-z]+)", 'title' => "cms_page", 'file' => 'cms_pages.php', 'vars' => 'cms_page_name'],
    */

    public function get_route($request_uri)
    {
        //TODO remove global
        global $humo_option;

        $result_array = [];
        $result_array['page404'] = false;
        //$result_array['page301'] = false;

        // Query-string routes take priority over URL path matching. This prevents
        // folder names such as "familytree" from being mistaken for the family route.
        if (isset($_GET['page']) && is_string($_GET['page'])) {
            foreach ($this->routes_array as $route_array) {
                // Match both canonical page names and legacy route aliases such as
                // page=descendant_report, which resolves to the family page.
                if ($route_array['page'] === $_GET['page'] || $route_array['path'] === $_GET['page']) {
                    $result_array['page'] = $route_array['page'];
                    $result_array['title'] = $humo_option["database_name"] . ' - ' . __($route_array['title']);

                    $request_path = parse_url($request_uri, PHP_URL_PATH) ?: '';
                    $index_position = strrpos($request_path, '/index.php');
                    $result_array['tmp_path'] = $index_position !== false
                        ? substr($request_path, 0, $index_position + 1)
                        : '';
                    break;
                }
            }
        } else {
            // Match rewritten routes by complete path segments, not substrings.
            $request_path = parse_url($request_uri, PHP_URL_PATH) ?: '';
            $url_array = array_values(array_filter(explode('/', trim($request_path, '/')), 'strlen'));

            foreach ($this->routes_array as $route_array) {
                $route_position = array_search($route_array['path'], $url_array, true);
                if ($route_position === false) {
                    continue;
                }

                $result_array['page'] = $route_array['page'];
                $result_array['title'] = $humo_option["database_name"] . ' - ' . __($route_array['title']);

                $path_segments = array_slice($url_array, 0, $route_position);
                $result_array['tmp_path'] = $path_segments
                    ? '/' . implode('/', $path_segments) . '/'
                    : '';

                // *** Check if link to website is valid. ***
                $check_route = $path_segments ? implode('/', $path_segments) . '/' : '';
                if ($check_route && !file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $check_route)) {
                    $result_array['page404'] = true;
                }

                // *** Get URL rewrite variables ***
                if ($humo_option["url_rewrite"] == "j" && isset($route_array['vars'])) {
                    $vars = explode(',', $route_array['vars']);
                    $value_position = $route_position + 1;

                    if (count($vars) === 1 && isset($url_array[$value_position])) {
                        $result_array[$vars[0] === 'select_tree_id' ? 'select_tree_id' : 'id'] = $url_array[$value_position];
                    }

                    if (count($vars) === 2) {
                        if (isset($url_array[$value_position])) {
                            $result_array['select_tree_id'] = $url_array[$value_position];
                        }
                        if (isset($url_array[$value_position + 1])) {
                            $result_array[$vars[1]] = $url_array[$value_position + 1];
                        }
                    }
                }
                break;
            }
        }

        // *** No valid page found. Check if link is the homepage.  ***
        /*
        if (!isset($result_array['page'])) {
            // *** Check if the URI links to the correct server folder ***
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $request_uri)) {
                $result_array['page404'] = true;
            }
        }
        */

        // *** Reroute links like: humo-gen/%3Cb%3E37%3C/languages/cs/flag.gif ***
        // *** %3Cb%3E = <b> ***
        //if (strpos($_SERVER['REQUEST_URI'], '%3Cb%3E') > 0) {
        //  $result_array['page301'] = str_replace('%3Cb%3E', '', $_SERVER['REQUEST_URI']);
        //  $result_array['page301'] = str_replace('%3C', '', $result_array['page301']);
        //}

        return $result_array;
    }
}
