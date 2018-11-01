<?php

namespace Tests\Feature;

use App\Models\Country\Country;
use App\Models\Language\Alphabet;
use App\Models\Language\Language;
use App\Models\Language\NumeralSystem;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class v4_wikiRoutesTest extends API_V4_Test
{

	public function test_v4_wiki_countries_all()
	{
		/**
		 * @category V4_API
		 * @category Route Name: v4_countries.all
		 * @category Route Path: https://api.dbp.test/countries?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\CountriesController::index
		 */
		$path = route('v4_countries.all', $this->params);
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();

		/**
		 * @category V4_API
		 * @category Route Name: v4_countries.jsp
		 * @category Route Path: https://api.dbp.test/countries/joshua-project?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\CountriesController::joshuaProjectIndex
		 */
		$path = route('v4_countries.jsp', $this->params);
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();

		/**
		 * @category V4_API
		 * @category Route Name: v4_countries.one
		 * @category Route Path: https://api.dbp.test/countries/{country_id}?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\CountriesController::show
		 */
		$current_country = Country::inRandomOrder()->first();
		$path = route('v4_countries.one', array_add($this->params, 'country_id', $current_country->id));
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();
	}


	public function test_v4_wiki_languages_all()
	{
		/**
		 * @category V4_API
		 * @category Route Name: v4_languages.all
		 * @category Route Path: https://api.dbp.test/languages?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\LanguagesController::index
		 */
		$path = route('v4_languages.all', $this->params);
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();

		/**
		 * @category V4_API
		 * @category Route Name: v4_languages.one
		 * @category Route Path: https://api.dbp.test/languages/{language_id}?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\LanguagesController::show
		 */
		$current_language = Language::inRandomOrder()->first();
		$path = route('v4_languages.one', array_add($this->params, 'language_id', $current_language->id));
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();
	}

	public function test_v4_wiki_alphabets()
	{
		/**
		 * @category V4_API
		 * @category Route Name: v4_alphabets.all
		 * @category Route Path: https://api.dbp.test/alphabets?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\AlphabetsController::index
		 */
		$path = route('v4_alphabets.all', $this->params);
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();

		/**
		 * @category V4_API
		 * @category Route Name: v4_alphabets.one
		 * @category Route Path: https://api.dbp.test/alphabets/{alphabet_id}?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\AlphabetsController::show
		 */
		$current_alphabet = Alphabet::inRandomOrder()->first();
		$path = route('v4_alphabets.one', array_add($this->params, 'alphabet_id', $current_alphabet->script));
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();

		/**
		 * @category V4_API
		 * @category Route Name: v4_alphabets.store
		 * @category Route Path: https://api.dbp.test/alphabets?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\AlphabetsController::store
		 */
		$test_alphabet = [
			'script'                => 'Zwww',
			'name'                  => 'Test Script',
			'family'                => 'Artificial',
			'type'                  => 'alphabet',
			'white_space'           => '[unspecified]',
			'open_type_tag'         => '[none]',
			'complex_positioning'   => '[unknown]',
			'requires_font'         => 0,
			'unicode'               => 1,
			'diacritics'            => 0,
			'contextual_forms'      => 0,
			'reordering'            => 0,
			'case'                  => 1,
			'split_graphs'          => 0,
			'status'                => 1,
			'baseline'              => 0,
			'direction'             => 'ltr',
			'sample'                => 'Just Normal Latin Characters',
			'description'           => 'This script was generated by a test.'
		];
		$path = route('v4_alphabets.store', $this->params);
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->post($path, $test_alphabet);
		$response->assertSuccessful();

		/**
		 * @category V4_API
		 * @category Route Name: v4_alphabets.update
		 * @category Route Path: https://api.dbp.test/alphabets/{alphabet_id}?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\AlphabetsController::update
		 */
		$alphabet = Alphabet::where('name','Test Script')->first();
		$path = route('v4_alphabets.update', array_merge(['alphabet_id' => $alphabet->script], $this->params));
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->put($path, ['description' => 'This script was generated and updated by a test']);
		$response->assertSuccessful();

		Alphabet::where('name','Test Script')->delete();
	}


	public function test_v4_wiki_numbers()
	{
		/**
		 * @category V4_API
		 * @category Route Name: v4_numbers.all
		 * @category Route Path: https://api.dbp.test/numbers/?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\NumbersController::index
		 */
		$path = route('v4_numbers.all', $this->params);
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();

		/**
		 * @category V4_API
		 * @category Route Name: v4_numbers.one
		 * @category Route Path: https://api.dbp.test/numbers/{number_id}?v=4&key=1234
		 * @see      \App\Http\Controllers\Wiki\NumbersController::show
		 */
		$numeralSystem = NumeralSystem::inRandomOrder()->first();
		$path = route('v4_numbers.one', array_merge(['id' => $numeralSystem->id], $this->params));
		echo "\nTesting: $path";
		$response = $this->withHeaders($this->params)->get($path);
		$response->assertSuccessful();
	}
}