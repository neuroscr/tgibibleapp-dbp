<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;

class ContentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('remote_biblebook_checker', function ($attribute, $value, $parameters, $validator) {
            $content_config = config('services.content');
            $valid = true;
            if (!empty($content_config['url'])) {
                $input = $validator->getData();
                $book_id = array_get($input, 'book_id');
                $client = new Client();
                if ($book_id) {
                    // bible_id and book_id
                    // validate bible or book exists
                    try {
                        $res = $client->get($content_config['url'] .
                           'bibles/' . $value . '/book/'. $book_id .
                           '?v=4&key=' . $content_config['key'],
                           ['http_errors' => false]);
                        $result = json_decode($res->getBody() . '', true);
                        if (isset($result['error'])) {
                            $valid = false;
                        } else
                        if (isset($result['data']) && !count($result['data'])) {
                            // book missing
                            $valid = false;
                        }
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $valid = false;
                    }
                } else {
                    // bible_id book only
                    try {
                        $res = $client->get($content_config['url'] . 'bibles/' . $value .
                           '/name/'. $GLOBALS['i18n_id'] . '?v=4&key=' . $content_config['key'],
                           ['http_errors' => false]);
                        $result = json_decode($res->getBody() . '', true);
                        if (isset($result['error'])) {
                            // bible missing
                            $valid = false;
                            $validator->errors()->add('bible_id', 'bible_id must exist!');
                        }
                    } catch (\GuzzleHttp\Exception\RequestException $e) {
                        $valid = false;
                    }
                }
            }
            return $valid;
        }, ':attribute does not exist');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
