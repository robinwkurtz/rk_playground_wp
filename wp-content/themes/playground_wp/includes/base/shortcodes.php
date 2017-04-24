<?php

add_filter('the_content', 'wpautop', 40);
add_filter('the_content', 'empty_paragraph_fix', 20);
//add_filter('the_content', 'shortcode_empty_paragraph_fix', 12);

function shortcode_empty_paragraph_fix($content) {
	$array = array(
		'<p>[' => '[',
		']</p>' => ']',
		']<br />' => ']',
	);
	$content = strtr($content, $array);
	return $content;
}

function empty_paragraph_fix($content) {
	$find = array(
		'<p></div>', '<p><div', '</div></p>', '<br />', '<p></p>', "</div>\n</p>\n<p>", 'columns"></p>');
	$replace = array(
		'</div>', '<div', '</div>', '', '', '</div>', 'columns">');
	$content = str_replace($find, $replace, $content);
	return $content;
}

function yearCode() {
	return date("Y");
}
add_shortcode('year', 'yearCode');

/** Instructions:
 * [email]foo@bar.com[/email]
 * [email email='foo@bar.com']email me![/email]
 * [email email='foo@bar.com' data-update='false']<foo>Bar html -- this doesnt get touched by js </foo>[/email]
 **/
function emailCode($atts, $content = null) {
	/**
	 * @var $class $class
	 * @var $email $email
	 * @var $update_text $update_text
	 **/
	extract(shortcode_atts(array('type' => '', 'color' => '', 'email' => '', 'class' => '', 'update_text' => true), $atts));

	//$classes = "cta cta-email js-replacer-text color-$color type-$type $class";
	$classes = "js-replacer-text $class";
	$classes = trim($classes);

	$toSplit = ($email !== '' ? $email : do_shortcode($content));
	$outputContent = ($email !== '' ? do_shortcode($content) : '');
	$splitVals = explode('@', $toSplit);
	$domain = array_pop($splitVals);
	$email = $splitVals[0];

	$dataTags = '';
	$dataTags .= ($domain !== '' ? " data-domain='$domain'" : '');
	$dataTags .= ($email !== '' ? " data-extra='$email'" : '');
	$dataTags .= ($update_text !== true ? " data-update='false'" : '');
	$dataTags .= ($outputContent !== '' && $update_text === true ? " data-text='$outputContent'" : '');

	$returnContent = "<a class='$classes' href='#' $dataTags>";
	if ($update_text !== true):
		$returnContent .= $outputContent;
	else:
		$returnContent .= _x('Please enable JavaScript', 'Titles', theme_domain());
	endif;
	$returnContent .= "</a>";
	return $returnContent;
}
add_shortcode('email', 'emailCode');

function buttonCode($atts, $content = null)
{
	extract(shortcode_atts(array('link' => '', 'class' => '', 'popout' => ''), $atts));
	$url = ($link !== '' ? $link : '#');
	$target = ($popout === 'true') ? '_blank' : '_self';
	return '<a href="' . $url . '" class="btn" target="' . $target . '">' . $content . '</a>';
}

add_shortcode('button', 'buttonCode');

function videopopCode($atts, $content = null)
{
	extract(shortcode_atts(array('link' => '', 'class' => ''), $atts));
	$url = ($link !== '' ? $link : '#');
	return '<a href="' . $url . '" class="link js-modal-video">' . $content . '</a>';
}

add_shortcode('videopop', 'videopopCode');
