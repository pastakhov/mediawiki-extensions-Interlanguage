<?php
/**
 * MediaWiki InterlanguageCentral extension v1.3
 *
 * Copyright © 2010-2011 Nikola Smolenski <smolensk@eunet.rs>
 * @version 1.3
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * For more information,
 * @see https://www.mediawiki.org/wiki/Extension:Interlanguage
 */

$wgInterlanguageCentralExtensionIndexUrl = "";

$wgJobClasses['purgeDependentWikis'] = 'InterlanguageCentralExtensionPurgeJob';
$wgExtensionCredits['parserhook'][] = [
	'name'			=> 'Interlanguage Central',
	'author'		=> 'Nikola Smolenski',
	'url'			=> 'https://www.mediawiki.org/wiki/Extension:Interlanguage',
	'version'		=> '1.4.0',
	'descriptionmsg'	=> 'interlanguagecentral-desc',
];

$wgMessagesDirs['InterlanguageCentral'] = __DIR__ . '/i18n/central';
$wgExtensionMessagesFiles['InterlanguageCentralMagic'] = dirname(__FILE__) . '/InterlanguageCentral.i18n.magic.php';
$wgAutoloadClasses['InterlanguageCentralExtensionPurgeJob'] = dirname(__FILE__) .  '/InterlanguageCentralExtensionPurgeJob.php';
$wgAutoloadClasses['InterlanguageCentralExtension'] = dirname(__FILE__) . '/InterlanguageCentralExtension.php';
$wgAutoloadLocalClasses['ApiQueryLangLinks'] = dirname(__FILE__) . '/api/ApiQueryLangLinks.php';
$wgHooks['ParserFirstCallInit'][] = 'wfInterlanguageCentralExtension';

/**
 * @param $parser Parser
 * @return bool
 */
function wfInterlanguageCentralExtension( $parser ) {
	global $wgHooks, $wgInterlanguageCentralExtension;

	if( !isset( $wgInterlanguageCentralExtension ) ) {
		$wgInterlanguageCentralExtension = new InterlanguageCentralExtension();
		$wgHooks['LinksUpdate'][] = $wgInterlanguageCentralExtension;
		$parser->setFunctionHook( 'languagelink', [ $wgInterlanguageCentralExtension, 'languagelink' ], Parser::SFH_NO_HASH );
	}
	return true;
}

$wgHooks['LoadExtensionSchemaUpdates'][] = function ( DatabaseUpdater $updater ) {
	$updater->addExtensionTable( 'interlanguage_links', __DIR__ . '/db_patches/interlanguage_links.sql' );
};

$wgHooks['LanguageLinks'][] = function ( Title $title, &$links, &$linkFlags ) {
	$pageId = $title->getArticleID();
	if ( !$pageId ) {
		return;
	}

	$a = [];
	foreach ( $links as $l ) {
		list ( $lang, $titleText ) = explode( ':', $l );
		$a[$lang] = $titleText;
	}

	$conds = [ 'ill_from' => $title->mArticleID ];
	$dbr = wfGetDB( DB_REPLICA );
	if ( $a ) {
		$conds[] = 'ill_lang NOT IN (' . $dbr->makeList( array_keys( $a ) ) . ')';
	}
	$res = $dbr->select(
		'interlanguage_links',
		[ 'ill_lang', 'ill_title' ],
		$conds,
		__FUNCTION__
	);
	foreach ( $res as $row ) {
		if ( isset( $a[$row->ill_lang] ) ) {
			continue;
		}
		$a[$row->ill_lang] = true;
		$links[] = $row->ill_lang . ':' . $row->ill_title;
	}
};
