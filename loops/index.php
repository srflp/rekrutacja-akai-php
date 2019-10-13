<?php
// @author: Marcin Lawniczak <marcin@lawniczak.me>
// @date: 24.10.2017
// @update: 26.09.2019
// This is a simple task, which does not require Composer
// The script will be outputting to a web browser, so use HTML for formatting
// Write out numbers from 100 to 0, each in a separate line
// If the number is a multiple of 4, write Fire instead of the number
// If the number is a multiple of 7, write Boom instead of the number
// If the number is a multiple of 10, repeat it 10 times in the same line in red


// ^ The instruction above is a bit confusing.
// How should I handle the case of a number which is simultaneously a multiple of 4 and 7?
// Should I print both Fire and Boom then? I did so.
//
// And should I print a total of 11 numbers when the number is a multiple of 10?
// (1 in black, 10 in red?)
// I did so, but it looks to me like an unwanted behavior.
// "If the number is a multiple of 10, repeat it 10 times in the same line in red, instead of writing the number" sounds clear
//
// I think that this whole instruction should be improved, so that people
// could clearly understand which numbers should they print, and which not.

// Data generating logic:
// returns two-element Array containing a black string, and then a red string
function rowGenerator() {
  for ($i = 1; $i <= 100; $i++) {
    $row_chunks = [];
    if ($i % 4 != 0 && $i % 7 != 0) {
      $row_chunks[] = $i;
    }
    if ($i % 4 == 0) {
      $row_chunks[] = 'Fire';
    }
    if ($i % 7 == 0) {
      $row_chunks[] = 'Boom';
    }
    $row_chunks = [implode($row_chunks, ' ')];
    if ($i % 10 == 0) {
      $row_chunks[] = trim(str_repeat($i . ' ', 10));
    }
    yield $row_chunks;
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

  public function getHTML() {
    return $this->dom->saveHTML();
  }
}

// View logic:
$dom = new MyDOM();

$style_el = $dom->createElement(null, 'style', '.red { color: #DC143C; }', [
  'type' => 'text/css',
]);
$ul_el = $dom->createElement(null, 'ul');

$chunked_rows = rowGenerator();
foreach ($chunked_rows as $chunked_row) {
  $li_el = $dom->createElement($ul_el, 'li');
  $span_el = $dom->createElement($li_el, 'span', $chunked_row[0]);
  if (!empty($chunked_row[1])) {
    $red_span_el = $dom->createElement($li_el, 'span', ' ' . $chunked_row[1], [
      'class' => 'red',
    ]);
  }
}

echo $dom->getHTML();

?>
