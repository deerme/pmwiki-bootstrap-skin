<?php if (!defined('PmWiki')) exit();
/*  Copyright 2013 Michael Paulukonis
    This file is bootstrap-fluid.php; part of the bootstrap skin for pmwiki 2
    you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published
    by the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
*/


global $RecipeInfo, $SkinName, $SkinRecipeName, $WikiStyleApply, $PageLogoUrl,
    $HTMLHeaderFmt, $PageHeaderFmt, $PageNavStyle, $UseDarkstrapTheme, $UseFlatUI,
    $PageEditForm, $PageTextStartFmt, $BodySpan, $BootBodyClass;
# Some Skin-specific values
## TODO auto-populate from jake task (since version is tracked there)
$RecipeInfo['BootstrapSkin']['Version'] = '0.2.4';
$SkinName = 'bootstrap-fluid';
$SkinRecipeName = "BootstrapSkin";

# for use in conditional markup  (:if enabled BootstrapSkin:)
global $BootstrapSkin; $BootstrapSkin = 1;

SDV($PageEditForm, "Bootstrap.EditForm");

# default, unless already set in config.php
if (!isset($PageLogoUrl)) {
    $PageLogoUrl = "$SkinDirUrl/images/ico/favicon.png";
}


## from Hans Bracker's Triad skin (version 2008-07-10)
## automatic loading of skin default config pages
global $WikiLibDirs, $SkinDir;
$where = count($WikiLibDirs);
if ($where>1) $where--;
array_splice($WikiLibDirs, $where, 0,
             array(new PageStore("$SkinDir/wikilib.d/\$FullName")));

# add stylechange.php for cookie setting code if set.
/* if ($EnableStyleOptions == 1)  */
# disabling this causes a ton of issues - need to revisit to see what should be where....
include_once("$SkinDir/themechange.php");

## required for apply-actions
$WikiStyleApply['link'] = 'a';  #allows A to be labelled with class attributes

# Markup() is a core pmwiki function defined in pmwiki.php
# Keep() is a core pmwiki function defined in pmwiki.php
Markup('button', 'links',
           '/\\(:button(\\s+.*?)?:\\)/ei',
           "Keep(BootstrapButton(PSS('$1 ')), 'L')");

# ParseArgs() is a core pmwiki function defined in pmwiki.php
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

Markup('icon', 'inline',
       '/\\(:icon(\\s+.*?)?:\\)/ei',
       "BootstrapIcon(PSS('$1 '))");

function BootstrapIcon($args) {

       $icon = sprintf('<i class=%s ></i>', $args);

       return Keep($icon);
}

# if (:noleft:) markup is present, mainbody will be span12
# otherwise, default to span9 (ie sidebar is span3)
if (! isset($BodySpan)) {
   $BodySpan = "span9";
}

Markup('noleft', 'directives',
       '/\\(:noleft:\\)/ei',
       "HideLeftBoot()");

# SetTmplDisplay() is a core pmwiki function defined in pmwiki.php
function HideLeftBoot() {

    global $BodySpan;
    $BodySpan = "span12";

    SetTmplDisplay('PageLeftFmt',0);

}



# if (:boostrap-center-main:) markup is present, mainbody will be no class
# and BootBodyClass will be container, instead of row-fluid
# otherwise, span9/12 is required for content to appear to the right of the left-bar.
if (! isset($BootBodyClass)) {
   $BootBodyClass = "row-fluid";
}

Markup('bootstrap-center-main', 'directives',
       '/\\(:bootstrap-center-main:\\)/ei',
       "BootstrapCenterMain()");   

function BootstrapCenterMain() {

	global $BodySpan, $BootBodyClass;
	$BodySpan = "";
	$BootBodyClass = "container";
	
	SetTmplDisplay('PageLeftFmt', 0);

}	   


/* Dropdowns
   Used in menu sections (navbar, etc). To use, insert:

   (:bgroups title=Groups pattern=pattern exclude=glob:)

   where title is the setting for the dropdown group.

   ganked from https://github.com/tamouse/pmwiki-bootstrap-skin/blob/dropdowns/bootstrap.php

   TODO this markup has been superceeded by "(:bdropdown :)" markup in dropdown.php file
        being left in temporarily as updates are progressing
*/
Markup("bgroups",">links","/\\(:bgroupdropdown\s*(.*?)\s*:\\)/e",
       "GroupDropdownMenu('$1')");

function GroupDropdownMenu($inp) {

    $defaults = array('title'=>'Dropdown');

    $args = array_merge($defaults, ParseArgs($inp));


    $inline_code_begin = "<li class='dropdown'><a href='#' class='dropdown-toggle' data-toggle='dropdown'>".$args['title']."<b class='caret'></b></a><ul class='dropdown-menu'>";
    $inline_code_end = "</ul></li>";

    // NOTE: if pattern not present, will default to ALL pages in wiki
    // hrm. "pattern" should probably change to "include"....
    $pattern = $args['pattern'];
    $exclude = $args['exclude']; // initial implementation is a naive string-check

    // TODO: improve exclude pattern
    // look at http://www.pmwiki.org/wiki/PmWiki/PageLists

    $group_list = GetWikiPages($pattern, $exclude);
    $formatted_list = BuildGroupList($group_list);

    return Keep($inline_code_begin.$formatted_list.$inline_code_end);

}

/*
  ListPages() is a core pmwiki function defined in pmwiki.php
  and takes a glob-pattern, NOT page-list patterns
*/
function GetWikiPages($pattern, $exclude) {

    $pagelist = ListPages($pattern);
    $grouplist = array();
    foreach($pagelist as $page) {
        list ($group, $name) = explode('.',$page);
        if (stristr($exclude, $name) === FALSE) {
            $grouplist[] = "($group.)$name";
        }
    }
    sort($grouplist);
    return $grouplist;
}


/*
  HTML based on code from http://scottgalloway.blogspot.com/2012/08/twitter-bootstrap-nested-nav-lists.html
  MakeLink is a core pmwiki function defined in pmwiki.php
 */
function BuildGroupList($list) {
    $out = '';

    $group = '';
    foreach($list as $page) {
        # if group name is empty or != previous group name, capture it and start new unordered list
        preg_match('/\((.*?).\)/', $page, $matches);
        if ($group == '' || $group != $matches[1]) {

            # only close if a group has been set
            if ($group != '') {
                $out .= "</ul></li>";
            }

            $group = $matches[1];

            $out .= "<li class='nav nav-list'>$group<b class='caret'></b>";
            $out .= "<ul class='dropdown-menu'>";
        }

        $out .= '<li>';
        $out .= MakeLink($pagename, $page);
        $out .= '</li>';
    }

    $out .= "</ul></li>";

    return $out;
}


include_once("$SkinDir/dropdown.php");