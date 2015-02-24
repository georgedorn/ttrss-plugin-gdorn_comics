<?php
/**
 * Okay, instead of implementing this with all kinds of callbacks and filter files,
 * this is just going to be one massive file.  Fork it and submit PRs to improve it
 * if you feel like it, otherwise I'm just scratching my own itch here.
 *
 * Most important: Keep it simple.  Follow one pattern.
 */

define('ADD_IDS', True);


class gdorn_comics extends Plugin {
	function about() {
		return array(0.1,
			'Yet more stupid-simple comic-fetching.',
			'gdorn');
	}

	function init($host) {
		/**
		 * When debugging or writing a new fetcher, use the two RENDER hooks.
		 * These work in realtime, but don't cache, so they're slow.
		 *
		 * So once you are happy with your fetcher, use the ARTICLE_FILTER hook.
		 */
//		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
		$host->add_hook($host::HOOK_RENDER_ARTICLE_CDM, $this);
		$host->add_hook($host::HOOK_RENDER_ARTICLE, $this);
	}

	function hook_render_article_cdm($article) {
		return $this->mangle_article($article);
	}

	function hook_render_article($article) {
		return $this->mangle_article($article);
	}

	function mangle_article($article) {
		// Eat That Toast bog-standard example
		if (strpos($article['link'], 'eatthattoast.com/comic/') !== FALSE) {
			$article['content'] = preg_replace('#(/[0-9-]+)-150x150\.gif#', '$1.gif', $article['content']);
			$article['content'] = preg_replace('#(width|height)="150"#', '', $article['content']);
		}
		// Joy of Tech
		elseif (strpos($article['link'], 'http://www.geekculture.com/joyoftech/') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, '//p[@class="Maintext"]//img[contains(@src, "joyimages")]', $article);
		}
		// Girls with Slingshots
		elseif (strpos($article['link'], 'girlswithslingshots.com/comic/') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, "//div[@id='comicbody']//img", $article);
		}
		// CTRL+ALT+DEL Sillies
		elseif (strpos($article['link'], 'cad-comic.com/sillies/') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, "//div[@id='content']/img", $article);
		}
		// Three Panel Soul
		elseif (strpos($article['link'], 'threepanelsoul.com/2') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']/img", $article);
		}
		// Two Lumps
		elseif (strpos($article['link'], 'twolumps.net/d/') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, "//img[@class='ksc' and contains(@src, 'comics')]", $article);
		}
		// Breaking Cat News
		elseif (strpos($article['link'], 'breakingcatnews.com/comic/') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']/img", $article);
		}
		// Something Positive
		elseif (strpos($article['link'], 'somethingpositive.net') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, "//img[starts-with(@src, 'sp') and contains(@src, 'png')]", $article);
		}
		// Timothy Winchester (People I Know)
		elseif (strpos($article['link'], 'www.timothywinchester.com/2') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$orig_content = strip_tags($article['content']);
			$article['content'] = $this->get_img_tags($xpath, "//div[@class='singleImage']/img[@class='magicfields']", $article);
			$article['content'] .= "<br>$orig_content</br>";
		}
		// Awkward Zombie
		elseif (strpos($article['link'], 'awkwardzombie.com/index.php?comic') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$orig_content = strip_tags($article['content']);
			$article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']/img", $article);
			$article['content'] .= "<p>$orig_content</p>";
			//also append the blarg post because that's small, interesting,
			//and sometimes necessary for old fogeys like me to get what game it's about
			$entries = $xpath->query("//div[@id='blarg']/div[last()]");
			foreach ($entries as $entry){
				$article['content'] .= "<p><i>" . $entry->textContent . "</i></p>";
			}
		}
		// Scenes From A Multiverse (to get alt tags)
		elseif (strpos($article['link'], 'amultiverse.com/comic/') !== FALSE) {
			$xpath = $this->get_xpath_dealie($article['link']);
			$article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']//img", $article);
		}
		// XKCD (alt tags we don't need to call out for)
		elseif (strpos($article['content'], 'imgs.xkcd.com/comics/') !== FALSE) {
			$doc = new DOMDocument();
			$doc->loadHTML($article['content']);
			$xpath = new DOMXpath($doc);
			$imgs = $xpath->query('//img'); //doesn't get simpler than this
			foreach($imgs as $img){
				$article['content'] .= "<br><i>Alt: " . $img->getAttribute('title') . "</i>";
			}
		}
		// Questionable Content (cleanup)
		elseif (strpos($article['link'], 'questionablecontent') !== FALSE) {
			// only keep everything starting at the first <img>
			if(preg_match("@.*(<img.*)@", $article['content'], $matches)){
				$article['content'] = $matches[1];
			}
		}
		// Least I Could Do (wtf image size?)
		elseif (strpos($article['link'], 'leasticoulddo.com/comic') !== FALSE) {
			// only keep everything starting at the first <img>
			if(preg_match("@.*(<img.*?>)@", $article['content'], $matches)){
				$img = $matches[1];
				$img = preg_replace("@width=\"\d+\"@", "", $img);
				$img = preg_replace("@height=\"\d+\"@", "", $img);
				$article['content'] = $img;
			}
		}

		if(ADD_IDS){
			$article['content'] .= "<br>ID: " . $article['id'];
		}
		return $article;
	}

	function get_img_tags($xpath, $query, $article){
		$entries = $xpath->query($query);
		$result_html = '';
		foreach ($entries as $entry){
			$orig_src = $entry->getAttribute('src');
			$new_src = $this->rel2abs($orig_src, $article['link']);
			$entry->setAttribute('src', $new_src);
			$result_html .= $entry->ownerDocument->saveXML($entry);
			$alt_text = trim($entry->getAttribute('alt'));
			if (!$alt_text){
				$alt_text = trim($entry->getAttribute('title'));
			}
			if ($alt_text && $alt_text != $article['title']){
				$result_html .= "<br><i>Alt: $alt_text</i></br>";
			}
		}
		return $result_html;
	}


	function get_xpath_dealie($link){
		list($html, $content_type) = $this->get_content($link);
		$doc = new DOMDocument();
		$doc->loadHTML($html);
		$xpath = new DOMXPath($doc);
		return $xpath;
	}

	function get_content($link) {
		/**
		 * Use this if you want to dig into the linked page for content, e.g. alt tags.
		 */
		global $fetch_last_content_type;
		$html = fetch_file_contents($link);
		$content_type = $fetch_last_content_type;
		return array( $html,  $content_type);
	}

	function rel2abs($rel, $base)
	{
		if (parse_url($rel, PHP_URL_SCHEME) != '' || substr($rel, 0, 2) == '//') {
			return $rel;
		}
		if ($rel[0]=='#' || $rel[0]=='?') {
			return $base.$rel;
		}
		extract(parse_url($base));
		$path = preg_replace('#/[^/]*$#', '', $path);
		if ($rel[0] == '/') {
			$path = '';
		}

		/* dirty absolute URL */
		$abs = "$host$path/$rel";

		/* replace '//' or '/./' or '/foo/../' with '/' */
		$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
		for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

		/* absolute URL is ready! */
		return $scheme.'://'.$abs;
	}

	function api_version() {
		return 2;
	}
}

