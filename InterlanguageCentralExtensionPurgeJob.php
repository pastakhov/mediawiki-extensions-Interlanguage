<?php

use MediaWiki\MediaWikiServices;

/**
 * MediaWiki InterlanguageCentral extension
 * InterlanguageCentralExtensionPurgeJob class
 *
 * Copyright © 2010-2011 Nikola Smolenski <smolensk@eunet.rs>
 * @version 1.2
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

// Based on https://www.mediawiki.org/wiki/Manual:Job_queue/For_developers
class InterlanguageCentralExtensionPurgeJob extends Job {
	public function __construct( Title $title, array $params ) {
		parent::__construct( 'purgeDependentWikis', $title, $params );
	}

	/**
	 * Execute the job
	 *
	 * @return bool
	 */
	public function run() {
		//sleep() could be added here to reduce unnecessary use
		$ill = $this->params['ill'];

		$mwServices = MediaWikiServices::getInstance();

		foreach($ill as $lang => $pages) {
//			$iw = Interwiki::fetch( $lang );
			$iw = $mwServices->getInterwikiLookup()->fetch( $lang );
			if( !$iw ) continue;
			$apiUrl = $iw->getAPI();
			if( !$apiUrl ) continue;
			$apiUrl .= '?' . wfArrayToCGI( [
				'action'	=> 'purge',
				'format'	=> 'json', //Smallest response
				'titles'	=> implode( '|', array_keys( $pages ) )
			] );
//			Http::post( $apiUrl );
			$mwServices->getHttpRequestFactory()->post( $apiUrl );
			//TODO: error handling
		}

		return true;
	}

	//TODO: custom insert with duplicate merging
}
