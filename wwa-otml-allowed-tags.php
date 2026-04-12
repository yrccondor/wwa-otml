<?php
namespace WPWebAuthn\OTML;

if (!defined('ABSPATH')) {
    exit;
}

$wwaotml_allowed_tags = array(
    'a' => array(
        'href' => true,
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'target' => true,
        'rel' => true
    ),
    'abbr' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'title' => true,
        'lang' => true,
        'dir' => true
    ),
    'address' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'area' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'alt' => true,
        'coords' => true,
        'download' => true,
        'href' => true,
        'referrerpolicy' => true,
        'rel' => true,
        'shape' => true,
        'target' => true
    ),
    'b' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'bdo' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'dir' => true
    ),
    'big' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'blockquote' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'br' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'caption' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'code' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'center' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'cite' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'del' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'div' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true,
        'lang' => true,
        'height' => true,
        'width' => true
    ),
    'dfn' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'title' => true,
        'lang' => true,
        'dir' => true
    ),
    'dl' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'dt' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'dd' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'em' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'font' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'color' => true,
        'face' => true,
        'size' => true
    ),
    'h1' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true
    ),
    'h2' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true
    ),
    'h3' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true
    ),
    'h4' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true
    ),
    'h5' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true
    ),
    'h6' => array(
        'title' => true,
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true
    ),
    'hr' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'size' => true,
        'width' => true
    ),
    'i' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'img' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'border' => true,
        'height' => true,
        'hspace' => true,
        'src' => true,
        'usemap' => true,
        'vspace' => true,
        'width' => true,
        'title' => true,
        'alt' => true
    ),
    'ins' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'title' => true
    ),
    'kbd' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'title' => true
    ),
    'label' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'li' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'dir' => true,
        'type' => true
    ),
    'map' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'alt' => true,
        'coords' => true,
        'href' => true,
        'rel' => true,
        'shape' => true,
        'target' => true,
        'name' => true
    ),
    'ol' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'dir' => true,
        'type' => true
    ),
    'p' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'dir' => true,
        'lang' => true
    ),
    'pre' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'q' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'cite' => true
    ),
    's' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'samp' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'strike' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'small' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'span' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'strong' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'sub' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'sup' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'table' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'bgcolor' => true,
        'background' => true,
        'border' => true,
        'cellpadding' => true,
        'cellspacing' => true,
        'lang' => true,
        'dir' => true,
        'frame' => true,
        'rules' => true,
        'width' => true,
        'height' => true,
        'valign' => true
    ),
    'thead' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'bgcolor' => true,
        'background' => true,
        'border' => true,
        'lang' => true,
        'dir' => true,
        'valign' => true
    ),
    'tbody' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'bgcolor' => true,
        'background' => true,
        'border' => true,
        'lang' => true,
        'dir' => true,
        'valign' => true
    ),
    'tfoot' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'bgcolor' => true,
        'background' => true,
        'border' => true,
        'lang' => true,
        'dir' => true,
        'valign' => true
    ),
    'td' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'abbr' => true,
        'align' => true,
        'bgcolor' => true,
        'background' => true,
        'colspan' => true,
        'dir' => true,
        'height' => true,
        'lang' => true,
        'rowspan' => true,
        'scope' => true,
        'valign' => true,
        'width' => true
    ),
    'th' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'abbr' => true,
        'align' => true,
        'background' => true,
        'bgcolor' => true,
        'colspan' => true,
        'dir' => true,
        'height' => true,
        'lang' => true,
        'scope' => true,
        'valign' => true,
        'width' => true
    ),
    'tr' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'align' => true,
        'bgcolor' => true,
        'background' => true,
        'dir' => true,
        'valign' => true,
        'lang' => true
    ),
    'tt' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'u' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'ul' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'dir' => true
    ),
    'var' => array(
        'class' => true,
        'id' => true,
        'style' => true
    ),
    'html' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'lang' => true,
        'dir' => true
    ),
    'body' => array(
        'class' => true,
        'id' => true,
        'style' => true,
        'lang' => true,
        'dir' => true,
        'bgcolor' => true,
        'background' => true
    ),
    'style' => array(
        'type' => true
    ),
    'svg' => array(
        'xmlns' => true,
        'fill' => true,
        'viewbox' => true,
        'role' => true,
        'aria-hidden' => true,
        'focusable' => true,
        'class' => true,
    ),
    'path' => array(
        'd'=> true,
        'fill'=> true,
        'opacity'=> true,
    ),
    'g' => array(),
    'use' => array(
        'xlink:href' => true,
    ),
    'symbol' => array(
        'aria-hidden'=> true,
        'viewBox' => true,
        'id' => true,
        'xmls' => true,
    )
);
