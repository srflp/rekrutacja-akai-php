<?php
require __DIR__ . '/vendor/autoload.php';

// @author: Michal Dolata <https://github.com/MichalDolata>
// @author: Marcin Lawniczak <marcin@lawniczak.me>
// @date: 26.09.2017
// @update: 26.09.2019
// This task does require Composer. You can add more libraries if you want to.
// We suggest using Guzzle for requests (http://docs.guzzlephp.org/en/stable/)
// Remember to composer install
// The script will be outputting to a web browser, so use HTML for formatting

// When making different kinds of applications, data is often needed that we don't yet have.
// Many 3rd party providers offer APIs (wikipedia.org/wiki/Application_programming_interface)
// that can be consumed to find data we need.

// Your task is to use the The Star Wars API (https://swapi.co/) and it's docs (https://swapi.co/documentation)
// Display all starships provided through the API, with their properties
// Each ship should have the names of pilots and names of films displayed (if none, indicate)
// Each pilot should have its species also displayed

// Star Wars API Client
class SWApiClient {
  private $guzzle;

  public function __construct() {
    $this->guzzle = new \GuzzleHttp\Client([ 'base_uri' => 'https://swapi.co/api/' ]);
  }

  private function request($resource) {
    return json_decode(
      $this->guzzle->get($resource)->getBody()->getContents()
    );
  }

  public function getAllStarships() {
    $starship_arr = [];

    do {
      $res = $this->request($resource ?? 'starships');
      $resource = $res->next;
      foreach ($res->results as $starship) {
        foreach ($starship->pilots as $pilot_index => $pilot) {
          $res_pilot = $this->request($pilot);
          $starship->pilots[$pilot_index] = [
            'name' => $res_pilot->name,
            'species' => [],
          ];
          foreach ($res_pilot->species as $species) {
            $res_species = $this->request($species);
            $starship->pilots[$pilot_index]['species'][] = $res_species->name;
          }
        }
        foreach ($starship->films as $film_index => $film) {
          $res_film = $this->request($film);
          $starship->films[$film_index] = $res_film->title;
        }
        $starship_arr[] = $starship;
      }
    } while($res->next);

    return $starship_arr;
  }

}

// DOM-handling class
class MyDOM {
  private $dom;

  public function __construct() {
    $this->dom = new DOMDocument();
  }

  public function createElement($parent, $tag, $content = '', $attributes = []) {
    $this_el = $this->dom->createElement($tag, htmlspecialchars($content));
    foreach ($attributes as $name => $value) {
      $attr = $this->dom->createAttribute($name);
      $attr->value = $value;
      $this_el->appendChild($attr);
    }
    if ($parent == null) {
      $this->dom->appendChild($this_el);
    } else {
      $parent->appendChild($this_el);
    }

    return $this_el;
  }

  public function createElements($parent, $tag, $content_list = ['']) { // not supporting attributes
    $elements = [];
    foreach ($content_list as $content) {
      $elements[] = $this->createElement($parent, $tag, $content);
    }
    return $elements;
  }

  public function getHTML() {
    return $this->dom->saveHTML();
  }
}

// View logic:
$client = new SWApiClient();
$starships = $client->getAllStarships();

$dom = new MyDOM();

$ul_el = $dom->createElement(null, 'ul');
foreach ($starships as $starship) {
  $pilots = [];
  foreach ($starship->pilots as $pilot) {
    $species = implode(', ', $pilot['species']) ?: 'unknown species';
    $pilots[] = "{$pilot['name']} ({$species})";
  }
  $pilots = implode(', ', $pilots) ?: 'none';
  $films = implode(', ', $starship->films);
  $li_el = $dom->createElement($ul_el, 'li');
  $p_el = $dom->createElements($li_el, 'p', [
    "Name: {$starship->name}",
    "Model: {$starship->model}",
    "Starship class: {$starship->starship_class}",
    "Manufacturer: {$starship->manufacturer}",
    "Cost: {$starship->cost_in_credits} credits",
    "Length: {$starship->length} m",
    "Crew: {$starship->crew}",
    "Passengers: {$starship->passengers}",
    "Max atmosphering speed: {$starship->max_atmosphering_speed}",
    "Hyperdrive rating: {$starship->hyperdrive_rating}",
    "MGLT: {$starship->MGLT}",
    "Cargo capacity: {$starship->cargo_capacity}",
    "Consumables: {$starship->consumables}",
    "Pilots: {$pilots}",
    "Films: {$films}",
  ]);
  $dom->createElement($ul_el, 'br');
}

echo $dom->getHTML();

?>
