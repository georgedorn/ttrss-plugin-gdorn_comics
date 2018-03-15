<?php
/**
 * Okay, instead of implementing this with all kinds of callbacks and filter files,
 * this is just going to be one massive file.  Fork it and submit PRs to improve it
 * if you feel like it, otherwise I'm just scratching my own itch here.
 *
 * Most important: Keep it simple.  Follow one pattern.
 */

define('ADD_IDS', False);
define("GDORN_DEBUG", False);

class gdorn_comics extends Plugin {
    function about() {
        return array(
            0.1,
            'Yet more stupid-simple comic-fetching.',
            'gdorn'
        );
    }

    function init($host) {
        /**
         * When debugging or writing a new fetcher, use the two RENDER hooks.
         * These work in realtime, but don't cache, so they're slow.
         *
         * So once you are happy with your fetcher, use the ARTICLE_FILTER hook.
         */
        //    $host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
        $host->add_hook($host::HOOK_RENDER_ARTICLE_CDM, $this);
        $host->add_hook($host::HOOK_RENDER_ARTICLE, $this);
    }

    function hook_render_article_cdm($article) {
        return $this->mangle_article($article);
    }

    function hook_render_article($article) {
        try {
            return $this->mangle_article($article);
        }
        catch (Exception $e) {
            $article['content'] .= "<br>Error processing via gdorn_comics plugin!<br>" . $e->getMessage();
        }
    }

    function mangle_article($article) {
        // Penny Arcade
        if (strpos($article["link"], "penny-arcade.com") !== FALSE && strpos($article["title"], "Comic:") !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//div[@id="comicFrame"])', $article);
        }
        // The Trenches
        elseif (strpos($article["link"], "trenchescomic.com/comic/post/") !== FALSE &&
                strpos($article["title"], "Comic:") !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//div[@id="comic"]//img)', $article);
        }
        // SatW
        elseif (strpos($article["link"], "satwcomic.com/") !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//div[@class="container"]//center//img)', $article);
        }
        // Manic Pixie Nightmare Girls
        elseif (strpos($article["link"], "manicpixienightmaregirls.com/") !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//div[@id="main"]//img)', $article);
        }
        // PhD Unknown
        elseif (strpos($article["link"], "www.phdunknown.com/index.php?id=") !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//div[@id="comicbody"]//img)', $article) . $article['content'];
        }
        // SBMC
        elseif (strpos($article["link"], "www.smbc-comics.com/comic/") !== FALSE) {
            if (strpos($article["content"], "bonus panel!") !== FALSE) {
                $xpath = $this->get_xpath_dealie($article['link']);
                $aftercomic = $this->get_img_tags($xpath, '(//div[@id="aftercomic"]//img)', $article);
                $article['content'] .= $aftercomic;
            }
        }
        // Chainsawsuit
        elseif (strpos($article["link"], "chainsawsuit.com/comic/") !== FALSE ) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//div[@id="comic"]//img)', $article) . $article['content'];
        }
        // Eat That Toast
        elseif (strpos($article["link"], "eatthattoast.com/comic/") !== FALSE ) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//div[@id="comic"]//img)', $article);
        }
        // Poorly Drawn Lines
        elseif (strpos($article["link"], "poorlydrawnlines.com/comic/") !== FALSE ) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $img_tag = $this->get_img_tags($xpath, '(//div[@class="post"]//img)', $article);

            $article['content'] =  $img_tag . $article['content'];
        }
        // Cyanide & Happiness
        elseif (strpos($article["link"], "explosm.net/comics") !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '(//img[@id="main-comic"])', $article);
        }
        // Joy of Tech
        elseif (strpos($article['link'], 'www.geekculture.com/joyoftech/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, '//p[@class="Maintext"]//img[contains(@src, "joyimages")]', $article);
        }
        /* OotS uses some kind of referer check which prevents the browser from getting the image.
         * Possible fix would be to download the image via fetch_file_contents and mirror it.
        // Order of the Stick
        elseif (strpos(strtolower($article['link']), 'giantitp.com/comics/') !== FALSE) {
        	$xpath = $this->get_xpath_dealie($article['link']);
        	$article['content'] = $this->get_img_tags($xpath, '//td/img[contains(@src, "/comics/images/")]', $article);
        }
        */
        // Girls with Slingshots
        elseif (strpos($article['link'], 'girlswithslingshots.com/comic/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='cc-comicbody']//img", $article);

        }
        // CTRL+ALT+DEL Sillies
        elseif (strpos($article['link'], 'cad-comic.com/sillies/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='content']/img", $article);
        }
        // CTRL+ALT+DEL
        elseif (strpos($article['link'], 'cad-comic.com/comic/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@class='comicpage']//img[contains(@src, 'ENG_')]", $article);
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
        // Gunnerkrigg Court
        elseif (strpos($article['link'], 'gunnerkrigg.com/?p') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//img[starts-with(@src, '/comics/') and @class='comic_image']", $article);
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
            foreach ($entries as $entry) {
                $article['content'] .= "<p><i>" . $entry->textContent . "</i></p>";
            }
        }
        // Camp Weedonwantcha
        elseif (strpos($article['link'], 'campcomic.com/comic/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']//img", $article);
        }
        // The Oatmeal
        elseif (strpos($article['link'], '//theoatmeal.com/comics/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']//img", $article);
        }
        // Poly In Pictures
        elseif (strpos($article['link'], 'polyinpictures.com/comic/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']//img", $article);
        }
        // Dilbert
        elseif (strpos($article['link'], 'dilbert.com/strip/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@class='img-comic-container']//img", $article);
        }
        // Doghouse Diaries, which has broken alt tags in feedburner (if there are quotes)
        elseif (strpos($article['content'], 'thedoghousediaries.com/dhdcomics/') !== FALSE){
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='imgdiv']//img", $article);
            //also get blog
            $entries = $xpath->query("//div[@id='signoff-wrapper']");
            foreach ($entries as $entry) {
                $article['content'] .= "<p><i>" . $entry->textContent . "</i></p>";
            }
        }
        // Pain Train (to get alt tag)
        elseif (strpos($article['link'], 'paintraincomic.com/comic/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']//img", $article);
        }
        // Achewood (alt tag)
        elseif (strpos($article['link'], 'http://www.achewood.com/index.php?date=') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//p[@id='comic_body']//a//img", $article);
        }
        // Scenes From A Multiverse (to get alt tags)
        elseif (strpos($article['link'], 'amultiverse.com/comic/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']//img", $article);
        }
        // Dead Philosophers
        elseif (strpos($article['link'], 'dead-philosophers.com/?p') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic-1']//img", $article);
        }
        // Robot Hugs
        elseif (strpos($article['link'], 'www.robot-hugs.com/') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic-1']//img", $article);
            // And get blog entry
            $entries = $xpath->query("//div[@class='entry']");
            foreach ($entries as $entry) {
                $article['content'] .= "<br><p><i>" . $entry->textContent . "</i></p>";
            }
        }
        // Dinosaur Comics Cleanup
        elseif (strpos($article['link'], 'qwantz.com/index.php?comic') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//img[@class='comic']", $article);
            //also get the blog
            $entries = $xpath->query("//span[@class='rss-content']");
            foreach ($entries as $entry) {
                $article['content'] .= "<p>" . $entry->ownerDocument->saveXML($entry) . "</p>";
            }
        }
        // XKCD (alt tags we don't need to call out for)
        elseif (strpos($article['content'], 'imgs.xkcd.com/comics/') !== FALSE) {
            //noop
        }
        // Wondermark (alt tag already present)
        elseif (strpos($article['link'], 'wondermark.com/c') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//div[@id='comic']//img", $article);
            $entries = $xpath->query("//div[@id='comic-notes']");
            foreach ($entries as $entry) {
                $article['content'] .= "<p>" . $entry->ownerDocument->saveXML($entry) . "</p>";
            }
        }
        // Invisible Bread (make the bread visible)
        elseif (strpos($article['content'], 'invisiblebread.com/2') !== FALSE) {
            $doc = new DOMDocument();
            $doc->loadHTML($article['content']);
            $xpath = new DOMXpath($doc);
            $bread = $xpath->query("//a[contains(@href, 'bonus-panel')]")->item(0);
            if ($bread) {
                $bread_page_url = $bread->getAttribute('href');
                $xpath = $this->get_xpath_dealie($bread_page_url);
                $extraimage = $xpath->query("//img[@class='extrapanelimage']")->item(0);
                if ($extraimage) {
                    $new_element = $doc->createElement("img");
                    $new_element->setAttribute('src', $extraimage->getAttribute('src'));
                    $bread->parentNode->replaceChild($new_element, $bread);
                    $article['content'] = $doc->saveXML();
                }
            }
        }
        // Questionable Content (cleanup)
        elseif (strpos($article['link'], 'questionablecontent') !== FALSE) {
            // replace the <table> (containing project wonderful) with nothing
            $article['content'] = preg_replace("@<table.*table>@", '', $article['content']);
            // only keep everything starting at the first <img>
            if (preg_match("@.*?(<img.*.png\">.*)@", $article['content'], $matches)) {
               $article['content'] = $matches[1];
            }
        }
        // Alice Grove (get bigger image)
        elseif (strpos($article['link'], 'alicegrove.com') !== FALSE) {
            $xpath = $this->get_xpath_dealie($article['link']);
            $article['content'] = $this->get_img_tags($xpath, "//figure[@class='photo-hires-item']//img", $article);
        }
        // Least I Could Do (wtf image size?)
        elseif (strpos($article['link'], 'leasticoulddo.com/comic') !== FALSE) {
//            $xpath = $this->get_xpath_dealie($article['link']);
//            $article['content'] = $this->get_img_tags($xpath, "//div[@class='comic-wrap']//img[@class='comic']", $article);
        }
        //Sites that provide images and just need alt tags textified.
        elseif (strpos($article['content'], 'www.asofterworld.com/index.php?id') !== FALSE) {
            //no-op
        }
        //No matches
        else {
            return $article;
        }

        $article = $this->alt_textify($article);

        if (GDORN_DEBUG && $article['debug']) {
            foreach ($article['debug'] as $msg) {
                $article['content'] .= "<br><small>" . $msg . "</small>";
            }
        }

        if (ADD_IDS) {
            $article['content'] .= "<br>ID: " . $article['id'];
        }
        return $article;
    }

    /**
     * Inserts text captions from any image with an alt or title tag.
     */
    function alt_textify($article, $doc = NULL) {
        if ($doc === NULL) {
            $doc = new DOMDocument();
            $doc->loadHTML($article['content']);
        }
        $xpath = new DOMXpath($doc);
        $imgs = $xpath->query('//img');
        foreach ($imgs as $img) {
            $alt_text = trim($img->getAttribute('alt'));
            if ($alt_text == $article['title'] || strpos($article['title'], $alt_text) !== False) {
                $alt_text = false;
            }
            $article['debug'][] = "Got ($alt_text) from alt tag.";
            $title_text = trim($img->getAttribute('title'));
            if ($title_text == $article['title'] || strpos($article['title'], $title_text) !== False) {
                $title_text = false;
            }
            $article['debug'][] = "Got ($alt_text) from title tag.";
            if (!$alt_text && !$title_text) {
                $article['debug'][] = "No alt text for img " . $img->getAttribute('src');
                continue;
            }

            $article['debug'][] = "Before: " . htmlspecialchars($article['content']);
            $new_element = $doc->createElement("div");
            $new_element->appendChild($img->cloneNode());
            $para_element = $doc->createElement("p");
            $para_element->setAttribute('style', 'text-align:center');
            $new_element->appendChild($para_element);

            if ($alt_text && !$title_text){
                $text_element = $doc->createElement("i", $alt_text);
                $para_element->appendChild($text_element);
            } elseif ($title_text && !$alt_text){
                $text_element = $doc->createElement("i", $title_text);
                $para_element->appendChild($text_element);
            } elseif ($alt_text == $title_text){
                $text_element = $doc->createElement("i", $alt_text);
                $para_element->appendChild($text_element);
            } elseif (strpos($alt_text, $title_text) !== false){
                $text_element = $doc->createElement("i", $alt_text);
                $para_element->appendChild($text_element);
            } elseif (strpos($title_text, $alt_text) !== false){
                $text_element = $doc->createElement("i", $title_text);
            } else {
                // there's both alt and title texts, they're both different, use both.
                $alt_element = $doc->createElement("i", $alt_text);
                $para_element->appendChild($alt_element);
                $para_element->appendChild($doc->createElement("br"));
                $title_element = $doc->createElement("i", $title_text);
                $para_element->appendChild($title_element);
            }
            $img->parentNode->replaceChild($new_element, $img);
        }
        $article['content'] = $doc->saveHTML();
        $article['debug'][] = "After: " . htmlspecialchars($article['content']);
        return $article;
    }

    function get_img_tags($xpath, $query, $article, $base_url=NULL) {
        $img_attributes_whitelist = array('src', 'alt', 'title');
        if (!$base_url){
            $base_url = $article['link'];
        }
        $entries = $xpath->query($query);
        $result_html = '';
        foreach ($entries as $entry) {
            $orig_src = $entry->getAttribute('src');
            $new_src = $this->rel2abs($orig_src, $base_url);
            $article['debug'][] = "rel2abs turned ($orig_src) into ($new_src)";
            $entry->setAttribute('src', $new_src);
            $attributes = $entry->attributes;
            $to_remove = array();
            foreach($attributes as $attrib_name => $node){
                if (!in_array($attrib_name, $img_attributes_whitelist)){
                    $to_remove[] = $attrib_name;
                }
            }
            foreach($to_remove as $attrib_name){
                $entry->removeAttribute($attrib_name);
            }
            $result_html .= $entry->ownerDocument->saveXML($entry);
        }
        return $result_html;
    }

    function get_xpath_dealie($link) {
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
        if (!$html){
            global $fetch_last_error_code;
            $html = $fetch_last_error_code;
        }
        $content_type = $fetch_last_content_type;
        return array(
            $html,
            $content_type
        );
    }

    function rel2abs($rel, $base) {
        $rel = trim($rel);
        if (parse_url($rel, PHP_URL_SCHEME) != '' || substr($rel, 0, 2) == '//') {
            return $rel;
        }
        if ($rel[0] == '#' || $rel[0] == '?') {
            return $base . $rel;
        }
        extract(parse_url($base));
        $path = preg_replace('#/[^/]*$#', '', $path);
        if ($rel[0] == '/') {
            $path = '';
        }

        /* dirty absolute URL */
        $abs = "$host$path/$rel";

        /* replace '//' or '/./' or '/foo/../' with '/' */
        $re = array(
            '#(/\.?/)#',
            '#/(?!\.\.)[^/]+/\.\./#'
        );
        for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
        }

        /* absolute URL is ready! */
        return $scheme . '://' . $abs;
    }

    function api_version() {
        return 2;
    }
}
