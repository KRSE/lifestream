<?php
$twitter_user = 'ekfors';
$feeds = array(
	'http://www.42km.se/rss/',
	'http://api.flickr.com/services/feeds/photos_public.gne?id=47324257@N00&lang=en-us&format=rss_200',
	'http://krse.tumblr.com/rss/',
	'http://del.icio.us/rss/KRSE',
	'http://twitter.com/statuses/user_timeline/ekfors.rss'
);

function getClass($url)
{
	// Create a CSS class name from the URL of the feed.
	$class = parse_url($url, PHP_URL_HOST); // TODO use regex to get the hostname instead of this flakey PHP function.
	$class = preg_replace("/www\./", "", $class); // Remove `www.`.
	$class = preg_replace("/\.(com|org|net)/", "", $class); // Remove top level domains. Add more as you see fit.
	$class = preg_replace("/\./", "_", $class); // Replace `.`s with `_`s.
	$class = preg_replace('#^\d+#', '', $class); // Remove numbers i beginning of string
	return $class;
}

date_default_timezone_set('Europe/Stockholm'); // Change this to your timezone.
require_once('simplepie.inc');
foreach ($feeds as $feed) {
	$merge[] = new SimplePie($feed);
}

$merged = SimplePie::merge_items($merge, 0, 25); // Get the 20 most recent items.
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
<head>
	<title>Someone's Lifestream</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>

	<h1>My Lifestream</h1>
<?php
	$thedate = '';
	foreach ($merged as $item):
		if ($thedate != $item->get_date('F j, Y')) {
			$thedate =  $item->get_date('F j, Y');
			echo '<h2>' . $thedate . '</h2>';
		}
		$theclass = getClass($item->feed->get_permalink());
?>
	<div class="item <?php echo $theclass; ?>">
		<?php if (stripos($item->feed->get_permalink(), 'twitter.com') ): // This is a Tweet. ?>

		<div class="content">
			<?php
			$tweet = $item->get_description();
			// Tweet parsing mostly from Phwitter: http://jasontan.org/code/phwitter/
			$tweet = preg_replace("/^" . $twitter_user . ":/", "", $tweet); // Strip username from begenning of Tweet.
			$tweet = preg_replace("/(http|https|ftp):\/\/[^\s]*/i","<a href=\"$0\">$0</a>", $tweet); // Add links to URLs
			$tweet = preg_replace("/@([a-zA-Z0-9_]*)/","<a href=\"http://twitter.com/$1\">$0</a>", $tweet); // Make @username a link to a username's profile.
			echo $tweet;
			?>
		</div><!-- .content -->

		<?php else: // Not Twitter ?>

		<?php if (!stripos($item->feed->get_permalink(), 'tumblr.com') ): // Don't show titles on Tumblr posts. ?>
		<h3><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a></h3>
		<?php endif; ?>
		<div class="content">
			<?php 
			$post = $item->get_description();
			$post = preg_replace("/<img[^>]+\>/i", "", $post); // Remove images 
			$post =	preg_replace('{^(<br(\s*/)?>|ANDnbsp;)+}i', '', $post); // Remove excess <br> and &nbsp; from start of string
			$post = preg_replace('{(<br(\s*/)?>|ANDnbsp;)+$}i', '', $post); // Remove excess <br> and &nbsp; from end of string 
			echo strip_tags($post, '<a>');
			?> 
		</div><!-- .content -->

		<?php endif; // end of Twitter check ?>
		<div class="date"><small>Posted at <?php echo $item->get_date('g:i a'); ?></small></div>
	</div><!-- .item -->
	<?php endforeach; ?>
	<div class="note"><a href="http://github.com/trey/lifestream/">Make your own Lifestream. &rarr;</a></div>
</body>
</html>
