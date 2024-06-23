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
  $info['src'] = $a['src'];
  $info['title'] = $a['alt'];
}
foreach ($document('//table[@class="potdfeeder"]/descendant::a[@href][@title="Wikipedia:Featured pictures"][not(@class)]/ancestor::div[position()=2]') as $a) {
  // $a alone gives me sanitized text, but I want the innerHTML.
  $info['description'] = $a->saveHtml();
}

foreach ($document('//table[@class="potdfeeder"]/descendant::a[@href][@title="' . $info['title'] . '"][not(@class)]') as $a) {
  if ($a['title'] == $info['title'] && $a['class'] == '') {
    echo "Link: ". $a['href'] . "\n";
  }
  $info['caption'] = (string)$a;
  $info['href'] = $a['href'];
}

var_dump($info);

