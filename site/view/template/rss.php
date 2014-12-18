<?php
namespace RevenantBlue\Site;

require_once DIR_APPLICATION . 'model/articles/articles-main.php';
// Initialize the blog model;
$blog = new Articles;
$rssEntries = $blog->loadArticlesByPublished(10, 0, 1, 'date_posted', 'desc');

// Begin RSS feed.
$channel = array(
	"title"        => $globalSettings['site_title']['value']
	"description"  => $globalSettings['site_description']['value'],
	"link"         => HTTP_SERVER . "news"
);

$items = array();
foreach($rssEntries as $entry) {
	$items[] = array(
		"title"       => $entry['title'],
		"description" => "<![CDATA[" . str_replace("\r\n", "", $entry['summary']) ."<br /><br />]]>",
		"link"        => HTTP_SERVER . 'news/' . $entry['alias'],
		"pubDate"     => $entry['date_posted']
	);
}

header('Content-type: text/xml');

$output = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n";
$output .= "<rss version=\"2.0\">\n";
$output .= "\t<channel>\n";
$output .= "\t\t<title>" . $channel["title"] . "</title>\n";
$output .= "\t\t<description>" . $channel["description"] . "</description>\n";
$output .= "\t\t<link>" . $channel["link"] . "</link>\n";

foreach ($items as $item) {
	$output .= "\t\t<item>\n";
	$output .= "\t\t\t<title>" . $item["title"] . "</title>\n";
	$output .= "\t\t\t<description>" . $item["description"] . "</description>\n";
	$output .= "\t\t\t<link>" . $item["link"] . "</link>\n";
	$output .= "\t\t\t<pubDate>" . $item["pubDate"] . "</pubDate>\n";
	$output .= "\t\t</item>\n";
}
$output .= "\t</channel>\n";
$output .= "</rss>\n";


echo $output;
