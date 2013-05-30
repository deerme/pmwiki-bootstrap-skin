<?php if (!defined('PmWiki')) exit();
/*  Copyright 2013 Michael Paulukonis
    This file is bootstrap-fluid.php; part of the bootstrap skin for pmwiki 2
    you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
*/



global $RecipeInfo, $SkinName, $SkinRecipeName, $WikiStyleApply, $PageLogoUrl, $HTMLHeaderFmt, $PageHeaderFmt, $PageNavStyle, $UseDarkstrapCss;
# Some Skin-specific values
$RecipeInfo['BootstrapSkin']['Version'] = '2013-05-20';
$SkinName = 'bootstrap-fluid';
$SkinRecipeName = "BootstrapSkin";

# for use in conditional markup  (:if enabled TriadSkin:)
global $BootstrapSkin; $BootstrapSkin = 1;


$PageLogoUrl = "$SkinDirUrl/images/ico/favicon.png";

## from Hans Bracker's Triad skin (version 2008-07-10)
## automatic loading of skin default config pages
global $WikiLibDirs, $SkinDir;
$where = count($WikiLibDirs);
if ($where>1) $where--;
array_splice($WikiLibDirs, $where, 0,
             array(new PageStore("$SkinDir/wikilib.d/\$FullName")));


## you must populate $UseDarktstrapCSS in local/config.php
## ROADMAP: instead of one variable, will able to choose between a variety of bootstrap themes (user-configurable)
## cookie or something.

## NOTE: the light-theme's inverse (Dark) is the dark-theme's "normal"
## so the below settings for navbar look the same
if ($UseDarkstrapCss == true) {
        $HTMLHeaderFmt['option-css'] =
    "<link href='$SkinDirUrl/css/darkstrap.css' rel='stylesheet'>
    <!-- all customization should go in pmwiki.darkstrap.css -->
    <link href='$SkinDirUrl/css/pmwiki.darkstrap.css' rel='stylesheet'>";

        $PageNavStyle =
                "<div id='wikihead' class='navbar navbar-fixed-top'> ";


} else {

        $HTMLHeaderFmt['option-css'] = "";
        $PageNavStyle =
                "<div id='wikihead' class='navbar navbar-inverse navbar-fixed-top'>";
}


## required for apply-actions
$WikiStyleApply['link'] = 'a';  #allows A to be labelled with class attributes


Markup('button', 'links',
       '/\\(:button(\\s+.*?)?:\\)/ei',
       "Keep(BootstrapButton(PSS('$1 ')), 'L')");


function BootstrapButton($args) {

        $opt = ParseArgs($args);

        // expect link, class

        // TODO: test for options
        // TODO: handle alt params
        // TODO: handle rel=nofollow per pmwiki settings
        // what about other PmWiki shortcut-type things?
        // like... [[PmWiki/basic editing|+]]%apply=link class="btn"%

        $target = $opt['link'];
        $text = $opt['text'] ? $opt['text'] : $target; // if text not provided, default to the link
        $class = $opt['class'];

        $l = '<a href="%s" class="%s">%s</a>';
        $linkf = sprintf($l, $target, $class, $text);

        return $linkf;

}

# the markup seems to work -- it's just that the CSS isn't finding the icon set...

Markup('icon', 'inline',
       '/\\(:icon(\\s+.*?)?:\\)/ei',
       "BootstrapIcon(PSS('$1 '))");

function BootstrapIcon($args) {

        $icon = sprintf('<i class=%s ></i>', $args);

        return $icon;
}


## added, not working. commenting out for the time being Saturday, May 25, 2013

# attempt to set configs via actions....


/* global $Now, $CookiePrefix, $DarkstrapCookie; */

/* # set cookie expire time (default 1 year) */
/* SDV($DarkstrapCookieExpires,$Now+60*60*24*365); */

/* $prefix = $CookiePrefix.$SkinName.'_'; */

/* SDV($SkinCookie, $prefix.'setskin'); */

/* # darkstrap cookie routine */
/* if($EnableDarkstrapOptions==1) { */
/*     SDV($DarkstrapCookie, $prefix.'setcss'); */
/*     if (isset($_COOKIE[$DarkstrapCookie])) $sf = $_COOKIE[$DarkstrapCookie]; */
/*     if (isset($_GET['setcss'])) { */
/*       $sf = $_GET['setcss']; */
/*       setcookie($DarkstrapCookie,$sf,$DarkstrapCookieExpires,'/');} */
/*     if (isset($_GET['css'])) $sf = $_GET['css']; */
/*     if (@$PageDarkstrapList[$sf]) $DarkstrapCss = $PageDarkstrapList[$sf]; */
/*     else $sf = $DefaultDarkstrap; */
/* } */

#####end cookies
