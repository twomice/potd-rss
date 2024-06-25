<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/config.php';

validateConfigOrDie($config);

$htmlFile = 'https://en.wikipedia.org/wiki/'. $config['wikiPageTitle'];
//$htmlFile = 'example.html';

purgeWikiPageCache($config['wikiPageTitle']);
$info = getInfoFromFile($htmlFile);
validateInfoOrDie($info);
$rssOutput = getRssOutput($info);
writeToRssFile($rssOutput, $config['outputFile']);

/**
 * Ensure our configuration is sound; if not, die with error messages.
 * @param array $config
 */
function validateConfigOrDie($config) {
  $errors = [];
  if (empty($config['outputFile'])) {
    $errors[] = 'Missing configuration: outputFile';
  }
  elseif (is_dir($config['outputFile'])) {
    $errors[] = "outputFile '{$config['outputFile']}' is a directory; must be a filename.";
  }
  elseif (!is_writable($config['outputFile'])) {
    $errors[] = "outputFile '{$config['outputFile']}' is not writable.";
  }
  if (empty($config['wikiPageTitle'])) {
    $errors[] = 'Missing configuration: wikiPageTitle';
  }
  if (!empty($errors)) {
    echo "Configuration is not sound; ending here. See errors below.\n";
    var_dump($errors);
    die();
  }
}

/**
 * Write a string to a file.
 * @param string $rssString The RSS to write out.
 * @param string $outputFile Full system path to the output filename.
 */
function writeToRssFile($rssString, $outputFile) {
  $fp = fopen($outputFile, 'w');
  fputs($fp, $rssString);
  fclose($fp);
}

/**
 * Compose and return RSS based on the given information.
 * @param array $info As returned by getInfoFromFile().
 * @return string Full RSS output.
 */
function getRssOutput($info) {
  $rssDate = date('r');
  $feedDocument = new FluentDOM\DOM\Document();
  $feedDocument->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
  $feedDocument->formatOutput = TRUE;

  $rss = $feedDocument->appendElement('rss', ['version' => '2.0']);

  $channel = $rss->appendElement('channel');
  $channel->appendElement('title', 'CIGA POTD');
  $channel->appendElement('link', 'https://twomice.me/ciga');
  $channel->appendElement('description', 'Daily Wikipedia POTD');
  $channel->appendElement('pubDate', $rssDate);
  $channel->appendElement('lastBuildDate', $rssDate);

  $item = $channel->appendElement('item');
  $item->appendElement('title', $info['title']);
  $item->appendElement('link', $info['href']);
  $item->appendElement('pubDate', $rssDate);
  $item->appendElement('guid', $info['potdUrl']);
  $description = $item->appendElement('description', $info['description']);
  $description->appendElement('img', ['src' => $info['src']]);

  return $feedDocument->saveXml();
}

/**
 * Invoke Wikipedia API to purche cache on a given page, to ensure that the POTD
 * content on that page is current.
 * 
 * @param string $wikiPageTitle The Wikipedia title of the relevant Wikipedia page.
 */
function purgeWikiPageCache($wikiPageTitle) {
  $endPoint = "https://en.wikipedia.org/w/api.php";

	$params = [
		"action" => "purge",
		"titles" => $wikiPageTitle,
		"format" => "json"
	];

	$ch = curl_init();

	curl_setopt( $ch, CURLOPT_URL, $endPoint );
	curl_setopt( $ch, CURLOPT_POST, true );
	curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

	$output = curl_exec( $ch );
	curl_close( $ch );
}

/**
 * Parse the given html to build an array of info for our RSS feed.
 * @param string $htmlFile The file to be loaded by FluentDOM.
 * 
 * @return array
 */
function getInfoFromFile($htmlFile) {
  $info = [];
  $document = FluentDOM::load(
    $htmlFile,
    'text/html',
    [FluentDOM\Loader\Options::ALLOW_FILE => TRUE]
  );

  $urlA = $document('//span[@id="potdfeeder-url"]/descendant::a')[0];
  $info['potdUrl'] = $urlA['href'];

  $titleA = $document('//div[@id="potdfeeder-title"]/descendant::a')[0];
  $info['title'] = $titleA->textContent;
  $info['href'] = 'https://en.wikipedia.org' . $titleA['href'];

  $img = $document('//div[@id="potdfeeder-potd"]/descendant::img')[0];
  if ($img) {
    // Get the largest srcset candidate. (Assume this content usese only 'x' descriptors,
    // not 'w' descriptors). Also treat 'src' as the '1x' candidate.
    $srcset = [
      '1.0000' => $img['src']
    ];
    foreach (explode(',', $img['srcset']) as $candidate) {
      $arr = preg_split('/\s+/', trim($candidate));
      list($src, $descriptor) = $arr;
      $descriptor = (float) $descriptor;
      $descriptor = number_format((float)$descriptor, 4, '.', '');
      $srcset["$descriptor"] = $src;
    }
    ksort($srcset);
    $info['src'] = array_pop($srcset);
  }
  else {
    $video = $document('//div[@id="potdfeeder-potd"]/descendant::video')[0];
    if ($video) {
      $info['src'] = $video['poster'];
    }
  }

  $caption = $document('//div[@id="potdfeeder-caption"]')[0];
  $info['description'] = trim($caption->textContent);
  
  return $info;
}

/**
 * Ensure our info will result in desirable RSS content; if not, die with errors.
 * @param type $info
 */
function validateInfoOrDie($info) {
  $errors = [];
  if(empty($info['src'])) {
    $errors[] = "Te 'src' element is missing";
  }
  if (!empty($errors)) {
    echo "Parsed info won't result in desirable RSS content; ending here. See errors below.\n";
    var_dump($errors);
    die();
  }
}