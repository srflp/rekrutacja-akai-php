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

  public function getAllVehicles() {
    $vehicle_arr = [];

    do {
      $res = $this->request($resource ?? 'vehicles');
      $resource = $res->next;
      foreach ($res->results as $vehicle) {
        foreach ($vehicle->pilots as $pilot_index => $pilot) {
          $res_pilot = $this->request($pilot);
          $vehicle->pilots[$pilot_index] = [
            'name' => $res_pilot->name,
            'species' => [],
          ];
          foreach ($res_pilot->species as $species) {
            $res_species = $this->request($species);
            $vehicle->pilots[$pilot_index]['species'][] = $res_species->name;
          }
        }
        foreach ($vehicle->films as $film_index => $film) {
          $res_film = $this->request($film);
          $vehicle->films[$film_index] = $res_film->title;
        }
        $vehicle_arr[] = $vehicle;
      }
    } while($res->next);

    return $vehicle_arr;
  }

}

// DOM-handling class
class MyDOM {
  private $dom;

  public function __construct() {
    $this->dom = new DOMDocument();
  }

  public function createElement($parent, $tag, $content = '', $attributes = []) {
    $this_el = $this->dom->createElement($tag, $content);
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
$vehicles = $client->getAllVehicles();

$dom = new MyDOM();

$ul_el = $dom->createElement(null, 'ul');
foreach ($vehicles as $vehicle) {
  $pilots = [];
  foreach ($vehicle->pilots as $pilot) {
    $species = implode(', ', $pilot['species']);
    $pilots[] = "{$pilot['name']} ({$species})";
  }
  $pilots = implode(', ', $pilots) ?: 'none';
  $films = implode(', ', $vehicle->films);
  $li_el = $dom->createElement($ul_el, 'li');
  $p_el = $dom->createElements($li_el, 'p', [
    "Name: {$vehicle->name}",
    "Model: {$vehicle->model}",
    "Manufacturer: {$vehicle->manufacturer}",
    "Cost: {$vehicle->cost_in_credits}",
    "Length: {$vehicle->length}",
    "Max atmosphering speed: {$vehicle->max_atmosphering_speed}",
    "Crew: {$vehicle->crew}",
    "Passengers: {$vehicle->passengers}",
    "Cargo capacity: {$vehicle->cargo_capacity}",
    "Consumables: {$vehicle->consumables}",
    "Vehicle class: {$vehicle->vehicle_class}",
    "Pilots: {$pilots}",
    "Films: {$films}",
  ]);
  $dom->createElement($ul_el, 'br');
}

echo $dom->getHTML();

?>
