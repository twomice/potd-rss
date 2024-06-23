<?php
require __DIR__.'/vendor/autoload.php';

$htmlFile = 'http://www.heise.de/';
$htmlFile = 'https://en.wikipedia.org/wiki/User:Potdfeeder';
$htmlFile = 'example.html';

$links = [];

$document = FluentDOM::load(
  $htmlFile,
  'text/html',
  [FluentDOM\Loader\Options::ALLOW_FILE => TRUE]
);


foreach ($document('//table[@class="potdfeeder"]/descendant::img') as $a) {
  var_dump($a['src']);
  var_dump($a['alt']);
  $title = $a['alt'];
}
foreach ($document('//table[@class="potdfeeder"]/descendant::a[@href][@title="' . $title . '"][not(@class)]') as $a) {
  if ($a['title'] == $title && $a['class'] == '') {
    echo "Link: ". $a['href'] . "\n";
  }
  $links[] = [
    'caption' => (string)$a,
    'href' => $a['href']
  ];
}
var_dump($links);

