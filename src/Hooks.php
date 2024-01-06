<?php
/**
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\BoilerPlate;

class Hooks implements \MediaWiki\Hook\BeforePageDisplayHook {

	/**
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 * @param \OutputPage $out
	 * @param \Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		// Show page link based on current day of the year as seed
		$dayOfYear = date( 'z' );
		// If we already got an article for today, use that (and make sure the file is from the current year)
		if ( file_exists( __DIR__ . '/data/' . $dayOfYear . '.txt' ) && date( 'Y' ) == date( 'Y', filemtime( __DIR__ . '/data/' . $dayOfYear . '.txt' ) ) ) {
			$article = file_get_contents( __DIR__ . '/data/' . $dayOfYear . '.txt' );
			$out->addHTML( '<div class="daily-page" style="background-color: #ffcc00; padding: 10px; border: 1px solid #000;">' );
			$out->addHTML( '<h2>Today\'s page</h2>' );
			$out->addHTML( '<p><a href="' . $article . '">' . $article . '</a></p>' );
			$out->addHTML( '</div>' );
			return;
		}
		mt_srand( $dayOfYear );
		// Get a random page on the wiki using SQL
		$dbr = wfGetDB( DB_REPLICA );
		$sql = 'SELECT page_title FROM page WHERE page_namespace = 0 ORDER BY rand(' . $dayOfYear . ') LIMIT 1';
		$res = $dbr->query( $sql );
		$row = $res->fetchObject();
		// Create data directory if it doesn't exist
		if ( !file_exists( __DIR__ . '/data' ) ) {
			mkdir( __DIR__ . '/data' );
		}
		$row->page_title = str_replace( '_', ' ', $row->page_title );
		// Save the article for today
		file_put_contents( __DIR__ . '/data/' . $dayOfYear . '.txt', $row->page_title );
		// Show the article
		$out->addHTML( '<div class="daily-page" style="background-color: #ffcc00; padding: 10px; border: 1px solid #000;">' );
		$out->addHTML( '<h2>Today\'s page</h2>' );
		$out->addHTML( '<p><a href="' . $row->page_title . '">' . $row->page_title . '</a></p>' );
		$out->addHTML( '</div>' );
	}

}
